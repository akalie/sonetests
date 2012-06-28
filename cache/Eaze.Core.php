<?php
    class CacheManagerData {
        public $data = null;

        public function __construct( $data ) {
            $this->data = $data;
        }
    }

    /**
     * Cache Manager
     *
     * @package Eaze
     * @subpackage Core
     * @author sergeyfast
     */
    class CacheManager {
        public static function GetFileDefaultChecksum( $file ) {
            return self::GetFileChecksum( $file );
        }

        public static function GetFileTimestamp( $file ) {
            return filectime( $file );
        }

        public static function GetFileChecksum( $file ) {
            return  md5_file( $file );
        }

        /**
         * Get Cached XML Path
         *
         * @param string        $fileForCache    the file for cache
         * @param string        $patternToCache  the pattern for cache
         * @param array|string  $cacheFunction   the cached function for DOMDocument
         * @return string
         */
        public static function GetCachedXMLPath( $fileForCache, $patternToCache, $cacheFunction ) {
            $checksum  = self::GetFileDefaultChecksum( $fileForCache );
            $filePath  = sprintf( '%s/' . $patternToCache, CONFPATH_CACHE, $checksum );

            if ( file_exists( $filePath ) ) {
                return $filePath;
            }

            // Move to cache
            $doc = new DOMDocument();
            if ( ! $doc->load( $fileForCache ) ) {
                Logger::Error( 'Error while loading %s', $fileForCache );
                return null;
            }

            // return cached document
            call_user_func( $cacheFunction, $doc );
            Logger::Info( '%s recompiled to %s', $fileForCache, $filePath );

            self::ClearCache( str_replace( '%s', '(.*)', $patternToCache ));
            $doc->save( $filePath );

            return $filePath;
        }


        /**
         * Get Cached File Path
         *
         * @param string        $fileForCache    the file for cache
         * @param string        $patternToCache  the pattern for cache (sites_%s.xml)
         * @param array|string  $cacheFunction   the cached function
         * @param string        $filePrefix      the file prefix
         * @return string
         */
        public static function GetCachedFilePath( $fileForCache, $patternToCache, $cacheFunction, $filePrefix = null ) {
            $checksum = self::GetFileDefaultChecksum( $fileForCache );

            if ( empty( $filePrefix ) )  {
                $pathChecksum = md5( $fileForCache ) ;
            } else {
                $pathChecksum = $filePrefix;
            }

            $filePath  = sprintf( '%s/' . $patternToCache, CONFPATH_CACHE,  $pathChecksum, $checksum  );

            if ( file_exists( $filePath ) ) {
                return $filePath;
            }

            // return cached document
            $data = new CacheManagerData( file_get_contents($fileForCache) );

            call_user_func( $cacheFunction, $data );
            Logger::Info( '%s recompiled to %s', $fileForCache, $filePath );

            self::ClearCache( str_replace( '%s', '(.*)', sprintf( $patternToCache, $pathChecksum, '%s' )));
            file_put_contents( $filePath, $data->data );

            return $filePath;
        }


        /**
         * Clear Cache
         *
         * @param string $pattern  the regexp pattern
         */
        public static function ClearCache( $pattern = '(.+)xml' ) {
            $d = dir( CONFPATH_CACHE );

            while (false !== ($entry = $d->read())) {
                if ( substr($entry, 0, 1)=='.' ) continue;

                $filename = CONFPATH_CACHE .'/'. $entry;

                if ( preg_match( '?' . $pattern . '?i' , $entry ) ) {

                    Logger::Info( $pattern );
                    unlink( $filename );
                }
            }

            $d->close();
        }
    }
?><?php
    /**
     * Convert Class
     *
     * @package Eaze
     * @subpackage Core
     * @see http://ru2.php.net/manual/ru/types.comparisons.php
     * @see http://ru2.php.net/manual/ru/language.types.type-juggling.php
     * @author sergeyfast
     */
    class Convert implements IConvert {

        /**
         * Default TimeZone
         *
         * @var DateTimeZone
         */
        protected static $defaultTimeZone = null;


        /**
         * Converts value to string
         * @static
         * @param  mixed $value
         * @return string|null
         */
        public static function ToString( $value ) {
            if ( $value === null
                 || is_object( $value )
            ) {
                return null;
            }

            return (string) $value;
        }


        /**
         * Converts value to integer  if it isn't object, array, empty string or null
         * @static
         * @param  mixed $value
         * @return int|null
         */
        public static function ToInt( $value ) {
            if ( $value === null
                 || $value === ''
                 || $value === 'null'
                 || $value === 'NULL'
                 || is_array( $value )
                 || is_object( $value )
            ) {
                return null;
            }

            return (int) $value;
        }


        /**
         * @see ToInt()
         * @static
         * @param  mixed $value
         * @return int|null
         */
        public static function ToInteger( $value ) {
            return self::ToInt( $value );
        }


        /**
         * Convert::ToFloat
         * @static
         * @param  mixed $value
         * @return float|null
         */
        public static function ToDouble( $value ) {
            return self::ToFloat( $value );
        }


        /**
         * Converts value to float if it isn't object, array, empty string or null
         * @static
         * @param  mixed $value
         * @return float|null
         */
        public static function ToFloat( $value ) {
            if ( $value === null
                 || $value === ''
                 || $value === 'null'
                 || $value === 'NULL'
                 || is_array( $value )
                 || is_object( $value )
            ) {
                return null;
            }

            return (float) $value;
        }


        /**
         * Converts value to bool (strings 'false' or 'f' are interpreted as false);
         * @static
         * @param  mixed $value
         * @return bool|null
         */
        public static function ToBoolean( $value ) {
            if ( $value === null
                 || $value === ''
                 || $value === 'null'
                 || $value === 'NULL' )
            {
                return null;
            }

            if ( is_string( $value ) ) {
                $result = strtolower( $value );
                if ( $result === 'false' || $result === 'f' ) {
                    return false;
                }
            }

            return (bool) $value;
        }


        /**
         * Converts Value to Array
         * @static
         * @param  mixed $value
         * @return array
         */
        public static function ToArray( $value ) {
            $result = (array) $value;

            return $result;
        }


        /**
         * Converts value to stdClass
         * @static
         * @param  mixed $value
         * @return stdClass
         */
        public static function ToObject( $value ) {
            $result = (object) $value;

            return $result;
        }


        /**
         * Converts Value to DateTime
         * @static
         * @param  $value
         * @param DateTimeZone|null $zone optional
         * @return DateTimeWrapper|null
         */
        public static function ToDateTime( $value, DateTimeZone $zone = null ) {
            if ( empty( $zone ) ) {
                if ( empty ( self::$defaultTimeZone ) ) {
                    self::$defaultTimeZone = new DateTimeZone( DEFAULT_TIMEZONE );
                }

                $zone = self::$defaultTimeZone;
            }

            $className = is_object( $value ) ? strtolower( get_class( $value ) ) : null;
            if ( 'datetime' == $className || 'datetimewrapper' == $className ) {
                return $value;
            } elseif ( $value !== null ) {
                $string = $value;

                try {
                    return new DateTimeWrapper( $string, $zone );
                } catch ( Exception $ex ) {
                    return null;
                }
            }

            return null;
        }


        /**
         * Returns value (stub)
         * @static
         * @param  mixed $value
         * @return mixed
         */
        public static function ToParameter( &$value ) {
            return $value;
        }


        /**
         * @see ToDateTime()
         * @static
         * @param  mixed  $value
         * @param DateTimeZone|null $zone
         * @return DateTimeWrapper|null
         */
        public static function ToDate( $value, DateTimeZone $zone = null ) {
            return self::ToDateTime( $value, $zone );
        }


        /**
         * @see ToDateTime
         * @static
         * @param  mixed $value
         * @param DateTimeZone|null $zone
         * @return DateTimeWrapper|null
         */
        public static function ToTime( $value, DateTimeZone $zone = null ) {
            return self::ToDateTime( $value, $zone );
        }


        /**
         * Convert value To Type
         *
         * @param mixed  $value
         * @param string $type
         * @return mixed
         */
        public static function ToValue( $value, $type = TYPE_PARAMETER ) {
            switch ( $type ) {
                case TYPE_STRING:
                    return Convert::ToString( $value );
                case TYPE_INTEGER:
                    return Convert::ToInt( $value );
                case TYPE_FLOAT:
                    return Convert::ToFloat( $value );
                case TYPE_BOOLEAN:
                    return Convert::ToBoolean( $value );
                case TYPE_DATE:
                case TYPE_TIME:
                case TYPE_DATETIME:
                    return Convert::ToDateTime( $value );
                case TYPE_ARRAY:
                    return Convert::ToArray( $value );
                case TYPE_OBJECT:
                    return Convert::ToObject( $value );
                case TYPE_PARAMETER:
                    return Convert::ToParameter( $value );
                default:
                    return $value;
            }
        }
    }
