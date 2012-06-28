<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetLinkInfoControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetLinkInfoControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $result = array();

            $url = Request::getString('url');
            if ($url) {
                $metaDetail = MetaDetailFactory::GetOne(array('url' => trim($url, '/')));
                if (!empty($metaDetail)) {
                    $result['url'] = $url;
                    $result['title'] = !empty($metaDetail->pageTitle) ? $metaDetail->pageTitle : '';
                    $result['description'] = !empty($metaDetail->metaDescription) ? $metaDetail->metaDescription : '';
                    $result['img'] = !empty($metaDetail->alt) ? $metaDetail->alt : '';

                    if (!empty($metaDetail->alt)) {
                        $result['img'] = MediaUtility::GetFilePath( 'Link', 'photos', 'small', $metaDetail->alt, MediaServerManager::$MainLocation );
                    } else {
                        $result['img'] = '';
                    }
                }
            }

            echo ObjectHelper::ToJSON($result);
        }
    }
?>