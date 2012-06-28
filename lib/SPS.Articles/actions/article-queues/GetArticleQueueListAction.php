<?php
    /**
     * Get ArticleQueue List Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property ArticleQueue[] list
     */
    class GetArticleQueueListAction extends BaseGetAction {

        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => false
            );

            parent::$factory = new ArticleQueueFactory();
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {
            $targetFeeds = TargetFeedFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            Response::setArray( "targetFeeds", $targetFeeds );
        }
    }
?>