?><?php
    Package::Load( 'Eaze.Core' );

    /**
     * Cookie
     *
     * @package Eaze
     * @subpackage Core
     * @author sergeyfast
     */
    class Cookie {

        /**
         * Initialized flag
         *
         * @var boolean
         */
        public static $Initialized = false;

        /**
         * Cookie Parameters
         *
         * @var ParamObject
         */
        private static $paramObject;


        /**
         * Get or Set Value
         *
         * @param string $mode     the mode
         * @param string $key      the key value
         * @param mixed  $value    the value
         * @param string $type     value type (string,int,etc..)
         */
        private static function value( $mode = MODE_GET, $key, $value = null, $type = null ) {
            if ( true == isset( self::$paramObject ) ) {
                return self::$paramObject->value( $mode, $key, $value, $type );
            }

            return null;
        }


        /**
         * Init Cookies
         */
        public static function Init() {
            if ( ! self::$Initialized ) {
                self::$Initialized = true;
                Request::Init();

                self::$paramObject = new ParamObject( $_COOKIE );
            }
        }


        /**
         * Send a cookie
         *
         * @param string  $name     The name of the cookie
         * @param string  $value    The value of the cookie. This value is stored on the clients computer; do not store sensitive information.
         * @param int     $expires  The time the cookie expires. time()+60*60*24*30 will set the cookie to expire in 30 days. If set to 0, or omitted, the cookie will expire at the end of the session (when the browser closes).
         * @param string  $path     The path on the server in which the cookie will be available on. If set to '/', the cookie will be available within the entire domain.
         * @param string  $domain   The domain that the cookie is available. To make the cookie available on all subdomains of example.com then you'd set it to '.example.com'. The . is not required but makes it compatible with more browsers. Setting it to www.example.com will make the cookie only available in the www subdomain.
         * @param bool    $secure   Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client. When set to TRUE, the cookie will only be set if a secure connection exists. The default is FALSE. On the server-side, it's on the programmer to send this kind of cookie only on secure connection (e.g. with respect to $_SERVER["HTTPS"]).
         * @param bool    $httponly When TRUE the cookie will be made accessible only through the HTTP protocol. This means that the cookie won't be accessible by scripting languages, such as JavaScript. This setting can effectly help to reduce identity theft through XSS attacks (although it is not supported by all browsers). Added in PHP 5.2.0.
         * @return bool
         */
        public static function setCookie( $name, $value = null, $expires = null, $path = null, $domain = null, $secure = false, $httponly = true ) {
            $file = null;
            $line = null;

            $value = Convert::ToString( $value );

            if ( headers_sent( $file, $line ) ) {
                Logger::Error( 'Headers have been sent already by %s:%d' ,$file, $line );
                return false;
            }

            return setcookie( $name, $value, $expires, $path, $domain, $secure, $httponly );
        }


        /** Getters -------------------------------------------------------------- */
        public static function getInteger( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_INTEGER );
        }

        public static function getBoolean( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_BOOLEAN );
        }

        public static function getString( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_STRING );
        }

        public static function getFloat( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_FLOAT );
        }

        public static function getArray( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_ARRAY );
        }

        public static function getObject( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_OBJECT );
        }

        public static function getParameter( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_PARAMETER );
        }

        public static function getDateTime( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_DATETIME );
        }

        public static function getParameters() {
            if ( true == isset( self::$paramObject ) ) {
                return self::$paramObject->getParameters();
            }

            return null;
        }
    }
?><?php
    define( 'DEFAULT_TIMEZONE', date_default_timezone_get() );

    /**
     * DateTimeWrapper
     *
     * @package Eaze
     * @subpackage Core
     * @author sergeyfast
     */
    class DateTimeWrapper extends DateTime {

        /**
         * Date in ISO 8601 for serialization
         * @var string
         */
        private $timestamp;

        /**
         * Is Null (or now)
         * @var bool
         */
        private $isNull = false;


        /**
         * Construct
         *
         * @param string       $value
         * @param DateTimeZone $zone
         */
        public function __construct( $value, $zone = null ) {
            if ( empty( $zone ) ) {
                $zone = new DateTimeZone( DEFAULT_TIMEZONE );
            }

            if ( is_null( $value ) ) {
                $this->isNull = true;
            }

            parent::__construct( $value, $zone );
        }


        /**
         * Output DateTime as ISO 8601
         * @return string
         */
        public function __toString() {
            return $this->format( 'c' );
        }


        /**
         * Sleep
         * @return array
         */
        public function __sleep() {
            $this->timestamp = $this->format( 'c' );
            return array( 'timestamp' );
        }


        /**
         * Wake Up
         * @return void
         */
        public function __wakeup() {
            $this->__construct($this->timestamp);
        }


        /**
         * Clone DateTime Wrapper
         * @return DateTimeWrapper
         */
        public function __clone() {
            return new DateTimeWrapper( $this->format( 'c' ) );
        }


        /**
         * Return "Now"
         *
         * @static
         * @return DateTimeWrapper
         */
        public static function Now() {
            return new DateTimeWrapper( 'now' );
        }


        /**
         * Compare To
         * @deprecated use <>= instead
         * @param DateTimeWrapper $object
         * @return -1   This instance is less than obj.
         * @return 0    This instance is equal to obj.
         * @return 1    This instance is greater than obj.
         */
        public function CompareTo( $object ) {
            $instance = $this->format( "U" );
            $obj      = $object->format( "U" );

            if ( $instance < $obj ) {
                return -1;
            } else if ( $instance > $obj ) {
                return 1;
            } else {
                return 0;
            }
        }


        /**
         * Returns d.m.Y G:i
         * @param string $format
         * @return string
         */
        public function DefaultFormat( $format = 'd.m.Y G:i' ) {
            return $this->format( $format );
        }


        /**
         * Returns d.m.Y H:i
         * @param string $format
         * @return void
         */
        public function Default24hFormat( $format = 'd.m.Y H:i' ) {
            return $this->format( $format );
        }


        /**
         * Returns G:i
         * @param string $format
         * @return void
         */
        public function DefaultTimeFormat( $format = 'G:i' ) {
            return $this->format( $format );
        }


        /**
         * Returns d.m.Y
         * @param string $format
         * @return void
         */
        public function DefaultDateFormat( $format = 'd.m.Y' ) {
            return $this->format( $format );
        }


        /**
         * Checks if DateTime got null value in constructor
         * @return bool
         */
        public function IsNull() {
            return $this->isNull;
        }
    }
