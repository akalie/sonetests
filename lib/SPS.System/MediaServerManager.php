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
?>