<?php
    /**
    *  MediaServerManager
    */
    class MediaServerManager implements IModule {

		const HttpBadRequest    = 400;
		const HttpNoContent     = 204;

        private static $params = array();
        
        public static $Host		        = '';
        public static $MainLocation     = '';
        public static $TempLocation     = '';
        public static $UploadPort       = '';
        public static $UploadHost       = '';
        public static $ResizeExec       = '';
        public static $resizeCommands   = '';

		/**
         * Init
         *
         * @static
         * @param DOMNodeList $params
         * @return void
         */
		public static function Init( DOMNodeList $params ) {
            foreach ( $params as $param ) {
                /** @var DOMElement $param */
                self::$params[$param->getAttribute("name")] = $param->nodeValue;
            }
            self::$Host             = self::$params['host'];
            self::$MainLocation     = self::$params['mainLocation'];
            self::$TempLocation     = self::$params['tempLocation'];
            self::$UploadPort       = self::$params['uploadPort'];
            self::$UploadHost       = self::$params['uploadHost'];
            self::$ResizeExec       = self::$params['resizeExec'];
            self::$resizeCommands   = eval( 'return ' . self::$params['resizeCommands']  . ';' );
        }

		/**
		 * Put file to media server
		 *
		 * @static
		 * @param $filename (full path of file which we would like to save, e.g. /usr/local/tmp/test.html)
		 * @param $remoteFilename (path on remote server, e.g. '/user-avatars/original/4/40/409ab75f.jpg')
		 * @param string $location (e.g. self::$TempLocation)
		 * @return array
		 */
		public static function PutFile( $filename, $remoteFilename, $location = null ) {
            $location = !empty( $location ) ? $location : self::$MainLocation;

			$result = array(
				'success'           => false
				, 'remotePath'      => null
				, 'url'		        => null
				, 'errorMessage'    => null
				, 'httpCode'	    => null
				, 'isRewrited'      => false
			);

			if ( !is_file( $filename ) ) {
				return $result;
			}

			$options = array(
				CURLOPT_RETURNTRANSFER  => true
				, CURLOPT_PUT		    => true
				, CURLOPT_INFILE	    => fopen($filename, 'r')
				, CURLOPT_INFILESIZE    => filesize( $filename )
				, CURLOPT_URL		    => self::formatPath( $remoteFilename, $location )
				, CURLOPT_PORT		    => self::$UploadPort
			);

			$result = self::execCURL( $options );

			$result['remotePath'] = $remoteFilename;

			fclose( $options[CURLOPT_INFILE] );

			return $result;
		}

		/**
		 * Get file from media server
		 *
		 * @static
		 * @param string $filename (full path of file which would contain remote file, e.g. /usr/local/tmp/test.html)
		 * @param $remoteFilename (path on remote server, ex. '/user-avatars/original/4/40/409ab75f.jpg')
		 * @param string $location (e.g. self::$TempLocation)
		 * @return array
		 */
		public static function GetFile( $filename, $remoteFilename, $location = null ) {
            $location = !empty( $location ) ? $location : self::$MainLocation;

			$options = array(
				CURLOPT_FILE	=> fopen( $filename , 'w'),
				CURLOPT_URL	    => self::formatPath( $remoteFilename, $location )
			);

			$result = self::execCURL( $options );

			fclose( $options[CURLOPT_FILE] );

			return $result;
		}

		/**
		 * Delete file from media server
		 *
		 * @static
		 * @param $remoteFilename (path on remote server, ex. '/user-avatars/original/4/40/409ab75f.jpg')
		 * @param string $location (e.g. self::$TempLocation)
		 * @return array
		 */
		public static function DeleteFile( $remoteFilename, $location = null ) {
            $location = !empty( $location ) ? $location : self::$MainLocation;

			$options = array(
				CURLOPT_URL			    => self::formatPath( $remoteFilename, $location ),
				CURLOPT_PORT			=> self::$UploadPort,
				CURLOPT_HEADER		    => false,
				CURLOPT_RETURNTRANSFER  => true,
				CURLOPT_SSL_VERIFYPEER  => false,
				CURLOPT_CUSTOMREQUEST   => 'DELETE'
			);

			$result = self::execCURL( $options );

			return $result;
		}

		/**
		 * Init directories for file on media server
		 *
		 * @static
		 * @param $remoteFilename (path on remote server, ex. '/user-avatars/original/4/40/409ab75f.jpg')
		 * @param string $location (e.g. self::$TempLocation)
		 * @return void (will be created directory '/user-avatars/original/4/40/')
		 */
		public static function InitDirsForFile( $remoteFilename, $location = null ) {
            $location = !empty( $location ) ? $location : self::$MainLocation;

			$remoteEmptyFilename = dirname( $remoteFilename ) . '/.empty-file';

			$emptyFile = Site::GetRealPath( 'temp://.empty-file' );
			if( !is_file( $emptyFile ) ) {
				file_put_contents( $emptyFile, '' );
			}

			self::PutFile( $emptyFile, $remoteEmptyFilename, $location );
			self::DeleteFile( $remoteEmptyFilename, $location );
		}

		/**
		 * Move file on media server between two locations
		 *
		 * @static
		 * @param $remoteFilename (path on remote server, ex. '/user-avatars/original/4/40/409ab75f.jpg')
		 * @param $sourceLocation (e.g. self::$TempLocation)
		 * @param $targetLocation (e.g. self::$TempLocation)
		 * @return array
		 */
		public static function MoveFile( $remoteFilename, $sourceLocation, $targetLocation ) {
			$sourceFile = self::formatPath( $remoteFilename, $sourceLocation );
			$targetFile = self::formatPath( $remoteFilename, $targetLocation, true );

			//building directories for target file
			self::InitDirsForFile( $targetFile, $targetLocation );

			$options = array(
				CURLOPT_URL			    => $sourceFile,
				CURLOPT_RETURNTRANSFER  => true,
				CURLOPT_CUSTOMREQUEST   => 'MOVE',
				CURLOPT_PORT			=> self::$UploadPort,
				CURLOPT_HTTPHEADER	    => array( 'Destination: ' . $targetFile )
			);

			$result = self::execCURL( $options );

			return $result;
		}

		/**
		 * Get path of file ob media server
		 *
		 * @static
		 * @param $path (path on remote server, ex. '/user-avatars/original/4/40/409ab75f.jpg')
		 * @param string $location (use '' or self::$TempLocation)
		 * @param boolean $withoutHost
		 * @param string $host
		 * @return string
		 */
		private static function formatPath( $path, $location = null, $withoutHost = false, $host = null ) {
            $location   = !empty( $location ) ? $location : self::$MainLocation;
            $host       = !empty($host) ? $host : self::$UploadHost;
			return sprintf( '%s%s/%s', ( $withoutHost ) ? '' : $host, rtrim( $location, '/' ), trim( $path, '/' ) );
		}

		/**
		 * Execute CURL with given options
		 *
		 * @static
		 * @param array $options
		 * @return array
		 */
		private static function execCURL( $options ) {
			$ch = curl_init();
			curl_setopt_array($ch, $options);

			$output = curl_exec($ch);
			$info   = curl_getinfo($ch);

			$result = array(
				'success'	    => $info['http_code'] < self::HttpBadRequest,
				'url'		    => $options[CURLOPT_URL],
				'errorMessage'  => $info['http_code'] >= self::HttpBadRequest ? $output : '',
				'httpCode'	    => $info['http_code'],
				'isRewrited'	=> $info['http_code'] == self::HttpNoContent,
			);

			return $result;
		}

        public static function Resize( $original, $thumbnail, $max_width, $max_height, $quality, $scale = true ) {
            return ImageHelper::Resize( $original, $thumbnail, $max_width, $max_height, $quality, $scale );

            if( $scale == true ) {
                $cmd = sprintf(
                    '%s %s -resize %dx%d%s %s'
                    , self::$ResizeExec
                    , $original
                    , $max_width
                    , $max_height
                    , self::$resizeCommands['resize']
                    , $thumbnail
                );
            } else {
                $cmd = sprintf(
                    '%s %s -resize %dx%d%s -gravity center -extent %dx%d %s'
                    , self::$ResizeExec
                    , $original
                    , $max_width
                    , $max_height
                    , self::$resizeCommands['crop']
                    , $max_width
                    , $max_height
                    , $thumbnail
                );
            }

            exec( $cmd );
        }
	}