?><?php
    /**
     * DirectoryInfo
     *
     * @package Eaze
     * @subpackage Core
     * @author sergeyfast
     */
    class DirectoryInfo {

        /**
         * Directory Path
         *
         * @var string
         */
        private $directoryPath = '';

        /**
         * Root Dir
         *
         * @var string
         */
        private $rootDirectory = '';


        /**
         * Create Instance
         *
         * @param string $directoryPath
         * @return DirectoryInfo
         */
        public function __construct( $directoryPath ) {
            if ( !is_null( $directoryPath ) && is_dir( $directoryPath ) ) {
                $this->directoryPath = realpath( $directoryPath );
            }
        }


        /**
         * Get Extension
         *
         * @static
         * @param string $fileName
         * @return string
         */
        public static function GetExtension( $fileName ) {
            $pointPosition = mb_strrpos( $fileName, "." );

            if ( false === $pointPosition ) {
                return null;
            }

            $extension = mb_strtolower( mb_substr( $fileName, $pointPosition + 1 ) );

            return $extension;
        }


        /**
         * Get Instance
         *
         * @param string $rootDirectory
         * @param string $relativeDirectory
         * @return DirectoryInfo
         */
        public static function GetInstance( $rootDirectory, $relativeDirectory = null ) {
            if ( !is_dir( $rootDirectory ) ) {
                return null;
            }


            if ( !empty( $relativeDirectory ) ) {
                $rootDirectory .= str_replace( '..', '', $relativeDirectory );

                return DirectoryInfo::GetInstance( $rootDirectory );
            } else {
                $directory = new DirectoryInfo( $rootDirectory );
            }

            return $directory;
        }


        /**
         * Create Sub Dir
         *
         * @param string $newDirectory
         * @return bool
         */
        public function CreateSubDirectory( $newDirectory ) {
            if ( ( is_null( $this->directoryPath ) )
                 || ( empty( $newDirectory ) )
            ) {
                return ( null );
            }

            $path = sprintf( '%s/%s', $this->directoryPath, $newDirectory );

            if ( file_exists( $path ) || is_dir( $path ) ) {
                return false;
            }

            $result = mkdir( $path );

            return $result;
        }


        /**
         * Get Page Count
         *
         * @param integer $pageSize
         * @return float
         */
        public function Count( $pageSize = 10 ) {
            $count = $this->getCount();

            if ( !empty( $count ) ) {
                $count = 0;
            }

            return $count / $pageSize;
        }


        /**
         * Get Count
         *
         * @return integer
         */
        private function getCount() {
            if ( is_null( $this->directoryPath ) ) {
                return null;
            }

            $i = 0;

            if ( $handle = opendir( $this->directoryPath ) ) {
                while ( false !== ( $file = readdir( $handle ) ) ) {
                    if ( ( $file != "." )
                         && ( $file != ".." )
                    ) {
                        $i++;
                    }
                }

                closedir( $handle );
            }

            return $i;
        }


        /**
         * Get Files And Folders
         * @param int $page
         * @param int $pageSize
         *
         * @return array
         */
        public function GetAll( $page = 0, $pageSize = 10 ) {
            if ( is_null( $this->directoryPath ) ) {
                return null;
            }

            $list = array();
            $i = 0;

            if ( $handle = opendir( $this->directoryPath ) ) {
                while ( false !== ( $file = readdir( $handle ) ) ) {
                    if ( ( $file != "." )
                         && ( $file != ".." )
                    ) {
                        /// Check Pages
                        $start = $page * $pageSize;
                        $end = $start + $pageSize;

                        if ( ( !empty( $start ) ) && ( $i < $start ) ) {
                            $i++;
                            continue;
                        }
                        if ( ( !empty( $end ) ) && ( $i >= $end ) ) {
                            break;
                        }

                        /// Get Files       
                        $file = $this->directoryPath . "/" . $file;

                        $list[$i] = array(
                            'path'       => $file
                            , 'fullName' => basename( $file )
                        );

                        // Check File Options
                        if ( is_file( $file ) ) {
                            $list[$i]['isDir']     = false;
                            $list[$i]['name']      = basename( $file, "." . $this->getExtension( $file ) );
                            $list[$i]['extension'] = $this->getExtension( $file );
                            $list[$i]['size']      = filesize( $file );
                        } else {
                            $list[$i]['isDir']     = true;
                        }

                        $i++;
                    }
                }

                closedir( $handle );
            }

            return $list;
        }


        /**
         * Returns file list with specified mask
         *
         * @param string $mask
         * @return array
         */
        public function GetFiles( $mask = null ) {
            if ( is_null( $this->directoryPath ) ) {
                return null;
            }

            $files = array();

            if ( $handle = opendir( $this->directoryPath ) ) {
                while ( false !== ( $file = readdir( $handle ) ) ) {
                    if ( ( $file != "." )
                         && ( $file != ".." )
                         && ( is_file( $this->directoryPath . "/" . $file ) )
                    ) {
                        if ( ( !empty( $mask ) )
                             && ( strpos( $file, $mask ) === false )
                        ) {
                            continue;
                        }

                        $files[] = array(
                            'filename'    => $file
                            , 'path'      => realpath( $this->directoryPath . "/" . $file )
                            , 'name'      => basename( $file, "." . $this->getExtension( $file ) )
                            , 'extension' => $this->getExtension( $file )
                        );
                    }
                }

                closedir( $handle );
            }

            return $files;
        }


        /**
         * Get Directory Path
         *
         * @return string
         */
        public function GetDirectoryPath() {
            return $this->directoryPath;
        }


        /**
         * Reformat Path
         *
         * @param string $path
         * @return string
         */
        public static function FormatPath( $path ) {
            return str_replace( '\\', '/', $path );
        }


        /**
         * Get Relative Path
         *
         * @param string $dir
         * @return string
         */
        public function GetRelativePath( $dir = null ) {
            $rootDir = self::FormatPath( $this->rootDirectory );
            $newDir = self::FormatPath( $this->directoryPath . '/' . $dir );

            $result = str_replace( $rootDir, '/', $newDir );

            return $result;
        }


        /**
         * Get Parent Path
         *
         * @return string
         */
        public function GetParentPath() {
            $rootDir = self::FormatPath( $this->rootDirectory );
            $newDir = self::FormatPath( $this->directoryPath . "/" );

            $result = str_replace( $rootDir, '/', $newDir );

            $result = self::FormatPath( dirname( $result ) );
            return $result;
        }
    }

?><?php
    /**
 * File Info
 *
 * @package Eaze
 * @subpackage Core
 * @author sergeyfast
 */
    class FileInfo {

        /**
         * File Name
         *
         * @var string
         */
        private $fileName = '';


        /**
         * Constructor
         *
         * @param string $fileName  the file path
         */
        public function __construct( $fileName = null ) {
            if ( !is_null( $fileName ) && file_exists( $fileName ) ) {
                $this->fileName = realpath( $fileName );
            }
        }


        /**
         * Copy To
         *
         * @param string $targetFileName
         * @return bool
         */
        public function CopyTo( $targetFileName ) {
            if ( !file_exists( $this->fileName ) ) {
                return false;
            }

            $result = copy( $this->fileName, $targetFileName );

            return $result;
        }


        /**
         * Move To
         *
         * @param string $targetFileName
         * @return bool
         */
        public function MoveTo( $targetFileName ) {
            if ( !$this->CopyTo( $targetFileName ) ) {
                return false;
            }

            if ( !unlink( $this->fileName ) ) {
                return false;
            }

            $this->fileName = realpath( $targetFileName );

            return true;
        }


        /**
         * Get Extension
         *
         * @return string
         */
        public function GetExtension() {
            return DirectoryInfo::GetExtension( $this->fileName );
        }


        /**
         * Get Type
         *
         * @return string
         */
        public function GetType() {
            $type = false;
            //$type = mime_content_type( $this->fileName );
            if ( !$type ) {
                $type = "application/octet-stream";
            }

            return $type;
        }


        /**
         * Return File name
         *
         * @return string file name
         */
        public function GetName() {
            return basename( $this->fileName );
        }


        /**
         * Get Instance
         *
         * @static
         * @param string $fileName
         * @return FileInfo
         */
        public static function GetInstance( $fileName ) {
            if ( !file_exists( $fileName ) ) {
                return null;
            }

            $file = new FileInfo( $fileName );

            return $file;
        }


        /**
         * Get Full Name
         *
         * @return string
         */
        public function GetFullName() {
            return $this->fileName;
        }


        /**
         * Get Base Directory
         *
         * @return string full directory path
         */
        public function GetBaseDirectory() {
            return dirname( $this->fileName );
        }


        /**
         * Get File Size
         *
         * @return int
         */
        public function GetFileSize() {
            $size = filesize( $this->fileName );

            return $size;
        }


        /**
         * Delete filename
         * @return bool
         */
        public function Delete() {
            if ( !file_exists( $this->fileName ) ) {
                return false;
            }

            return unlink( $this->fileName );
        }
    }
