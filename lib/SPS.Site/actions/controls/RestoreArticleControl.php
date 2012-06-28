<?php
    Package::Load( 'SPS.Site' );

    /**
     * RestoreArticleControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class RestoreArticleControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );
            if ($id) {
                $o = new Article();
                $o->statusId = 1;
                ArticleFactory::UpdateByMask($o, array('statusId'), array('articleId' => $id));
            }
        }
    }
?>