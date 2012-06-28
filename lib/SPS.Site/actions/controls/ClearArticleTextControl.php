<?php
    Package::Load( 'SPS.Site' );

    /**
     * ClearArticleTextControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class ClearArticleTextControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );

            if (empty($id)) {
                return;
            }

            $object = ArticleFactory::GetById($id);
            if (empty($object)) {
                return;
            }

            //check access
            if (!AccessUtility::HasAccessToSourceFeedId($object->sourceFeedId)) {
                return;
            }

            if ($id) {
                $o = new ArticleRecord();
                $o->content = '';
                ArticleRecordFactory::UpdateByMask($o, array('content'), array('articleId' => $id));
            }
        }
    }

?>