?><?php
    define( 'TYPE_STRING',    'string' );
    define( 'TYPE_INTEGER',   'integer' );
    define( 'TYPE_FLOAT',     'float' );
    define( 'TYPE_BOOLEAN',   'boolean' );
    define( 'TYPE_ARRAY',     'array' );
    define( 'TYPE_OBJECT',    'object' );
    define( 'TYPE_RESOURCE',  'resource' );
    define( 'TYPE_PARAMETER', 'parameter' );
    define( 'TYPE_DATETIME',  'dateTime' );
    define( 'TYPE_TIME',      'time' );
    define( 'TYPE_DATE',      'date' );
    define( 'TYPE_LTREE',     'ltree' );
    
    define( 'MODE_GET',       'get' );
    define( 'MODE_SET',       'set' );
    
    interface IConvert {
    
        static function ToString( $value );
        static function ToInt( $value );
        static function ToInteger( $value );
        static function ToDouble( $value );
        static function ToFloat( $value );
        static function ToBoolean( $value );
        static function ToArray( $value );
        static function ToObject( $value );
        static function ToDateTime( $value, DateTimeZone $zone = null );
        static function ToDate( $value, DateTimeZone $zone = null );
        static function ToTime( $value, DateTimeZone $zone = null );
        static function ToParameter( &$value );
    }
