<?php
    /**
     * MediaUtility
     * @author Shuler
     * @package SPS
     * @subpackage System
     */
    class MediaUtility {

        /**
         * Mapping for media files of objects
         *
         * @var array
         */
        public static $Mapping = array(
            'Article' => array(
                'photos' => array(
                    'folder'            => 'article-photos'
                    , 'maxSize'         => 2097152 //2MB (1024*1024*2)
                    , 'ext'             => array( 'jpeg', 'jpg', 'gif', 'png' )
                    , 'isImage'         => true
                    , 'resizes'     => array(
                        'small' => array(
                            'name'      => 'small'
                            , 'width'   => 60
                            , 'height'  => 60
                            , 'quality' => 100
                            , 'scale'   => false
                        )
                        , 'original' => array(
                            'name'      => 'original'
                        )
                    )
                )
            )
            , 'Link' => array(
                'photos' => array(
                    'folder'            => 'link-photos'
                    , 'maxSize'         => 2097152 //2MB (1024*1024*2)
                    , 'ext'             => array( 'jpeg', 'jpg', 'gif', 'png' )
                    , 'isImage'         => true
                    , 'resizes'         => array(
                        'small' => array(
                            'name'      => 'small'
                            , 'width'   => 130
                            , 'height'  => 63
                            , 'quality' => 100
                            , 'scale'   => false
                            , 'watermark' => true
                        )
                        , 'original' => array(
                            'name'      => 'original'
                        )
                    )
                )
            )
        );

        /**
         * @static
         * @param string $folder (e.g. 'user-avatars')
         * @param string $size (e.g. 'small')
         * @param string $fileName (e.g. '409ab75f.jpg')
         * @return string
         */
        public static function GenerateRemotePath( $folder, $size, $fileName ) {
            return sprintf( '/%s/%s/%s/%s/%s'
                , $folder
                , $size
                , substr( $fileName, 0, 1 )
                , substr( $fileName, 0, 2 )
                , $fileName
            );
        }

        /**
         * @static
         * @param string $objectName (e.g. 'SiteUser')
         * @param string $field (e.g. 'image')
         * @param string $size (e.g. 'small')
         * @param string $fileName (e.g. '409ab75f.jpg')
         * @param string $location (e.g. MediaServerManager::$TempLocation)
         * @return string
         */
        public static function GetFilePath( $objectName, $field, $size, $fileName, $location = null ) {
            $location = !empty( $location ) ? $location : MediaServerManager::$MainLocation;

            $folder = self::$Mapping[$objectName][$field]['folder'];

            return sprintf( 'http://%s%s/%s'
                , MediaServerManager::$Host
                , rtrim( $location, '/' )
                , trim( self::GenerateRemotePath( $folder, $size, $fileName ), '/' )
            );
        }

        /**
         * Save Request Files To Temp (with form files and file uploads check)
         *
         * @static
         * @param string $objectName (e.g. 'SiteUser')
         * @param string $field (e.g. 'image')
         * @param string $prefix (e.g. 'files')
         * @return array
         */
        public static function SaveRequestFilesToTemp( $objectName, $field, $prefix ) {
            $result         = array();
            $filesUpload    = Request::GetFiles( $prefix );
            $filesForm      = Request::getArray( $prefix );

            if( !empty( $filesForm ) ) {
                foreach( $filesForm as $id => $file ) {
                    $result[$id] = $file;
                }
            }

            if( !empty( $filesUpload ) ) {
                foreach( $filesUpload as $id => $file ) {
                    if ( $file["error"] == 0 ) {
                        $result[$id] = self::SaveTempFile( $file, $objectName, $field );
                        $result[$id]['location'] = MediaServerManager::$TempLocation;
                    }
                }
            }

            //max files count check
            $result = array_slice( $result, 0, self::$Mapping[$objectName][$field]['maxCount'], true );

            return $result;
        }

        /**
         * Save uploaded file to temp
         *
         * @static
         * @param array $file (file from $_FILES)
         * @param string $objectName (e.g. 'SiteUser')
         * @param string $field (e.g. 'image')
         * @param string $remoteFileName (e.g. '3e84a8a7b6be2ff01702ad878859c105.jpg')
         * @param string $watermarkPath (full real path of user watermark)
         * @return array
         */
        public static function SaveTempFile( $file, $objectName, $field, $remoteFileName = null, $watermarkPath = null ) {
            $result = array();
            
            //mapping
            $mapping = self::$Mapping[$objectName][$field];

            //ext check
            $ext = pathinfo( $file['name'], PATHINFO_EXTENSION );
            $ext = mb_strtolower( $ext );
            if( !in_array( $ext, $mapping['ext'] ) ) {
                $result['error'] = 'extension';
                return $result;
            }

            //image check
            if( $mapping['isImage'] && !ImageHelper::IsImage( $file['tmp_name'] ) ) {
                $result['error'] = 'notImage';
                return $result;
            }

            //convert to jpeg
            if( $mapping['isImage'] ) {
                self::ConvertImageToJpg( $file['tmp_name'] );
            }

            //size check
            if( !empty( $mapping['maxSize'] ) ) {
                if( filesize( $file['tmp_name'] ) > $mapping['maxSize'] ) {
                    $result['error'] = 'filesize';
                    return $result;
                }
            }

            //dimensions check
            if( $mapping['isImage'] ) {
                $dimensions = ImageHelper::GetImageSizes( $file['tmp_name'] );

                if( array_key_exists( 'dimensions', $mapping ) ) {
                    if( array_key_exists( 'width', $mapping['dimensions'] ) && $dimensions['width'] != $mapping['dimensions']['width'] ) {
                        $result['error'] = 'imageDimensionsWrong';
                    } else if( array_key_exists( 'height', $mapping['dimensions'] ) && $dimensions['height'] != $mapping['dimensions']['height'] ) {
                        $result['error'] = 'imageDimensionsWrong';
                    }
                }

                if( array_key_exists( 'minDimensions', $mapping ) ) {
                    if( array_key_exists( 'width', $mapping['minDimensions'] ) && $dimensions['width'] < $mapping['minDimensions']['width'] ) {
                        $result['error'] = 'imageDimensionsMin';
                    } else if( array_key_exists( 'height', $mapping['minDimensions'] ) && $dimensions['height'] < $mapping['minDimensions']['height'] ) {
                        $result['error'] = 'imageDimensionsMin';
                    }
                }

                if( !empty( $result['error'] ) ) {
                    return $result;
                }
            }
            
            //resize check
            if( empty( $mapping['resizes'] ) ) {
                $result['error'] = 'rulesError';
                return $result;
            }

            //remote filename
            if( empty( $remoteFileName ) ) {
                $remoteFileName = md5( md5_file($file['tmp_name']) . time() );
                $remoteFileName = sprintf( '%s.%s', $remoteFileName, $ext );
            }

            //local temp filename
            $localTempFileName = Site::GetRealPath( 'temp://' . $remoteFileName );

            //creating copy of image for resize
            if( !empty( $mapping['initDimensions'] ) ) {
                $imageForResizePath = $localTempFileName . '_res';
                MediaServerManager::Resize( $file['tmp_name'], $imageForResizePath, $mapping['initDimensions']['width'], $mapping['initDimensions']['height'], 100, true );
            } else {
                $imageForResizePath = $file['tmp_name'];
            }

            //saving image types
            foreach( $mapping['resizes'] as $resizeRule ) {
                //new path
                $remoteFilePath = self::GenerateRemotePath( $mapping['folder'], $resizeRule['name'], $remoteFileName );

                if( !empty( $resizeRule['width'] ) && !empty( $resizeRule['height'] ) && !empty( $resizeRule['quality'] ) ) {
                    //saving resize

                    if( $resizeRule['name'] == 'crop' ) {
                        //fix dimensions for crop resize
                        list ($src_width, $src_height, $type, $w) = getimagesize($imageForResizePath);
                        $resizeRule['width'] = ( $src_width < 600 ) ? $src_width : 600;
                        $resizeRule['height'] = ( $src_height < 600 ) ? $src_height : 600;
                    }

                    MediaServerManager::Resize( $imageForResizePath, $localTempFileName, $resizeRule['width'], $resizeRule['height'], $resizeRule['quality'], $resizeRule['scale'] );
                    $localUploadFileName = $localTempFileName;
                } else {
                    //saving original
                    $localUploadFileName = $imageForResizePath;
                }

                //placing watermark after resize
                if( !empty( $resizeRule['watermark'] ) ) {
                    if( $localUploadFileName == $imageForResizePath ) {
                        copy( $localUploadFileName, $localTempFileName );
                        $localUploadFileName = $localTempFileName;
                    }
                    WatermarkUtility::PlaceWatermark( $localUploadFileName, $watermarkPath );
                }

                //upload file option
                MediaServerManager::PutFile( $localUploadFileName, $remoteFilePath, MediaServerManager::$TempLocation );

                //removing local file option if it exists
                if( $localUploadFileName == $localTempFileName ) {
                    unlink( $localTempFileName );
                }
            }

            if( $imageForResizePath != $file['tmp_name'] ) {
                unlink( $imageForResizePath );
            }

            //result
            $result['filename'] = $remoteFileName;

            return $result;
        }

        /**
         * Move object file from temp
         *
         * @static
         * @param string $objectName (e.g. 'SiteUser')
         * @param string $field (e.g. 'image')
         * @param array $fileNames (e.g. ['409ab75f.jpg', '8e5abb00.jpg'])
         * @return void
         */
        public static function MoveObjectFilesFromTemp( $objectName, $field, $fileNames ) {
            $mapping    = self::$Mapping[$objectName][$field];
            $folder     = self::$Mapping[$objectName][$field]['folder'];

            foreach( $mapping['resizes'] as $resizeRule ) {
                foreach( $fileNames as $fileName ) {
                    $path = self::GenerateRemotePath( $folder , $resizeRule['name'], $fileName );
                    MediaServerManager::MoveFile( $path, MediaServerManager::$TempLocation, MediaServerManager::$MainLocation );
                }
            }
        }

        public static function ConvertImageToJpg( $sourcePath ) {
            $fileFormat = ImageHelper::IsImage( $sourcePath );
            if( !in_array( $fileFormat, array( 'GIF' ) ) ) {
                return true;
            }

            $source = imagecreatefromgif( $sourcePath );
            $w      = imagesx($source);
            $h      = imagesy($source);

            $jpg    = imagecreatetruecolor($w, $h);

            imagecopyresampled($jpg, $source, 0, 0, 0, 0, $w, $h, $w, $h);

            imagejpeg($jpg, $sourcePath);

            imagedestroy($source);
            imagedestroy($jpg);
        }
    }
?>