<?php
    Package::Load( 'SPS.Site' );

    /**
     * SaveLinkInfoControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SaveLinkInfoControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $data = Request::getArray('data');

            $metaDetail = new MetaDetail();
            $metaDetail->url = trim($data['link'], '/');
            $metaDetail->pageTitle = !empty($data['header']) ? $data['header'] : '';
            $metaDetail->metaDescription = !empty($data['description']) ? $data['description'] : '';
            $metaDetail->alt = '';
            $metaDetail->isInheritable = false;
            $metaDetail->statusId = 1;

            //original id
            if (!empty($metaDetail->url)) {
                $originalObject = MetaDetailFactory::GetOne(array('url' => $metaDetail->url));
                if (!empty($originalObject)) {
                    $metaDetail->metaDetailId = $originalObject->metaDetailId;
                    $metaDetail->alt = !empty($originalObject->alt) ? $originalObject->alt : '';
                }
            }

            if (!empty($data['coords'])) {
                $dimensions = $this->getDimensions($data['coords']);

                $urlData = UrlParser::Parse($data['link']);

                if (!empty($urlData['imgOriginal'])) {
                    $tmpName = Site::GetRealPath('temp://') . md5($urlData['imgOriginal']) . '.jpg';
                    $fileContent = (Site::IsDevel()) ? file_get_contents($urlData['imgOriginal']) : UrlParser::getUrlContent($urlData['imgOriginal']);

                    file_put_contents($tmpName, $fileContent);
                    $file = array(
                        'tmp_name'  => $tmpName,
                        'name'      => $tmpName,
                    );

                    ImageHelper::Crop( $tmpName, $tmpName, $dimensions['x'], $dimensions['y'], $dimensions['w'], $dimensions['h'], 100 );

                    $fileUploadResult = MediaUtility::SaveTempFile( $file, 'Link', 'photos' );

                    if( !empty( $fileUploadResult['filename'] ) ) {
                        MediaUtility::MoveObjectFilesFromTemp( 'Link', 'photos', array($fileUploadResult['filename']) );
                        unlink($tmpName);

                        $metaDetail->alt = $fileUploadResult['filename'];
                    }
                }
            }

            if (!empty($metaDetail->metaDetailId)) {
                $result = MetaDetailFactory::Update($metaDetail);
            } else {
                $result = MetaDetailFactory::Add($metaDetail);
            }

            if ($result) {
                $result = array();
                $result['url'] = $data['link'];
                $result['title'] = !empty($metaDetail->pageTitle) ? $metaDetail->pageTitle : '';
                $result['description'] = !empty($metaDetail->metaDescription) ? $metaDetail->metaDescription : '';
                $result['img'] = !empty($metaDetail->alt) ? MediaUtility::GetFilePath( 'Link', 'photos', 'small', $metaDetail->alt, MediaServerManager::$MainLocation ) : '';
            }

            $result = ObjectHelper::ToJSON($result);

            //постим обратно
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, $data['link'] . '?data=' . $result);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
            curl_exec($c);
            curl_close($c);

            echo $result;
        }

        private function getDimensions($coords) {
            $result = array();

            $fields = array( 'x', 'y', 'w', 'h' );

            foreach( $fields as $field ) {
                $value = $coords[$field];
                if( ( $value < 0 ) || empty( $value ) ) {
                    $value = 0;
                }

                $result[$field] = $value;
            }

            return $result;
        }
    }
?>