?><?php
    define( 'ELOG_NONE',    0 );
    define( 'ELOG_FATAL',   1 );
    define( 'ELOG_ERROR',   2 );
    define( 'ELOG_WARNING', 3 );
    define( 'ELOG_INFO',    4 );
    define( 'ELOG_DEBUG',   5 );

    /**
     * Logger
     *
     * @package Eaze
     * @subpackage Core
     * @author sergeyfast
     */
    class Logger {

        /**
         * Html Output mode
         */
        const HtmlMode = 'html';


        /**
         * Fire PHP Output mode
         */
        const FirePHPMode = 'fp';

        /**
         * Text Output Mode
         */
        const TextMode = 'text';

        /**
         * Output Mode
         * Can be html or fb.
         *
         * @var string
         */
        private static $outputMode = self::HtmlMode;

        /**
         * Checkpoints
         *
         * @var array
         */
        private static $checkpoints = array();

        /**
         * Current Checkpoint Level
         *
         * @var integer
         */
        private static $currentLevel = 0;

        /**
         * Current Log Level
         * Default is ELOG_NONE
         *
         * @var int
         */
        private static $logLevel = 0;

        /**
         * Start Time
         *
         * @var float
         */
        private static $startTime = 0;


        /**
         * Is First output (used for inline styles)
         * @var bool
         */
        private static $isFirstOutput = false;

        /**
         * Flag is used by {@see getCallingMethod} to decrease calling method.
         * After {@see getCallingMethod} is done, flag will reset automatically.
         * @var bool
         */
        private static $decreaseCallingMethod = false;


        /**
         * Initialize Logger
         * @param int $logLevel     ELOG_* constant (0..5)
         * @param string $outputTo  self::*Mode constant (text|html|fb)
         */
        public static function Init( $logLevel = ELOG_NONE, $outputTo = self::HtmlMode ) {
            Logger::$startTime = Logger::GetCurrentTime();

            Logger::LogLevel( $logLevel );
            Logger::OutputTo( $outputTo );
        }

        /**
         * Set Log Level
         *
         * @param integer $logLevel
         */
        public static function LogLevel( $logLevel ) {
            Logger::$logLevel = $logLevel;
        }


        /**
         * Get Current Log Level
         * @static
         * @return int
         */
        public static function GetCurrentLevel() {
            return Logger::$logLevel;
        }


        /**
         * Set Output mode
         *
         * @param string $mode html|fb|text
         */
        public static function OutputTo( $mode ) {
            self::$outputMode = $mode;
        }


        /**
         * Get Output Mode
         * @static
         * @return string
         */
        public static function GetOutputMode() {
            return self::$outputMode;
        }


        /**
         * Set Checkpoint
         */
        public static function Checkpoint() {
            Logger::$checkpoints[Logger::$currentLevel++] = array(
                'time'     => Logger::GetCurrentTime()
                , 'memory' => Logger::GetCurrentMemoryUsage()
            );
        }


        public static function Debug( $message, $_ = null )  {
            if ( Logger::$logLevel >= ELOG_DEBUG ) {
                $params = func_get_args();
                Logger::log( ELOG_DEBUG, $message, $params );
            }
        }

        public static function Info( $message, $_ = null ) {
            if ( Logger::$logLevel >= ELOG_INFO ) {
                $params = func_get_args();
                Logger::log( ELOG_INFO, $message, $params );
            }
        }


        public static function Warning( $message, $_ = null ) {
            if ( Logger::$logLevel >= ELOG_WARNING ) {
                $params = func_get_args();
                Logger::log( ELOG_WARNING, $message, $params );
            }
        }


        public static function Error( $message, $_ = null ) {
            if ( Logger::$logLevel >= ELOG_ERROR ) {
                $params = func_get_args();
                Logger::log( ELOG_ERROR, $message, $params );
            }
        }


        public static function Fatal( $message, $_ = null ) {
            if ( Logger::$logLevel >= ELOG_FATAL ) {
                $params = func_get_args();
                Logger::log( ELOG_FATAL, $message, $params );
            }
        }


        /**
         * Format Position
         *
         * @static
         * @param  array $trace backtrace element
         * @return string
         */
        private static function getCallingMethodString( $trace ) {
            return ( !empty( $trace['class'] ) ? $trace['class'] : '' )
                . ( !empty( $trace['type'] ) ? htmlentities( $trace['type'] ) : '' )
                . ( !empty( $trace['function'] ) ? $trace['function'] : '' );
        }


        /**
         * Flush Html Inline Style for Debug
         * @static
         * @return void
         */
        private static function flushHtmlInlineStyle() {
            $xhtml = <<<css
                <style type="text/css">
                    .eaze-logger {
                        border: 1px dotted #ccc;
                        background: #fff;
                        color: #333;
                        font-size: 11px;
                        font-family: Arial, Helvetica, sans-serif;
                        list-style-type: none;
                        left: 0;
                        margin: -1px 0 0;
                        overflow: auto;
                        padding: 3px 0;
                        top: 0;
                        width: 100%;
                        z-index: 1000;
                    }
                        .eaze-logger-info {
                            background: #fff;
                        }
                        .eaze-logger-error, .eaze-logger-fatal, .eaze-logger-warning {
                            background: #ffd9d9;
                        }
                        .eaze-logger-debug {
                            background: #dff7e3;
                        }
                        .eaze-logger div.time-before,
                        .eaze-logger div.time-after {
                            color: #666;
                            float: left;
                            overflow: hidden;
                            margin-right: 1em;
                            -o-text-overflow: ellipsis;
                            text-overflow: ellipsis;
                            width: 4.5em;
                        }
                        .eaze-logger div.time-after {
                            float: right;
                        }
                        .eaze-logger div.memory-before,
                        .eaze-logger div.memory-after {
                            color: #666;
                            float: left;
                            overflow: hidden;
                            margin-right: 1em;
                            text-overflow: ellipsis;
                            width: 4.7em;
                        }
                        .eaze-logger div.memory-after {
                            float: right;
                        }
                        .eaze-logger div.type {
                            color: #000;
                            font-weight: bold;
                            float: left;
                            padding-left: 1em;
                            width: 6em;
                        }
                        .eaze-logger div.text {
                            color: #000;
                            margin: 0 13em 0 18.5em;
                        }
                            .eaze-logger div.text pre {
                                font-size: 11px;
                                font-family: monospace;
                                color: #444;
                                margin: 0.5em 0;
                            }
                </style>
css;

            echo $xhtml;
        }


        /**
         * Save Log
         * @static
         * @param  array $result
         * @return void
         */
        private static function saveLog( $result ) {
            switch (self::$outputMode ) {
                case self::HtmlMode:
                    $checkPointTemplate = <<<xhtml
            <div class="time-after">%2.4f</div>
            <div class="memory-after">%2.4f Mb</div>
xhtml;
                    $messageTemplate = <<<xhtml
            <div class="eaze-logger eaze-logger-%s">
                <div class="time-before">%2.4f</div>
                <div class="memory-before">%2.4f Mb</div>
                <div class="type">%s</div>
                %s
                <div class="text">%s<strong>%s</strong> %s</div>
            </div>
xhtml;
                    if ( !self::$isFirstOutput ) {
                        self::$isFirstOutput = true;
                        self::flushHtmlInlineStyle();
                    }

                    // message
                    printf( $messageTemplate
                        , strtolower( $result['levelName'] )
                        , $result['relativeTime']
                        , $result['memoryUsage']
                        , $result['levelName']
                        , !empty( $result['checkPoint'] ) ? sprintf( $checkPointTemplate, $result['checkPoint'], $result['memPoint'] ) : ''
                        , $result['indentLevel']
                        , self::getCallingMethodString( $result['trace'] )
                        , $result['message']
                    );

                    break;
                case self::FirePHPMode:
                     $message =  sprintf( '[%2.4f]%s [%s Mb] %s >> %s '
                        , $result['relativeTime']
                        , $result['indentLevel']
                        , number_format( $result['memoryUsage'], 3 )
                        , $result['levelName']
                        , $result['message']
                    );

                    if ( ! empty( $result['checkPoint'] ) ) {
                        $message .= sprintf( ' [%f] [%s Mb]', $result['checkPoint'], number_format( $result['memPoint'], 3 )  );
                    }

                    fb( $message, self::convertLogLevelToFB($result['logLevel']));

                    break;
                case self::TextMode:
                    printf( '[%2.4f] [%s Mb] %s >> %s: %s %s' . PHP_EOL
                        , $result['relativeTime']
                        , $result['memoryUsage']
                        , $result['levelName']
                        , html_entity_decode( self::getCallingMethodString( $result['trace'] ) )
                        , $result['message']
                        , !empty( $result['checkPoint'] ) ? sprintf( '[%f] [%s Mb]', $result['checkPoint'], $result['memPoint'] ) : ''
                    );

                    break;
            }
        }


        /**
         * Convert LogLevel to FirePHP LogLevel
         * @static
         * @param  int $logLevel
         * @return string
         */
        private static function convertLogLevelToFB( $logLevel ){
            switch ($logLevel){
                case ELOG_DEBUG:
                case ELOG_NONE:
                    return FirePHP::LOG;
                case ELOG_INFO:
                    return FirePHP::INFO;
                case ELOG_WARNING:
                    return FirePHP::WARN;
                case ELOG_ERROR:
                case ELOG_FATAL:
                    return FirePHP::ERROR;
            }
        }


        /**
         * Log Action
         * @static
         * @param  int     $logLevel
         * @param  string  $message   debug message
         * @param  array   $args      arguments to sprintf (0 is index of message)
         * @return void
         */
        private static function log( $logLevel, $message, $args = array() ) {
            if ( Logger::$logLevel >= $logLevel ) {
                $trace = self::getCallingMethod();

                if ( !empty( $args ) && count( $args ) > 1 ) {
                    $message = vsprintf( $message, array_slice( $args, 1  ) );
                }

                $result = array(
                    'relativeTime'  => Logger::GetRelativeTime()
                    , 'memoryUsage' => Logger::GetCurrentMemoryUsage()
                    , 'indentLevel' => str_repeat( '&nbsp;', ( (Logger::$currentLevel - 1 < 0) ? 0 : Logger::$currentLevel - 1  ) * 10 )
                    , 'levelName'   => Logger::GetLevelName( $logLevel )
                    , 'message'     => $message
                    , 'logLevel'    => $logLevel
                    , 'datetime'    => date( 'c' )
                    , 'checkPoint'  => (Logger::$currentLevel > 0 ) ? Logger::getCheckpointTime( Logger::$currentLevel - 1 ) : null
                    , 'memPoint'    => (Logger::$currentLevel > 0 ) ? Logger::getCheckpointMemory( Logger::$currentLevel - 1 ) : null
                    , 'trace'       => $trace
                );

                Logger::saveLog( $result );

                if ( self::$logLevel >= $logLevel && $logLevel <= ELOG_ERROR ) {
                    Logger::Backtrace();
                }
            }

            // Flush Check point
            if ( Logger::$currentLevel > 0 ) {
                Logger::$currentLevel --;
                array_pop( Logger::$checkpoints );
            }
        }


        /**
         * Get Current Time
         *
         * @return float
         */
        public static function GetCurrentTime() {
            return microtime( true );
        }

        /**
         * Get Checkpoint Time
         *
         * @access public
         * @return float
         */
        public static function GetRelativeTime() {
            return round( (float)( Logger::GetCurrentTime() - (float) Logger::$startTime ), 6 );
        }


        /**
         * Get Relative Time To
         * @param $time
         *
         * @return float
         */
        public static function GetRelativeTimeTo( $time ) {
            return round( (float)( Logger::GetCurrentTime() - (float) $time ), 6 );
        }


        /**
         * Get Checkpoint Time.
         *
         * @return float
         */
        private static function getCheckpointTime( $level ) {
            return Logger::GetCurrentTime() - Logger::$checkpoints[$level]['time'];
        }

        /**
         * Gets Checkpoint Memory Usage.
         *
         * @param int $level  Checkpoint level
         * @return int
         */
        private static function getCheckpointMemory( $level ) {
            return ( Logger::GetCurrentMemoryUsage() - Logger::$checkpoints[$level]['memory'] );
        }

        /**
         * Get Current Memory Usage
         * @static
         * @return float MBytes
         */
        public static function GetCurrentMemoryUsage() {
            return round( ( (float)memory_get_usage()/1024 / 1024 ), 3 );
        }


        /**
         * Get Level Name
         *
         * @param integer $logLevel
         * @return string
         */
        public static function GetLevelName( $logLevel ) {
            switch ( $logLevel ) {
                case ELOG_DEBUG:
                    return 'DEBUG';
                case ELOG_INFO:
                    return 'INFO';
                case ELOG_WARNING:
                    return 'WARNING';
                case ELOG_ERROR:
                     return 'ERROR';
                case ELOG_FATAL:
                     return 'FATAL';
                default:
                    return 'NONE';
            }
        }


        /**
         * Print Backtrace
         */
        public static function Backtrace() {
            ob_start();
            debug_print_backtrace();
            $trace = ob_get_contents();
            ob_end_clean();

            $result = $trace;
            switch ( self::$outputMode ) {
                case self::HtmlMode:
                    $result = <<<xhtml
                    <div class="eaze-logger eaze-logger-info">
                        <div class="type">Backtrace</div>
                        <div class="text"><pre>{$trace}</pre></div>
                    </div>
xhtml;
                    if ( !self::$isFirstOutput ) {
                        self::$isFirstOutput = true;
                        self::flushHtmlInlineStyle();
                    }
                    break;
            }

            echo $result;
        }


        /**
         * Get Calling Method
         * @static
         * @param int $level
         * @return array
         */
        private static function getCallingMethod( $level = 3 ) {
            if ( self::$decreaseCallingMethod ) {
                $level ++;
                self::$decreaseCallingMethod = false;
            }

            $trace  = debug_backtrace();
            if ( !empty( $trace[$level] ) ) {
                $result             = $trace[$level];
                $result['fromLine'] = !empty( $trace[$level - 1] ) ? $trace[$level - 1]['line'] : $result['line'];
            } else {
                $result             = $trace[$level - 1];
                $result['fromLine'] = !empty( $trace[$level - 2] ) ? $trace[$level - 2]['line'] : $result['line'];
            }

            return $result;
        }


        /**
         * Print R
         * @static
         * @param mixed $value
         */
        public static function PrintR( $value ) {
            $trace =  self::getCallingMethod( 2 );

            ob_start();
            print_r( $value );
            $result = ob_get_contents();
            ob_end_clean();

            $template = <<<xhtml
            <div class="eaze-logger eaze-logger-info">
                <div class="type">PrintR</div>
                <div class="text"><strong>%s</strong> at line %d <pre>%s</pre></div>
            </div>
xhtml;

            if ( !self::$isFirstOutput ) {
                self::$isFirstOutput = true;
                self::flushHtmlInlineStyle();
            }

            printf( $template, self::getCallingMethodString( $trace ), $trace['fromLine'], $result );
        }


        /**
         * Var_dump
         *
         * @param mixed  $value
         * @param array $_z
         */
        public static function VarDump( $value, $_ = array() ) {
            $trace =  self::getCallingMethod( 2 );

            ob_start();
            switch( func_num_args() ) {
                case 1:
                    var_dump( $value );
                    break;
                case 0:

                    break;
                default:
                    var_dump( func_get_args() );
                    break;
            }

            $result = ob_get_contents();
            ob_end_clean();

            $template = <<<xhtml
            <div class="eaze-logger eaze-logger-info">
                <div class="type">VarDump</div>
                <div class="text"><strong>%s</strong> at line %d <pre>%s</pre></div>
            </div>
xhtml;

            if ( !self::$isFirstOutput ) {
                self::$isFirstOutput = true;
                self::flushHtmlInlineStyle();
            }

            printf( $template, self::getCallingMethodString( $trace ), $trace['fromLine'], $result );
        }



        /**
         * Mix var_dump and print_r
         * @static
         * @param mixed  $value
         */
        public static function VarPrint( $value ) {
            self::$decreaseCallingMethod = true;
            if ( !empty( $value ) && ( is_array( $value ) || is_object( $value ) ) ) {
                return self::PrintR( $value );
            } else {
                return self::VarDump( $value );
            }
        }
    }
