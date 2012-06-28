<?php
    /**
     * Save SourceFeed Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property SourceFeed originalObject
     * @property SourceFeed currentObject
     */
    class SaveSourceFeedAction extends BaseSaveAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new SourceFeedFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param SourceFeed $originalObject 
         * @return SourceFeed
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var SourceFeed $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->sourceFeedId = $originalObject->sourceFeedId;
                $object->processed = $originalObject->processed;
            } else {
                $object->processed = null;
            }

            $targetFeedIds = Request::getArray( 'targetFeedIds' );
            $targetFeedIds = !empty($targetFeedIds) ? $targetFeedIds : array();
            $object->targetFeedIds = implode(',', $targetFeedIds);

            if ($object->type != SourceFeedUtility::Source) {
                $object->externalId = '-';
            }

            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param SourceFeed $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );

            if (!empty($object->externalId)) {
                $duplicates = SourceFeedFactory::Count(
                    array('externalId' => $object->externalId),
                    array(BaseFactory::WithoutDisabled => false, BaseFactory::CustomSql => ' and "sourceFeedId" != ' . PgSqlConvert::ToString((int)$object->sourceFeedId))
                );

                if (!empty($duplicates)) {
                    $errors['fields']['externalId']['unique'] = 'unique';
                }
            }

            if ($object->type != SourceFeedUtility::Source && !empty($errors['fields']['externalId'])) {
                unset($errors['fields']['externalId']);
            }

            if (empty($errors['fields'])) {
                unset($errors['fields']);
            }

            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param SourceFeed $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param SourceFeed $object
         * @return bool
         */
        protected function update( $object ) {
            $result = parent::$factory->Update( $object );
            
            return $result;
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {
            $targetFeeds = TargetFeedFactory::Get( null, array( BaseFactory::WithoutDisabled => false ) );
            Response::setArray( 'targetFeeds', $targetFeeds );
        }
    }
?>