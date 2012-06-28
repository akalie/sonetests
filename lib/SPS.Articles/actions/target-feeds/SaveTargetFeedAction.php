<?php
    /**
     * Save TargetFeed Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property TargetFeed originalObject
     * @property TargetFeed currentObject
     */
    class SaveTargetFeedAction extends BaseSaveAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new TargetFeedFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param TargetFeed $originalObject 
         * @return TargetFeed
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var TargetFeed $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->targetFeedId = $originalObject->targetFeedId;
            }

            $data = Request::getArray('targetFeed');
            if (empty($data['startTime'])) {
                $object->startTime = '09:00:00';
            }
            if (empty($object->period)) {
                $object->period = 60;
            }

            $object->grids = !empty($object->grids) ? $object->grids : array();

            $object->vkIds = str_replace(' ', '', $object->vkIds);

            //type
            if (empty($object->type) || empty(TargetFeedUtility::$Types[$object->type])) {
                $object->type = TargetFeedUtility::VK;
            }

            //publishers
            $publisherIds   = Request::getArray( 'publisherIds' );
            $publisherIds   = !empty($publisherIds) ? array_unique($publisherIds) : array();
            $publishers     = Response::getArray( 'publishers' );

            $object->publishers = array();
            if (!empty($publisherIds)) {
                foreach ($publisherIds as $publisherId) {
                    if (empty($publishers[$publisherId])) continue;

                    $tfp = new TargetFeedPublisher();
                    $tfp->publisherId = $publisherId;

                    $object->publishers[$publisherId] = $tfp;
                }
            }

            return $object;
        }

        protected function beforeSave() {
            $gridData = array();

            if (!empty($this->currentObject->grids)) {
                foreach ($this->currentObject->grids as $grid) {
                    $gridData[] = array(
                        'targetFeedGridId' => !empty($grid->targetFeedGridId) ? $grid->targetFeedGridId : '',
                        'period' => !empty($grid->period) ? $grid->period : '',
                        'startDate' => !empty($grid->startDate) ? $grid->startDate->DefaultFormat() : '',
                    );
                }
            }

            Response::setString('gridData', ObjectHelper::ToJSON($gridData));

            //publishers
            $publisherIds = array();
            if (!empty($this->currentObject->publishers)) {
                foreach ($this->currentObject->publishers as $feedPublisher) {
                    $publisherIds[$feedPublisher->publisherId] = $feedPublisher->publisherId;
                }
            }
            Response::setArray( 'publisherIds', $publisherIds );
        }
        
        
        /**
         * Validate Object
         *
         * @param TargetFeed $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );

            if (empty($errors['fields']['period'])) {
                if ($object->period <= 10 || $object->period >= 180) {
                    $errors['fields']['period']['periodVal'] = 'periodVal';
                }
            }

            if (!empty($object->grids)) {
                $gridErrors = array();
                $i = 0;
                foreach ($object->grids as $grid) {
                    if (empty($grid->startDate) || empty($grid->period)) {
                        $gridErrors[] = $i;
                    }
                    $i++;
                }
                if (!empty($gridErrors)) {
                    $errors['fields']['grids'] = $gridErrors;
                }
            }

            if (!empty($object->externalId)) {
                $duplicates = TargetFeedFactory::Count(
                    array('externalId' => $object->externalId),
                    array(BaseFactory::WithoutDisabled => false, BaseFactory::CustomSql => ' and "targetFeedId" != ' . PgSqlConvert::ToString((int)$object->targetFeedId))
                );

                if (!empty($duplicates)) {
                    $errors['fields']['externalId']['unique'] = 'unique';
                }
            }

            if ($object->type == TargetFeedUtility::VK && empty($object->publishers)) {
                $errors['fields']['publishers']['null'] = 'null';
            }
            if ($object->type == TargetFeedUtility::FB && empty($object->params['token'])) {
                $errors['fields']['token']['null'] = 'null';
            }

            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param TargetFeed $object
         * @return bool
         */
        protected function add( $object ) {
            ConnectionFactory::BeginTransaction();

            $result = parent::$factory->Add( $object );
            $objectId = parent::$factory->GetCurrentId();

            if ($result && !empty($object->grids)) {
                foreach ($object->grids as $grid) {
                    $grid->targetFeedId = $objectId;
                }
                $result = TargetFeedGridFactory::AddRange($object->grids);
            }

            if ($result && !empty($object->publishers)) {
                foreach ($object->publishers as $publisher) {
                    $publisher->targetFeedId = $objectId;
                }
                if (!empty($object->publishers)) {
                    $result = TargetFeedPublisherFactory::AddRange($object->publishers);
                } else {
                    $result = true;
                }
            }

            ConnectionFactory::CommitTransaction($result);
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param TargetFeed $object
         * @return bool
         */
        protected function update( $object ) {
            ConnectionFactory::BeginTransaction();

            $result = parent::$factory->Update( $object );
            $objectId = $object->targetFeedId;

            if ($result) {
                foreach ($object->grids as $grid) {
                    $grid->targetFeedId = $objectId;
                }
                $result = TargetFeedGridFactory::SaveArray($object->grids, $this->originalObject->grids);
            }

            if ($result) {
                foreach ($object->publishers as $publisher) {
                    $publisher->targetFeedId = $objectId;
                }

                TargetFeedPublisherFactory::DeleteByMask(array('targetFeedId' => $objectId));
                if (!empty($object->publishers)) {
                    $result = TargetFeedPublisherFactory::AddRange($object->publishers);
                } else {
                    $result = true;
                }
            }

            ConnectionFactory::CommitTransaction($result);
            return $result;
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {
            $publishers = PublisherFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            Response::setArray( "publishers", $publishers );
        }
    }
?>