?><?php
    /**
     * Param Object
     * @package Eaze
     * @subpackage Core
     * @author sergeyfast
     */
    class ParamObject  {
        /**
         * Parameters
         *
         * @var array
         */
        private $parameters;

        /**
         * Constructor
         *
         * @param array $parameters
         */
        public function __construct( &$parameters = array() ) {
            $this->parameters = $parameters;
        }


        /**
         * Get Value
         * @param  $key
         * @param string $type
         * @return mixed|null
         */
        public function GetValue( $key, $type = TYPE_PARAMETER ) {
            if ( isset( $this->parameters[$key] ) ) {
                return Convert::ToValue( $this->parameters[$key], $type );
            }

            return null;
        }


        public function SetValue( $key, $value, $type = TYPE_PARAMETER ) {
            $this->parameters[$key] = Convert::ToValue( $value, $type );
        }


        /**
         * Get or Set value to ParamObject
         * @param  string $mode MODE_GET or MODE_SET
         * @param  string $key  array key
         * @param  mixed $value
         * @param string $type TYPE_*
         * @return mixed|null
         */
        public function Value( $mode, $key, $value, $type = TYPE_PARAMETER ) {
            if ( $mode == MODE_GET ) {
                return $this->GetValue( $key, $type );
            }

            $this->SetValue( $key, $value, $type );
        }


        /**
         * Determines whether the ParamObject contains a specific key.
         * @param  $key
         * @return bool
         */
        public function ContainsKey( $key ) {
            return isset( $this->parameters[$key] );
        }


        /**
         * Get Parameters
         *
         * @return array
         */
        public function GetParameters() {
            return $this->parameters;
        }

        public function GetInteger( $key ) {
            return $this->GetValue( $key, TYPE_INTEGER );
        }

        public function GetBoolean( $key ) {
            return $this->GetValue( $key, TYPE_BOOLEAN );
        }

        public function GetString( $key ) {
            return $this->GetValue( $key, TYPE_STRING );
        }

        public function GetFloat( $key ) {
            return $this->GetValue( $key, TYPE_FLOAT );
        }

        public function GetArray( $key ) {
            return $this->GetValue( $key, TYPE_ARRAY );
        }

        public function GetObject( $key ) {
            return $this->GetValue( $key, TYPE_OBJECT );
        }

        public function GetParameter( $key ) {
            return $this->GetValue( $key, TYPE_PARAMETER );
        }

        public function GetDateTime( $key ) {
            return $this->GetValue( $key, TYPE_DATETIME );
        }

        public function SetInteger( $key, $value ) {
            $this->SetValue( $key, $value, TYPE_INTEGER );
        }

        public function SetBoolean( $key, $value ) {
            $this->SetValue( $key, $value, TYPE_BOOLEAN );
        }

        public function SetString( $key, $value ) {
            $this->SetValue( $key, $value, TYPE_STRING );
        }

        public function SetFloat( $key, $value ) {
            $this->SetValue( $key, $value, TYPE_FLOAT );
        }

        public function SetArray( $key, $value ) {
            $this->SetValue( $key, $value, TYPE_ARRAY );
        }

        public function SetObject( $key, $value ) {
            $this->SetValue( $key, $value, TYPE_OBJECT );
        }

        public function SetParameter( $key, $value ) {
            $this->SetValue( $key, $value, TYPE_PARAMETER );
        }

        public function SetDateTime( $key, $value ) {
            $this->SetValue( $key, $value,  TYPE_DATETIME );
        }
    }
