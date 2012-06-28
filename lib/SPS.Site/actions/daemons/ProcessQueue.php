<?php
    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );

    /**
     * ProcessQueue Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class ProcessQueue {

        /**
         * Entry Point
         */
        public function Execute() {
            set_time_limit(0);
            Logger::LogLevel(ELOG_DEBUG);

            ConnectionFactory::BeginTransaction();

            //атомарно занимаем запись в очереди
            //берем только ту запись, которую нужно отправить по времени
            //валера, твое время настало!
            $sql = <<<sql
                SELECT * FROM "articleQueues"
                WHERE "statusId" = 1
                AND @now >= "startDate"
                AND @now <= "endDate"
                LIMIT 1 FOR UPDATE;
sql;

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
            $cmd->SetDateTime('@now', DateTimeWrapper::Now());

            $ds         = $cmd->Execute();
            $structure  = BaseFactory::getObjectTree( $ds->Columns );
            while ($ds->next() ) {
                $object = BaseFactory::GetObject( $ds, ArticleQueueFactory::$mapping, $structure );
            }

            if (!empty($object)) {
                $object->statusId = StatusUtility::Queued;
                ArticleQueueFactory::UpdateByMask($object, array('statusId'), array('articleQueueId' => $object->articleQueueId) );
                ConnectionFactory::CommitTransaction(true);
            } else {
                ConnectionFactory::CommitTransaction(true);
                return;
            }

            $this->sendArticleQueue($object);
        }

        /**
         * @param ArticleQueue $articleQueue
         * @return bool
         */
        private function sendArticleQueue($articleQueue) {
            $result = false;

            //select objects
            $targetFeed = TargetFeedFactory::GetById($articleQueue->targetFeedId, array(), array(BaseFactory::WithLists => true));
            $articleRecord = ArticleRecordFactory::GetOne(
                array('articleQueueId' => $articleQueue->articleQueueId)
            );

            //check for article options
            ArticleFactory::$mapping['view'] = ArticleFactory::$mapping['table'];
            $article = ArticleFactory::GetById($articleQueue->articleId, array(), array(BaseFactory::WithoutDisabled => false));
            $sourceFeed = SourceFeedFactory::GetOne(array('sourceFeedId' => $article->sourceFeedId));

            if ($targetFeed->type == TargetFeedUtility::FB) {
                if (empty($targetFeed) || empty($articleRecord)) {
                    return false;
                }

                $this->sendPostToFb($targetFeed, $articleQueue, $articleRecord);
            }

            if ($targetFeed->type == TargetFeedUtility::VK) {
                if (empty($targetFeed) || empty($targetFeed->publishers) || empty($articleRecord)) {
                    return false;
                }

                foreach ($targetFeed->publishers as $publisher) {
                    try {
                        $this->sendPostToVk($sourceFeed, $targetFeed, $articleQueue, $articleRecord, $publisher->publisher);
                        return true;
                    } catch (ChangeSenderException $Ex) {
                        //ниче не делаем
                    }
                }
            }
        }

        /**
         * @param SourceFeed $sourceFeed
         * @param TargetFeed $targetFeed
         * @param ArticleQueue $articleQueue
         * @param ArticleRecord $articleRecord
         * @param Publisher $publisher
         */
        private function sendPostToVk($sourceFeed, $targetFeed, $articleQueue, $articleRecord, $publisher) {
            $isWithSmallPhoto = ArticleUtility::IsTopArticleWithSmallPhoto($sourceFeed, $articleRecord);
            if ($isWithSmallPhoto) {
                $articleRecord->photos = array();
            }

            $post_data = array(
                'text' => $articleRecord->content,
                'group_id' => $targetFeed->externalId,
                'vk_app_seckey' => $publisher->vk_seckey,
                'vk_access_token' => $publisher->vk_token,
                'photo_array' => array(),
                'audio_id' => array(),
                'video_id' => array(),
                'link' => $articleRecord->link,
                'header' => '',
            );

            if (!empty($articleRecord->photos)) {
                foreach ($articleRecord->photos as $photoItem) {
                    $remotePath = MediaUtility::GetFilePath( 'Article', 'photos', 'original', $photoItem['filename'], MediaServerManager::$MainLocation);
                    $localPath  = Site::GetRealPath('temp://upl_' . $photoItem['filename']);

                    file_put_contents($localPath, file_get_contents($remotePath));

                    $post_data['photo_array'][] = $localPath;
                }
            }

            $sender = new SenderVkontakte($post_data);

            try {
                $sender->send_post();

                //закрываем
                $this->finishArticleQueue($articleQueue);
            } catch (ChangeSenderException $Ex){
                throw $Ex;
            } catch (Exception $Ex){
                $err = $Ex->getMessage();
                Logger::Warning($err);

                AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId, $err);

                //ставим обратно в очередь
                $this->restartArticleQueue($articleQueue);
            }

            //unlink temp files
            if (!empty($post_data['photo_array'])) {
                foreach ($post_data['photo_array'] as $localPath) {
                    @unlink($localPath);
                }
            }
        }

        /**
         * @param TargetFeed $targetFeed
         * @param ArticleQueue $articleQueue
         * @param ArticleRecord $articleRecord
         */
        private function sendPostToFb($targetFeed, $articleQueue, $articleRecord) {
            $fields_arr = array(
                'album'         => $targetFeed->externalId,
                'access_token'  => $targetFeed->params['token'],
                'source'        => null,
                'message'       => $articleRecord->content,
                'targeting'     => array(
                    'countries' => array('RU'),
                    'locales'   => 17
                )
            );

            if (!empty($articleRecord->photos)) {
                $photoItem = current($articleRecord->photos);

                $remotePath = MediaUtility::GetFilePath( 'Article', 'photos', 'original', $photoItem['filename'], MediaServerManager::$MainLocation);
                $localPath  = Site::GetRealPath('temp://upl_' . $photoItem['filename']);

                file_put_contents($localPath, file_get_contents($remotePath));

                $fields_arr['source'] = '@' . $localPath;
            } else {
                AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId, 'Article has no photos');
                return;
            }

            try {
                $sender = new SenderFacebook(json_encode($fields_arr));
                $result = $sender->send_photo();
                Logger::Info($result);

                //закрываем
                $this->finishArticleQueue($articleQueue);
            } catch (Exception $Ex) {
                $err = $Ex->getMessage();
                Logger::Warning($err);

                AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId, $err);

                //ставим обратно в очередь
                $this->restartArticleQueue($articleQueue);
            }

            //unlink temp files
            @unlink($localPath);
        }

        private function finishArticleQueue($articleQueue) {
            $articleQueue->statusId = StatusUtility::Finished;
            $articleQueue->sentAt = DateTimeWrapper::Now();
            ArticleQueueFactory::UpdateByMask($articleQueue, array('statusId', 'sentAt'), array('articleQueueId' => $articleQueue->articleQueueId) );
        }

        private function restartArticleQueue($articleQueue) {
            $articleQueue->statusId = 1;
            ArticleQueueFactory::UpdateByMask($articleQueue, array('statusId'), array('articleQueueId' => $articleQueue->articleQueueId) );
        }
    }
?>