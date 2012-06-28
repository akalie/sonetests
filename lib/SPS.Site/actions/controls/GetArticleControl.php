<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetArticleControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetArticleControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $articleId = Request::getInteger( 'articleId' );
            if (empty($articleId)) return;

            $article = ArticleFactory::GetById($articleId);

            if (empty($article)) return;

            //check access
            if (!AccessUtility::HasAccessToSourceFeedId($article->sourceFeedId)) {
                return;
            }

            $articleRecord = ArticleRecordFactory::GetOne(array('articleId' => $articleId));

            if (empty($articleRecord)) return;

            $photos = array();
            if (!empty($articleRecord->photos)) {
                foreach($articleRecord->photos as $photoItem) {
                    $photo = $photoItem;
                    $photo['path'] = MediaUtility::GetFilePath( 'Article', 'photos', 'small', $photoItem['filename'], MediaServerManager::$MainLocation);
                    $photos[] = $photo;
                }
            }

            $result = array(
                'id' => $articleId,
                'text' => nl2br($articleRecord->content),
                'photos' => ObjectHelper::ToJSON($photos),
                'link' => $articleRecord->link
            );

            echo ObjectHelper::ToJSON($result);
        }
    }
?>