?><?php
    define( 'METHOD_POST',    'post' );
    define( 'METHOD_GET',     'get' );
    define( 'METHOD_REQUEST', 'request' );

    /**
     * Web Request Class
     *
     * @package Eaze
     * @subpackage Core
     * @author sergeyfast
     */
    class Request {
        /**
         * Initialized flag
         *
         * @var boolean
         */
        public static $Initialized = false;

        /**
         * '$_REQUEST' Instance
         *
         * @var ParamObject
         */
        private static $requestInstance;

        /**
         * '$_GET' Instance
         *
         * @var ParamObject
         */
        private static $getInstance;

        /**
         * '$_POST' Instance
         *
         * @var ParamObject
         */
        private static $postInstance;


        /**
         * 'Host' Instance
         *
         * @var Host
         */
        private static $hostInstance;

        /**
         * Get Value
         *
         * @param string $key      the key value
         * @param string $method   get, post, or both
         * @param string $type     value type
         */
        private static function value( $mode = MODE_GET, $key, $value = null, $method = null, $type = null ) {
            switch ( strtolower( $method ) ) {
                case METHOD_REQUEST:
                    if ( isset( self::$requestInstance ) ) {
                        return self::$requestInstance->Value( $mode, $key, $value, $type );
                    }

                    break;
                case METHOD_GET:
                    if ( true == isset( self::$getInstance ) ) {
                        if ( $mode == MODE_SET ) {
                            Logger::Warning( 'Set for _GET is deprecated' );
                        }

                        return self::$getInstance->Value( $mode, $key, $value, $type );
                    }

                    break;
                case METHOD_POST:
                    if ( true == isset( self::$postInstance ) ) {
                        if ( $mode == MODE_SET ) {
                            Logger::Warning( 'Set for _POST is deprecated' );
                        }

                        return self::$postInstance->Value( $mode, $key, $value, $type );
                    }

                    break;
            }

            return null;
        }


        public static function getInteger( $key, $method = METHOD_REQUEST ) {
            return self::value( MODE_GET, $key, null, $method, TYPE_INTEGER );
        }

        public static function getBoolean( $key, $method = METHOD_REQUEST ) {
            return self::value( MODE_GET, $key, null, $method, TYPE_BOOLEAN );
        }

        public static function getString( $key, $method = METHOD_REQUEST ) {
            return self::value( MODE_GET, $key, null, $method, TYPE_STRING );
        }

        public static function getFloat( $key, $method = METHOD_REQUEST ) {
            return self::value( MODE_GET, $key, null, $method, TYPE_FLOAT );
        }

        public static function getArray( $key, $method = METHOD_REQUEST ) {
            return self::value( MODE_GET, $key, null, $method, TYPE_ARRAY );
        }

        public static function getObject( $key, $method = METHOD_REQUEST ) {
            return self::value( MODE_GET, $key, null, $method, TYPE_OBJECT );
        }

        public static function getParameter( $key, $method = METHOD_REQUEST ) {
            return self::value( MODE_GET, $key, null, $method, TYPE_PARAMETER );
        }

        public static function getValue( $key, $type, $method = METHOD_REQUEST ) {
            return self::value( MODE_GET, $key, null, $method, $type );
        }

        public static function getDateTime( $key, $method = METHOD_REQUEST ) {
            return self::value( MODE_GET, $key, null, $method, TYPE_DATETIME );
        }

        public static function setInteger( $key, $value, $method = METHOD_REQUEST ) {
            return self::value( MODE_SET, $key, $value, $method, TYPE_INTEGER );
        }

        public static function setBoolean( $key, $value, $method = METHOD_REQUEST ) {
            return self::value( MODE_SET, $key, $value, $method, TYPE_BOOLEAN );
        }

        public static function setString( $key, $value, $method = METHOD_REQUEST ) {
            return self::value( MODE_SET, $key, $value, $method, TYPE_STRING );
        }

        public static function setFloat( $key, $value, $method = METHOD_REQUEST ) {
            return self::value( MODE_SET, $key, $value, $method, TYPE_FLOAT );
        }

        public static function setArray( $key, $value, $method = METHOD_REQUEST ) {
            return self::value( MODE_SET, $key, $value, $method, TYPE_ARRAY );
        }

        public static function setObject( $key, $value, $method = METHOD_REQUEST ) {
            return self::value( MODE_SET, $key, $value, $method, TYPE_OBJECT );
        }

        public static function setParameter( $key, $value, $method = METHOD_REQUEST ) {
            return self::value( MODE_SET, $key, $value, $method, TYPE_PARAMETER );
        }

        public static function setValue( $key, $value, $type, $method = METHOD_REQUEST ) {
            return self::value( MODE_SET, $key, $value, $method, $type );
        }

        public static function setDateTime( $key, $value, $method = METHOD_REQUEST ) {
            return self::value( MODE_SET, $key, $value, $method, TYPE_DATETIME );
        }

        /**
         * Get Parameters
         * @static
         * @param string $method
         * @return array
         */
        public static function getParameters( $method = METHOD_REQUEST ) {
             switch ( strtolower( $method ) ) {
                case METHOD_REQUEST:
                    if ( isset( self::$requestInstance ) ) {
                        return self::$requestInstance->GetParameters();
                    }

                    break;
                case METHOD_GET:
                    if ( isset( self::$getInstance ) ) {
                        return self::$getInstance->GetParameters();
                    }

                    break;
                case METHOD_POST:
                    if ( true == isset( self::$postInstance ) ) {
                        return self::$postInstance->GetParameters();
                    }

                    break;
            }

            return array();
        }

        /**
         * Init Request
         */
        public static function Init() {
            if ( ! self::$Initialized ) {
                self::$Initialized = true;

                Session::Init();
                Cookie::Init();
                Response::Init();

                self::$requestInstance = new ParamObject( $_REQUEST );
                self::$getInstance     = new ParamObject( $_GET );
                self::$postInstance    = new ParamObject( $_POST );
            }
        }


    	/**
    	 * Commit all changes in request
    	 */
    	public static function Commit() {
    	    if ( true == self::$Initialized ) {
                Session::Commit();
            }
    	}


    	/**
    	 * Get Current Host
    	 *
    	 * @return Host
    	 */
    	public static function GetHost() {
            if ( empty( self::$hostInstance ) ) {
                self::$hostInstance = Host::GetCurrentHost();
            }

    	    return self::$hostInstance;
    	}


        /**
         * Get $_SERVER Variable
         * @static
         * @param  string $key
         * @return string|null
         */
        public static function GetServerVariable( $key ) {
            if ( isset( $_SERVER[$key] ) ) {
                return $_SERVER[$key];
            }

            return null;
        }



        /**
         * Get Remote IP
         *
         * @return string X_REAL_IP, then REMOTE_ADDR
         */
        public static function GetRemoteIp() {
            if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
                 return $_SERVER['HTTP_X_REAL_IP'];
            } else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
                return $_SERVER['REMOTE_ADDR'];
            } else {
                return null;
            }
        }


        /**
         * Get HTTP Host
         *
         * @return string $_SERVER['HTTP_HOST']
         */
        public static function GetHTTPHost() {
            return self::GetServerVariable( 'HTTP_HOST' );
        }


        /**
         * Get Referer
         *
         * @return string $_SERVER['HTTP_REFERER']
         */
        public static function GetReferer() {
            return self::GetServerVariable( 'HTTP_REFERER' );
        }


        /**
         * Get Script Name
         *
         * @return string $_SERVER['SCRIPT_NAME']
         */
        public static function GetScriptName() {
            return self::GetServerVariable( 'SCRIPT_NAME' );
        }


        /**
         * Get User Agent
         *
         * @return string $_SERVER['HTTP_USER_AGENT']
         */
        public static function GetUserAgent() {
            return self::GetServerVariable( 'HTTP_USER_AGENT' );
        }


        /**
         * Get Request Uri
         *
         * @return string Request Uri
         */
        public static function getRequestUri() {
            return self::GetServerVariable( 'REQUEST_URI' );
        }


        /**
         * Get File
         *
         * @param string $key
         * @return array
         */
        public static function GetFile( $key  )  {
            if ( !empty( $key ) ) {
                if ( isset( $_FILES[$key] ) ) {
                    return $_FILES[$key];
                } else {
                    return null;
                }
            }

            return null;
        }


        /**
         * Get Files
         *
         * @param string $key
         * @return array
         */
        public static function GetFiles( $key = null ) {
            $files = array();

            $sourceFiles = $_FILES;
            if ( !empty( $key ) ) {
                if ( isset( $_FILES[$key] ) ) {
                    $sourceFiles = $_FILES[$key];
                } else {
                    $sourceFiles = array();
                }
            }

            foreach ( $sourceFiles as $field => $params ) {
                foreach ( $params  as $key => $value) {
                    $files[$key][$field] = $value;
                }
            }

            return $files;
        }
    }
