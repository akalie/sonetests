<?php
    define( 'CONFPATH_CACHE', 'cache' );


    /**
     * Package Loader
     *
     * @package Eaze
     * @subpackage Core
     * @author sergeyfast
     */
    class Package {

        /**
         * File flag for WITH_PACKAGE_COMPILE check
         */
        const CompiledEaze = 'compiled.eaze';

        /**
         * .include-order
         */
        const IncludeOrderFilename = '.include-order';


        /**
         * WITH_PACKAGE_COMPILE constant name
         */
        const WithPackageCompile = 'WITH_PACKAGE_COMPILE';

        /**
         * Loaded Packages
         *
         * @var array
         */
        public static $Packages = array();

        /**
         * Loaded Files
         *
         * @var array
         */
        public static $Files = array();

        /**
         * Structure of LIB Directory
         *
         * @var array
         */
        public static $LibStructure = array();

        /**
         * Get include order list for package
         * @static
         * @param  string $packageName
         * @return array string[]
         */
        private static function getIncludeOrderList( $packageName ) {
            $result       = array();
            $includeOrder = __LIB__ . '/' . $packageName . '/' . Package::IncludeOrderFilename;
            if ( is_file( $includeOrder ) ) {
                $result = file( $includeOrder, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
            }

            return $result;
        }


        /**
         * Load Package
         *
         * @param string $name
         * @return bool
         */
        public static function Load( $name ) {
            if ( ( empty( $name ) )
                 || ( isset( Package::$Packages[$name] ) )
            ) {
                return false;
            }

            // Check for package compile flag
            if ( defined( self::WithPackageCompile ) && WITH_PACKAGE_COMPILE ) {
                if ( strpos( $name, '/' ) !== false ) {
                    $name = str_replace( '/', '.', $name );
                }

                $cacheFileName = sprintf( '%s/%s/%s.php', __ROOT__, CONFPATH_CACHE, $name );
                if ( is_file( $cacheFileName ) ) {
                    Package::$Packages[$name] = $name;
                    /** @noinspection PhpIncludeInspection */
                    require_once $cacheFileName;
                    return true;
                }
            }

            $packageDir      = __LIB__ . '/' . $name . '/';
            $deferredInclude = array();
            if ( is_dir( $packageDir ) ) {
                $d = dir( $packageDir );
                while ( false !== ( $file = $d->read() ) ) {
                    /** @var $file string */
                    if ( !self::CheckPHPFilename( $file, $packageDir ) ) {
                        continue;
                    }

                    $loadedFile = $packageDir . $file;
                    $deferredInclude[$file] = $loadedFile;
                    Package::$Files[$loadedFile] = $loadedFile;
                }

                $d->close();
            }

            if ( !empty( $deferredInclude ) ) {
                $includeOrders = self::getIncludeOrderList( $name );
                if ( !empty( $includeOrders ) ) {
                    $newOrder  = array();

                    $orderKeys = array_unique( array_merge( $includeOrders, array_keys( $deferredInclude ) ) );
                    foreach( $orderKeys as $key ) {
                        $newOrder[$key] = $deferredInclude[$key];
                    }
                    $deferredInclude = $newOrder;
                }

                self::defferedRequire( $deferredInclude );
                return true;
            }

            return false;
        }


        /**
         * Check PHP Filename and existence
         * @static
         * @param  string $file filename
         * @param  string $packageDir directory path with trailing slash
         * @return bool
         */
        public static function CheckPHPFilename( $file, $packageDir ) {
            if ( $file == '.'
                 || $file == '..'
                 || strpos( $file, '.php' ) === false
                 || !is_file( $packageDir . $file )
            ) {
                return false;
            }

            return true;
        }


        /**
         * Deferred Include
         *
         * @param array $list
         */
        private static function defferedRequire( $list ) {
            foreach ( $list as $file ) {
                /** @noinspection PhpIncludeInspection */
                require_once $file;
            }
        }


        /**
         * Init Constants
         *
         * @return void
         */
        public static function InitConstants() {
            if ( !defined( '__LIB__' ) ) {
                define( '__LIB__', realpath( dirname( __FILE__ ) . '/..' ) );
            }

            if ( !defined( '__ROOT__' ) ) {
                define( '__ROOT__', realpath( dirname( __FILE__ ) . '/../..' ) );
            }
        }


        /**
         * Load Lib Directory Structure
         *
         * @return void
         */
        private static function initLibStructure() {
            $libDir = __LIB__ . '/';

            if ( is_dir( $libDir ) ) {
                $d = dir( $libDir );
                while ( false !== ( $packageDir = $d->read() ) ) {
                    if ( $packageDir != '.' && $packageDir != '..' && is_dir( $libDir . $packageDir ) ) {
                        $packageDir = $libDir . $packageDir . '/';

                        $pd = dir( $packageDir );
                        while ( false !== ( $file = $pd->read() ) ) {
                            if ( self::CheckPHPFilename( $file, $packageDir ) ) {
                                /** @var $file string */
                                Package::$LibStructure[$file] = $packageDir . $file;
                            }
                        }
                        $pd->close();
                    }
                }
                $d->close();
            }
        }


        /**
         * Load Class by Name
         *
         * @param string $className
         * @return bool
         */
        public static function LoadClass( $className ) {
            if ( true == empty( Package::$LibStructure ) ) {
                Package::initLibStructure();
                Logger::Error( 'AutoLoad Event for Class %s! Please, use Package::Load() instead of AutoLoad.', $className );
            }

            $fileName = $className . '.php';

            if ( isset( Package::$LibStructure[$fileName] ) ) {
                /** @noinspection PhpIncludeInspection */
                require_once( Package::$LibStructure[$fileName] );
                return true;
            }

            return false;
        }
    }


    /**
     * AutoLoad Function
     *
     * @param string $className  the class name
     */
    function __autoload( $className ) {
        Package::LoadClass( $className );
    }

    /**
     * Initialize Constants
     */
    Package::InitConstants();

    /**
     * Eaze Compile Packages Code
     *
     */
    $packageCompiledFlag = sprintf( '%s/%s/%s', __ROOT__, CONFPATH_CACHE, Package::CompiledEaze );
    if ( defined( Package::WithPackageCompile ) ) {
        if ( WITH_PACKAGE_COMPILE ) {
            if ( !file_exists( $packageCompiledFlag ) ) {
                eaze_compile_packages();
                touch( $packageCompiledFlag );
            }
        } else {
            if ( file_exists( $packageCompiledFlag ) ) {
                unlink( $packageCompiledFlag );

                $cacheDir = __ROOT__ . '/' . CONFPATH_CACHE . '/';
                $d = dir( $cacheDir );
                while ( false !== ( $file = $d->read() ) ) {
                    if ( Package::CheckPHPFilename( $file, $cacheDir ) ) {
                        unlink( $cacheDir . $file );
                    }
                }
                $d->close();
            }
        }
    }


    /**
     * Eaze Compile Packages Function
     */
    function eaze_compile_packages() {
        $lib = __LIB__ . '/';

        if ( !is_dir( $lib ) ) {
            return;
        }

        $d = dir( $lib );
        while ( false !== ( $libDir = $d->read() ) ) {
            if ( $libDir != '.' && $libDir != '..' && is_dir( $lib . $libDir ) ) {
                if ( false === strpos( $libDir, '.' ) ) {
                    continue;
                }

                $packageDir   = $libDir;
                $packageFiles = $subDirs = $subPackageFiles = array();
                $libDir       = $lib . $libDir . '/';

                $pd = dir( $libDir );
                while ( false !== ( $file = $pd->read() ) ) {
                    if ( $file != '.' && $file != '..' && $file != 'Package.php' ) {
                        $dirItem = $libDir . $file;
                        if ( is_file( $dirItem ) && strpos( $file, '.php' ) !== false ) {
                            $packageFiles[] = $dirItem;
                        } else {
                            if ( is_dir( $dirItem ) && $file != 'actions' && $file != 'tests' ) {
                                $subDirs[$packageDir . '.' . $file] = $dirItem;
                            }
                        }
                    }
                }

                if ( !empty( $subDirs ) ) {
                    foreach ( $subDirs as $subPackageKey => $subDir ) {
                        $spd = dir( $subDir );
                        while ( false !== ( $file = $spd->read() ) ) {
                            if ( Package::CheckPHPFilename( $file, $subDir . '/' ) ) {
                                $subPackageFiles[$subPackageKey][] = $subDir . '/' . $file;
                            }
                        }
                        $spd->close();
                    }
                }
                $pd->close();

                // glue files
                $buffer = '';
                foreach ( $packageFiles as $packageFile ) {
                    $buffer .= file_get_contents( $packageFile );
                }
                file_put_contents( sprintf( '%s/%s/%s.php', __ROOT__, CONFPATH_CACHE, $packageDir ), $buffer );

                if ( !empty( $subPackageFiles ) ) {
                    foreach ( $subPackageFiles as $subPackage => $subFilePaths ) {
                        // Glue files
                        $buffer = '';
                        foreach ( $subFilePaths as $subFilePath ) {
                            $buffer .= file_get_contents( $subFilePath );
                        }
                        file_put_contents( sprintf( '%s/%s/%s.php', __ROOT__, CONFPATH_CACHE, $subPackage ), $buffer );
                    }
                }
            }
        }
        $d->close();
    }
?>