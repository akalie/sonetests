<?php
    Package::Load( 'SPS.Site' );

    /**
     * DeleteArticleControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class DeleteArticleControl {

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
                $o = new Article();
                $o->statusId = 3;
                ArticleFactory::UpdateByMask($o, array('statusId'), array('articleId' => $id));
            }
        }
    }
?>