?><?php
    Package::Load( 'Eaze.Core' );

    /**
     * Response
     *
     * @package Eaze
     * @subpackage Eaze.Core
     */
    class Response  {
        /**
         * Initialized flag
         *
         * @var boolean
         */
        public static $Initialized = false;

        /**
         * Session Parameters
         *
         * @var ParamObject
         */
        private static $paramObject;

        /**
         * Enter description here...
         *
         * @param string $name  the file name
         * @param string $file      the file path
         */
        public static function SendFile( $file, $name =  null ) {
            $info = new FileInfo( $file );
            if ( empty( $name ) ) {
                $name = $info->GetName();
            }

            header('Content-Type: '.  $info->GetExtension() ) ;
            header('Content-Length: '. $info->GetFileSize() );
            header('Content-Disposition: attachment; filename="'. str_replace(' ', '%20', $name ) .'"');

            readfile( $file );
        }

        /**
         * Send Http Status Code
         *
         * @param string $code
         * @param message $message
         */
        public static function HttpStatusCode( $code, $message = '') {
            if (substr(php_sapi_name(), 0, 3) == 'cgi')  {
                header('Status: ' . $code . $message, true );
            } else {
                header( $_SERVER['SERVER_PROTOCOL'] . ' ' . $code . $message);
            }

            $fileName    = sprintf( '%s/%s.html', CONFPATH_ERRORS, $code);
            $xmlfileName = sprintf( '%s/%s.xml',  CONFPATH_ERRORS, $code);
            if ( file_exists( $xmlfileName ) ){
                $doc = new DOMDocument();
                $doc->load( $xmlfileName );
                /** @noinspection PhpUndefinedFieldInspection */
                $page = $doc->documentElement;

                new Page( $page, array( Site::GetCurrentURI() ), new DOMNodeList() );
            } elseif ( file_exists( $fileName ) ){
                /** @noinspection PhpIncludeInspection */
                include( $fileName );
            } else {
?>
<!DOCTYPE HTML PUBLIC '-//IETF//DTD HTML 2.0//EN'>
<html><head><title><?= $code ?> <?= $message ?></title></head><body><h1><?= $message ?></h1></body></html>
<?php
            }

           exit();
        }


        /**
         * Redirect to Url
         *
         * @param string $path
         */
        public static function Redirect( $path ) {
            if ( !headers_sent() ) {
                header( 'Location: ' . $path );
            } else {
                Logger::Error( 'Headers have been already sent. Cannot redirect to <a href="%s">%s</a>. Exiting.', $path, $path );
            }

            exit();
        }


        /**
         * Get or Set Value
         *
         * @param string $mode     the mode
         * @param string $key      the key value
         * @param mixed  $value    the value
         * @param string $type     value type (string,int,etc..)
         */
        private static function value( $mode = MODE_GET, $key, $value = null, $type = null ) {
            if ( true == isset( self::$paramObject ) ) {
                return self::$paramObject->value( $mode, $key, $value, $type );
            }

            return null;
        }


        /**
         * Init Session
         */
        public static function Init() {
            if ( ! self::$Initialized ) {
                self::$Initialized = true;
                Request::Init();

                self::$paramObject = new ParamObject();
            }
        }


        public static function getInteger( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_INTEGER );
        }

        public static function getBoolean( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_BOOLEAN );
        }

        public static function getString( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_STRING );
        }

        public static function getFloat( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_FLOAT );
        }

        public static function getArray( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_ARRAY );
        }

        public static function getObject( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_OBJECT );
        }

        public static function getParameter( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_PARAMETER );
        }

        public static function getDateTime( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_DATETIME );
        }

        public static function getParameters() {
            if ( true == isset( self::$paramObject ) ) {
                return self::$paramObject->getParameters();
            }

            return null;
        }

        public static function setInteger( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_INTEGER );
        }

        public static function setBoolean( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_BOOLEAN );
        }

        public static function setString( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_STRING );
        }

        public static function setFloat( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_FLOAT );
        }

        public static function setArray( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_ARRAY );
        }

        public static function setObject( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_OBJECT );
        }

        public static function setParameter( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_PARAMETER );
        }

        public static function settDateTime( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_DATETIME );
        }
    }
?><?php
    Package::Load( 'Eaze.Core' );

    /**
     * Session
     *
     * @package Eaze
     * @subpackage Core
     * @author sergeyfast
     */
    class Session {

        /**
         * Initialized flag
         *
         * @var boolean
         */
        public static $Initialized = false;

        /**
         * Session Parameters
         *
         * @var ParamObject
         */
        private static $paramObject;


        /**
         * Get or Set Value
         *
         * @param string $mode     the mode
         * @param string $key      the key value
         * @param mixed  $value    the value
         * @param string $type     value type (string,int,etc..)
         */
        private static function value( $mode = MODE_GET, $key, $value = null, $type = null ) {
            if ( true == isset( self::$paramObject ) ) {

                if ( !self::$Initialized && $mode == MODE_SET  ) {
                    Logger::Error( 'Session already stopped' );
                    return null;
                }

                return self::$paramObject->value( $mode, $key, $value, $type );
            }

            return null;
        }


        /**
         * Init Session
         */
        public static function Init() {
            if ( ! self::$Initialized ) {
                self::$Initialized = true;
                Request::Init();

                if ( !session_start() ) {
                    Logger::Error( 'Couldn\'t start session' );
                }

                self::$paramObject = new ParamObject( $_SESSION );
            }
        }


        /**
         * Get session id
         *
         * @return string
         */
        public static function getId() {
            return session_id();
        }


        /**
         * Set session id
         *
         * @param string $id
         * @return string
         */
        public static function setId( $id ) {
            return session_id( $id );
        }


        /**
         * Get session name
         *
         * @return string
         */
        public static function getName() {
            return session_name();
        }


        /**
         * Set session name
         *
         * @param string $name
         * @return string
         */
        public static function setName( $name ) {
            return session_name( $name );
        }


        /**
         * Commit session
         *
         * @return void
         */
        public static function Commit() {
            if ( true == isset( self::$paramObject ) ) {
                $_SESSION = self::$paramObject->getParameters();
            }

            session_commit();
            self::$Initialized = false;
        }


        /**
         * Destroy Session
         *
         * @return bool
         */
        public static function Destroy() {
            self::$paramObject = null;
            self::$Initialized  = false;

            return session_destroy();
        }


        /**
         * Get Session Save Path
         *
         * @return string
         */
        public static function getSavePath() {
            return session_save_path();
        }

        /**
         * Update the current session id with a newly generated one
         *
         * @param bool $deleteOldSession
         * @return bool
         */
        public static function regenerateId( $deleteOldSession = false ) {
            return session_regenerate_id( $deleteOldSession );
        }


        public static function getInteger( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_INTEGER );
        }

        public static function getBoolean( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_BOOLEAN );
        }

        public static function getString( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_STRING );
        }

        public static function getFloat( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_FLOAT );
        }

        public static function getArray( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_ARRAY );
        }

        public static function getObject( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_OBJECT );
        }

        public static function getParameter( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_PARAMETER );
        }

        public static function getDateTime( $key ) {
            return self::value( MODE_GET, $key, null, TYPE_DATETIME );
        }

        public static function getParameters() {
            if ( true == isset( self::$paramObject ) ) {
                return self::$paramObject->getParameters();
            }

            return null;
        }

        public static function setInteger( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_INTEGER );
        }

        public static function setBoolean( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_BOOLEAN );
        }

        public static function setString( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_STRING );
        }

        public static function setFloat( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_FLOAT );
        }

        public static function setArray( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_ARRAY );
        }

        public static function setObject( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_OBJECT );
        }

        public static function setParameter( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_PARAMETER );
        }

        public static function settDateTime( $key, $value ) {
            return self::value( MODE_SET, $key, $value, TYPE_DATETIME );
        }
    }
?>