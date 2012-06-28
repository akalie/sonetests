<?php
    /**
     * Get SourceFeed List Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property SourceFeed[] list
     */
    class GetSourceFeedListAction extends BaseGetAction {

        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => false
            );

            parent::$factory = new SourceFeedFactory();
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