?><?php
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
?><?php
    /**
     * WatermarkUtility
     * @author Shuler
     * @package H4U.System
     */
    class WatermarkUtility {

        /**
         * path to default watermark image
         * @var string
         */
        public static $WatermarkPath = 'images://fe/watermark.png';

        /**
         * quality of gif and jpeg compression
         * @var int
         */
        public static $Quality = 95;

        /**
         * Place watermark on image
         * @static
         * @param $file full path for source image
         * @param $watermarkPath full real path of user watermark
         * @return bool
         */
        public static function PlaceWatermark( $file, $watermarkPath = null ) {
            $save = false;

            list($imageX, $imageY, $type) = getimagesize($file);
            $image                        = self::Load( $file, $type );
            $waterMarkFile                = !empty( $watermarkPath ) ? $watermarkPath : Site::GetRealPath( self::$WatermarkPath );
            list($sx, $sy)                = getimagesize($waterMarkFile);
            $waterMark                    = self::Load( $waterMarkFile, IMAGETYPE_PNG );

            $marginRight  = 0;
            $marginBottom = 0;

            imagecopy(
                $image,
                $waterMark,
                $imageX - $sx - $marginRight,
                $imageY - $sy - $marginBottom,
                0,
                0,
                $sx,
                $sy
            );

            switch ($type) {
                case IMAGETYPE_GIF:
                    $save = imagegif($image, $file, self::$Quality);
                    break;
                case IMAGETYPE_JPEG:
                    $save = imagejpeg($image, $file, self::$Quality);
                    break;
                case IMAGETYPE_PNG:
                    $save = imagepng($image, $file, 8);
                    break;
            }

            imagedestroy($image);
            imagedestroy($waterMark);

            return $save;
        }

        public static function GenerateWatermark( $text ) {
            //creating temp file
            if( !is_dir( Site::GetRealPath( 'temp://watermarks/' ) ) ) {
                mkdir( Site::GetRealPath( 'temp://watermarks/' ) );
            }
            $tmpFilename = Site::GetRealPath( 'temp://watermarks/' . md5( $text ) . time() . '.png' );

            // font
            $font = Site::GetRealPath( 'shared://fonts/arial.ttf' );

            //dimensions
            $bbox 	= imagettfbbox ( 20, 0, $font, $text );
            $width 	= $bbox[2] - $bbox[0] + 5;
            $height = - $bbox[5] - $bbox[3] + 15;

            // Create the image
            $im = imagecreatetruecolor($width, $height);

            //saving all full alpha channel information
            imagesavealpha($im, true);

            //setting completely transparent color
            $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);

            //filling created image with transparent color
            imagefill($im, 0, 0, $transparent);

            // Create some colors
            $white = imagecolorallocate($im, 255, 255, 255);
            $black = imagecolorallocate($im, 0, 0, 0);

            // Add some shadow to the text
            $x = 1;
            $y = $height - 8;
            imagettftext($im, 20, 0, $x + 1, $y + 1, $black, $font, $text);

            // Add the text
            imagettftext($im, 20, 0, $x, $y, $white, $font, $text);

            // Using imagepng() results in clearer text compared with imagejpeg()
            imagepng( $im, $tmpFilename );
            imagedestroy($im);

            return $tmpFilename;
        }

        /**
         * Load file by format
         *
         * @static
         * @param string $file
         * @param int $type
         * @return resource
         */
        public static function Load( $file , $type = IMAGETYPE_JPEG ) {
            $result = null;

            if ( $type == IMAGETYPE_GIF ) {
                $result = imagecreatefromgif($file);
            } else
            if ( $type == IMAGETYPE_JPEG ) {
                $result = imagecreatefromjpeg($file);
            } else
            if ( $type == IMAGETYPE_PNG ) {
                $result = imagecreatefrompng($file);
            }

            return $result;
        }
    }
?>