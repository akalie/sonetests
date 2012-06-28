<?php
    /**
     * Get Publisher List Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property Publisher[] list
     */
    class GetPublisherListAction extends BaseGetAction {

        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => false
            );

            parent::$factory = new PublisherFactory();
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {}
    }
?>