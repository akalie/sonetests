<?php
    /**
     * Save Publisher Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property Publisher originalObject
     * @property Publisher currentObject
     */
    class SavePublisherAction extends BaseSaveAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new PublisherFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param Publisher $originalObject 
         * @return Publisher
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var Publisher $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->publisherId = $originalObject->publisherId;
            }
            
            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param Publisher $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param Publisher $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param Publisher $object
         * @return bool
         */
        protected function update( $object ) {
            $result = parent::$factory->Update( $object );
            
            return $result;
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {}
    }
?>