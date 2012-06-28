<?php
    Package::Load( 'SPS.Site' );

    /**
     * ParseUrlControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class ParseUrlControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $result = UrlParser::Parse(Request::getString('url'));
            echo ObjectHelper::ToJSON($result);
        }
    }
?>