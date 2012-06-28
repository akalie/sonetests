<?php
    /**
     * Save ArticleQueue Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property ArticleQueue originalObject
     * @property ArticleQueue currentObject
     */
    class SaveArticleQueueAction extends BaseSaveAction  {

        /**
         * @var ArticleRecord
         */
        private $articleRecord;
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new ArticleQueueFactory();
        }

        protected function beforeSave() {
            $this->photosToResponse();
        }

        protected function photosToResponse() {
            $photos = array();
            if (!empty($this->articleRecord->photos)) {
                foreach($this->articleRecord->photos as $photoItem) {
                    $photo = $photoItem;
                    $photo['path'] = MediaUtility::GetFilePath( 'Article', 'photos', 'small', $photoItem['filename'], MediaServerManager::$MainLocation);
                    $photos[] = $photo;
                }
            }
            Response::setString( 'filesJSON', ObjectHelper::ToJSON($photos) );
        }
               
        /**
         * Form Object From Request
         *
         * @param ArticleQueue $originalObject 
         * @return ArticleQueue
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var ArticleQueue $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->articleQueueId = $originalObject->articleQueueId;
                $object->createdAt      = $originalObject->createdAt;
            } else {
                $object->createdAt      = DateTimeWrapper::Now();
            }

            $this->articleRecord = ArticleRecordFactory::GetFromRequest( "articleRecord" );
            $this->articleRecord->articleId         = null; //NB
            $this->articleRecord->articleRecordId   = null; //NB

            //set original articleRecordId if exists
            if ( $originalObject != null ) {
                $originalArticleRecord = ArticleRecordFactory::GetOne(
                    array('articleQueueId' => $this->originalObject->articleQueueId)
                    , array(BaseFactory::WithColumns => '"articleRecordId"')
                );

                if (!empty($originalArticleRecord) && !empty($originalArticleRecord->articleRecordId)) {
                    $this->articleRecord->articleRecordId = $originalArticleRecord->articleRecordId;
                }
            }

            //get photos from request
            $photos = Request::getArray( 'files' );
            $photos = !empty($photos) ? $photos : array();
            $this->articleRecord->photos = $photos;

            //fix arrays
            $arrays = array('retweet', 'video', 'music', 'text_links');
            $data   = Request::getArray( "articleRecord" );
            foreach ($arrays as $arrayName) {
                $value = !empty($data[$arrayName]) ? $data[$arrayName] : '[]';
                $value = ObjectHelper::FromJSON($value);
                $this->articleRecord->$arrayName = $value;
            }

            //force articleId
            $articleId = Request::getInteger( 'articleId' );
            if ($articleId) {
                $article = ArticleFactory::GetById($articleId);
                if ($article) {
                    $object->articleId = $articleId;

                    //force article records fields
                    $forceArticleRecord = ArticleRecordFactory::GetOne(
                        array('articleId' => $articleId)
                    );

                    if (!empty($forceArticleRecord)) {
                        $this->articleRecord->content       = $forceArticleRecord->content;
                        $this->articleRecord->likes         = $forceArticleRecord->likes;
                        $this->articleRecord->photos        = $forceArticleRecord->photos;
                        $this->articleRecord->link          = $forceArticleRecord->link;
                        $this->articleRecord->rate          = $forceArticleRecord->rate;
                        $this->articleRecord->retweet       = $forceArticleRecord->retweet;
                        $this->articleRecord->video         = $forceArticleRecord->video;
                        $this->articleRecord->music         = $forceArticleRecord->music;
                        $this->articleRecord->map           = $forceArticleRecord->map;
                        $this->articleRecord->poll          = $forceArticleRecord->poll;
                        $this->articleRecord->text_links    = $forceArticleRecord->text_links;
                        $this->articleRecord->doc           = $forceArticleRecord->doc;
                    }
                }
            }

            Response::setParameter( "articleRecord", $this->articleRecord );

            $this->photosToResponse();
            
            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param ArticleQueue $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );

            $articleRecordErrors = ArticleRecordFactory::Validate( $this->articleRecord );

            if( !empty( $articleRecordErrors['fields'] ) && $this->action != BaseSaveAction::DeleteAction ) {
                foreach( $articleRecordErrors['fields'] as $key => $value ) {
                    $errors['fields'][$key] = $value;
                }
            }

            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param ArticleQueue $object
         * @return bool
         */
        protected function add( $object ) {
            $conn = ConnectionFactory::Get();
            $conn->begin();

            $result = parent::$factory->Add( $object );

            $this->articleRecord->articleQueueId = parent::$factory->GetCurrentId();

            if ( $result ) {
                if( empty( $this->articleRecord->articleRecordId ) ) {
                    $result = ArticleRecordFactory::Add( $this->articleRecord );
                } else {
                    $result = ArticleRecordFactory::Update( $this->articleRecord );
                }
            }

            if ( $result ) {
                $conn->commit();
            } else {
                $conn->rollback();
            }

            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param ArticleQueue $object
         * @return bool
         */
        protected function update( $object ) {
            $conn = ConnectionFactory::Get();
            $conn->begin();

            $result = parent::$factory->Update( $object );

            $this->articleRecord->articleQueueId = $object->articleQueueId;

            if ( $result ) {
                if( empty( $this->articleRecord->articleRecordId ) ) {
                    $result = ArticleRecordFactory::Add( $this->articleRecord );
                } else {
                    $result = ArticleRecordFactory::Update( $this->articleRecord );
                }
            }

            if ( $result ) {
                $conn->commit();
            } else {
                $conn->rollback();
            }

            return $result;
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {
            $targetFeeds = TargetFeedFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            Response::setArray( "targetFeeds", $targetFeeds );

            /*
            * Creating new ArticleRecord object or select existing
            */
            if( !empty( $this->originalObject ) ) {
                $this->articleRecord = ArticleRecordFactory::GetOne( array('articleQueueId' => $this->originalObject->articleQueueId) );
            }

            if( empty( $this->articleRecord ) ) {
                $this->articleRecord = new ArticleRecord();
            }

            Response::setParameter( "articleRecord", $this->articleRecord );
        }
    }
?>