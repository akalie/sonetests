<?php
    /**
     * Array Helper
     * @package Eaze
     * @subpackage Helpers
     */
    class ArrayHelper {

        /**
         * Merging arrays recursively
         * original array_merge_recursive converts all data to array and extends it if key already exists
         * so it may cause some bugs if array contains objects
         * this function does not convert values into arrays and replaces data in existing key
         *
         * @var array $array1
         * @var array $array2 [optional]
         * @var array $_ [optional]
         * @static
         * @return array
         */
        public static function MergeDistinct( array $array1, array $array2 = array(), array $_ = array() ) {
            $arrays = func_get_args();
            $merged = array_shift( $arrays );

            foreach( $arrays as $array ) {
                foreach ( $array as $key => &$value ) {
                    if ( is_array( $value ) && isset( $merged[$key] ) && is_array( $merged [$key] ) ) {
                        $merged[$key] = self::MergeDistinct( $merged[$key], $value );
                    } else {
                        $merged[$key] = $value;
                    }
                }
            }

           return $merged;
        }


        /**
         * Gets the first element in the array.
         *
         * @param array $param  Array to get first element.
         * @param boolean $key  Defines if needs to get element key. Default returns element value.
         * @return mixed
         */
        public static function GetFirstElement( array $param, $key = false ) {
            $value = reset( $param );
            if ( $key ) {
                return key( $param );
            }

            return $value;
        }


        /**
         * Sets value of the array with specified path.
         *
         * @param array $parent  Parent array to set value.
         * @param array $path    Path to the element.
         * @param mixed $value   Value of the element.
         * @param string $field
         * @return array
         */
        public static function SetValue( &$parent, array $path, $value, $field = null ) {
            if ( empty( $parent ) ) {
                $parent = array();
            }

            if ( empty( $path ) ) {
                return $parent;
            }

            if ( 1 == count( $path ) ) {
                $key = reset( $path );
                $parent[$key] = $value;

                return $parent;
            }

            $key = ArrayHelper::GetFirstElement( $path );
            unset( $path[ArrayHelper::GetFirstElement( $path, true )] );

            if ( ! isset( $parent[$key] ) ) {
                $parent[$key] = array();
            }

            if ( empty( $field ) ) {
                self::SetValue( $parent[$key], $path, $value );
            } else {
                self::SetValue( $parent[$key]->$field, $path, $value, $field );
            }
        }


        /**
         * Converts BaseTreeObject path to array.
         *
         * @param mixed $path   BaseTreeObject path.
         * @return array
         */
        public static function PathToArray( $path ) {
            $result = explode( ".", $path );

            return $result;
        }


        /**
         * Gets order number of the element with specified key.
         *
         * @param mixed $key       Key to find.
         * @param array $haystack  Array stack to search.
         * @return int position of key starting with 1
         */
        public static function GetOrderNumber( $key, $haystack ) {
            $position = array_search( $key, array_keys( $haystack ) );
            if ( $position !== false ){
                $position ++;
            }

            return $position;
        }


        /**
         * Collapse source objects
         *
         * @param array  $sourceObjects  the array of source objects
         * @param string $collapseKey    the object field
         * @param bool   $toArray        the collapse mode
         * @return array
         */
        public static function Collapse( $sourceObjects, $collapseKey, $toArray = true  ) {
            if ( empty( $sourceObjects ) ) {
                return null;
            }

            $result = array();
            foreach ( $sourceObjects as $object ) {
                if ( $toArray ) {
                    $result[$object->$collapseKey][] = $object;
                } else {
                    $result[$object->$collapseKey]   = $object;
                }
            }

            return $result;
        }


        /**
         * Get Array Value by Key or return default value
         * @static
         * @param  array      $array
         * @param  string|int $key   key for array_key_exists
         * @param mixed       $defaultValue optional
         * @return mixed
         */
        public static function GetValue( array $array, $key, $defaultValue = null ) {
            if ( array_key_exists( $key, $array ) ) {
                return $array[$key];
            }

            return $defaultValue;
        }

        /**
         * Get Array difference of two assoc arrays
         * result is array with 4 keys: 'identical', 'modified', 'added', 'missed'
         * 'identical' contains keys of equal values
         * 'modified' contains keys of modified values
         * 'added' contains keys of values presented only in $newArray
         * 'missed' contains keys of values presented only in $oldArray
         *
         * @static
         * @param array $newArray
         * @param array $oldArray
         * @param bool  $strict
         * @return array
         */
        public static function GetArrayDiff( $newArray, $oldArray, $strict = false ) {
            //result structure
            $result = array(
                'identical'     => array()
                , 'modified'    => array()
                , 'added'       => array()
                , 'missed'      => array()
            );

            //search missed, identical or modified elements in new array
            foreach( $oldArray as $key => $value ) {
                if( !array_key_exists( $key, $newArray ) ) {
                    $result['missed'][] = $key;
                } else {
                    $identical = ( $strict ? ( $value === $newArray[$key] ) : ( $value == $newArray[$key] ) );
                    if( $identical ) {
                        $result['identical'][] = $key;
                    } else {
                        $result['modified'][] = $key;
                    }
                }
            }

            //search added elements in old array
            foreach( $newArray as $key => $value ) {
                if( !array_key_exists( $key, $oldArray ) ) {
                    $result['added'][] = $key;
                }
            }

            return $result;
        }

        /**
         * Get objects field values
         *
         * @static
         * @param array  $objects  the array of source objects
         * @param array  $fields   main object field first, than sub objects fields if necessary
         *
         * @return array
         */
        public static function GetObjectsFieldValues( $objects, $fields ) {
            $result = array();

            if( !empty( $objects ) ) {
                foreach( $objects as $key => $object ) {
                    $value = $object;
                    
                    foreach( $fields as $field ) {
                        $value = $value->$field;
                    }

                    $result[$key] = $value;
                }
            }

            return $result;
        }

        /**
         * Get objects tree collapsed by self parent foreign key
         *
         * @static
         * @param array     $objects    the array of source objects
         * @param string    $primaryKey title of object primary key (ex. "categoryId")
         * @param string    $parentKey  title of object self parent foreign key (ex. "parentCategoryId")
         * @param string    $nodes      title of object array field with will contain child objects
         * @return array
         */
        public static function GetObjectsTree( $objects, $primaryKey, $parentKey, $nodes ) {
            $tree = array();

            foreach( $objects as $object ) {
                $id  = $object->$primaryKey;
                $pid = $object->$parentKey;
                if( is_null( $object->$nodes ) ) {
                    $object->$nodes = array();
                }

                if( empty( $pid ) ) {
                    $tree[$id] = $object;
                } else {
                    $objectNodes            = $objects[$pid]->$nodes;
                    $objectNodes[$id]       = $object;
                    $objects[$pid]->$nodes  = $objectNodes;
                }
            }

            return $tree;
        }
    }

?><?php
    /**
     * Asset Helper
     * @package Eaze
     * @subpackage Helpers
     * @author sergeyfast
     * @since 1.3
     * @static
     */
    class AssetHelper {

        /** JS Type */
        const JS  = 'js';

        /** CSS Type */
        const CSS = 'css';

        /** All browsers */
        const AnyBrowser = 'any';

        /** IE6 */
        const IE6 = 2;

        /** IE7 */
        const IE7 = 3;

        /** IE8 */
        const IE8 = 4;

        /** Any IE version */
        const IE = 5;

        /** <= IE 7 Wrapper */
        const LteIE7 = 7;

        /** <= IE 8 Wrapper */
        const LteIE8 = 8;

        /** <= IE 9 Wrapper */
        const LteIE9 = 9;

        /** < IE 8 Wrapper */
        const LtIE8 = 10;

        /** Line constants in container */
        const Line = '%line%';

        /**
         * Url of minify.php
         * @var string
         */
        static $MinifyUrl = '/shared/minify.php';

        /**
         * Filename of SVN Revision
         * @var string
         */
        protected static $revisionFilename = 'shared://.revision';


        /**
         * Enable PostProcess mode
         * @var bool
         */
        public static $PostProcess = true;


        /**
         * Flush points for PostProcess
         * @var array
         */
        private static $flushPoints = array();

        /**
         * Browser Modes
         * @var array
         */
        public static $BrowserModes = array(
            self::AnyBrowser
            , self::IE
            , self::IE6
            , self::IE7
            , self::IE8
            , self::LteIE7
            , self::LteIE8
            , self::LteIE9
            , self::LtIE8
        );


        /**
         * Conditional Comments
         * @var array
         */
        public static $WrapperTemplates = array(
            self::AnyBrowser => '%s'
            , self::IE6      => '<!--[if IE 6]>%s<![endif]-->'
            , self::IE7      => '<!--[if IE 7]>%s<![endif]-->'
            , self::IE8      => '<!--[if IE 8]>%s<![endif]-->'
            , self::LteIE7   => '<!--[if lte IE 7]>%s<![endif]-->'
            , self::LteIE8   => '<!--[if lte IE 8]>%s<![endif]-->'
            , self::LteIE9   => '<!--[if lte IE 9]>%s<![endif]-->'
            , self::IE       => '<!--[if IE]>%s<![endif]-->'
            , self::LtIE8    => '<!--[if lt IE 8]>%s<![endif]-->'
        );

        /**
         * Type Templates
         * @var array
         */
        public static $TypeTemplates = array(
            self::JS => array(
                'src'        => '<script type="text/javascript" src="%s"></script>'
                , self::Line => '<script type="text/javascript">%s</script>'
            )
            , self::CSS => array(
                'src'        => '<link rel="stylesheet" type="text/css" href="%s" />'
                , self::Line => '<style type="text/css">%s</style>'
            )
        );

        /**
         * Container for Assets
         * @var array
         */
        protected static $container = array(
            self::JS    => array()
            , self::CSS => array()
        );


        /**
         * Get SVN Revision
         * @static
         * @return string
         */
        public static function GetRevision() {
            static $revision;

            if ( empty( $revision ) ) {
                $filename = Site::GetRealPath( self::$revisionFilename );
                if ( is_file( $filename ) ) {
                    $revision = trim( file_get_contents( $filename ) );
                } else {
                    $revision = 1;
                }
            }

            return $revision;
        }


        /**
         * Add file to Container
         * @static
         * @param  string $type  container type(self::JS or self::CSS)
         * @param  string $file  filename
         * @param string $mode   browser mode
         * @return void
         */
        protected static function addFile( $type, $file, $mode = self::AnyBrowser ) {
            self::$container[$type][$mode][$file] = $file;
        }


        /**
         * Add Line to Container
         * @static
         * @param  string $type  container type(self::JS or self::CSS)
         * @param string $line   line
         * @param string $mode   browser mode
         * @return void
         */
        protected static function addLine( $type, $line, $mode = self::AnyBrowser ) {
            if ( array_key_exists( $mode, self::$container[$type] )
                    && array_key_exists( self::Line, self::$container[$type][$mode] ) )
            {
                self::$container[$type][$mode][self::Line] .= $line;
            } else {
                self::$container[$type][$mode][self::Line]  = $line;
            }
        }


        /**
         * Delete File
         * @static
         * @param  string $type
         * @param  string $file
         * @param string $mode
         * @return bool
         */
        protected static function deleteFile( $type, $file, $mode = self::AnyBrowser ) {
            $result = false;
            if ( array_key_exists( $mode, self::$container[$type] )
                    && array_key_exists( $file, self::$container[$type][$mode] ) ) {
                unset( self::$container[$type][$mode][$file] );
                $result = true;
            }

            return $result;
        }


        /**
         * Set Flush Point
         * @static
         * @param  string $type  asset type
         * @param $minify
         * @param $hostname
         * @param $maxGroups
         * @return string
         */
        protected static function setFlushPoint( $type, $minify, $hostname, $maxGroups ) {
            if ( empty( self::$flushPoints[$type] ) ) {
                $id = '{%' . uniqid( $type ) . '%}';
                self::$flushPoints[$type] = compact( 'id', 'minify', 'hostname', 'maxGroups' );
            }

            return self::$flushPoints[$type]['id'];
        }


        /**
         * Flush Browser Mode
         * @static
         * @param string $type
         * @param string $mode
         * @param bool   $minify
         * @param string $hostname
         * @param int    $maxGroups
         * @return string
         */
        protected static function flushMode( $type, $mode = self::AnyBrowser, $minify, $hostname, $maxGroups  ) {
            $result = '';
            if ( empty( self::$container[$type][$mode] ) ) {
                return $result;
            }

            $lines = '';
            $files = array();
            foreach( self::$container[$type][$mode] as $file => $content ) {
                if ( $file == self::Line ) {
                    $lines .= sprintf( self::$TypeTemplates[$type][self::Line], $content );
                } else {
                    $files[] = $file;
                }
            }

            // set files
            if ( !empty( $files ) ) {
                $paths = array();
                foreach ( $files as $file ) {
                    $paths[] = $minify ?  Site::TranslateUrlWithPath( $file, $hostname ) : Site::GetWebPath( $file, $hostname );
                }

                // set result
                $revision = self::GetRevision();
                if ( $minify ) {
                    $url        = Site::GetWebPath( self::$MinifyUrl, $hostname );
                    $pathGroups = array_chunk( $paths, $maxGroups );

                    foreach( $pathGroups as $paths ) {
                        $fullUrl = $url .  '?' . $revision . '&amp;files=' . implode( ',', $paths );
                        $result .= sprintf( self::$TypeTemplates[$type]['src'], $fullUrl );
                    }
                } else {
                    foreach( $paths as $path ) {
                        $result .= sprintf( self::$TypeTemplates[$type]['src'], $path . '?' . $revision );
                    }
                }
            }

            $result .= $lines;

            // return wrapped result & clear mode
            self::$container[$type][$mode] = array();

            return sprintf( self::$WrapperTemplates[$mode], $result );
        }


        /**
         * Post Process
         * @static
         * @param  string $html
         * @return string replaced html
         */
        public static function PostProcess( $html ) {
            if ( !self::$PostProcess || empty( self::$flushPoints ) ) {
                return $html;
            }

            $search  = array();
            $replace = array();
            foreach( self::$flushPoints as $type => $point ) {
                $search[] = $point['id'];
                $output   = '';
                foreach ( self::$BrowserModes as $mode ) {
                    $output .= self::flushMode( $type, $mode, $point['minify'], $point['hostname'], $point['maxGroups'] );
                }
                $replace[] = $output;
            }

            return str_replace( $search, $replace, $html );
        }
    }
?><?php
    /**
     * Helps to render CSS
     *
     * @package Eaze
     * @subpackage Helpers
     * @since 1.3
     * @author sergeyfast
     * @static
     */
    class CssHelper extends AssetHelper {

        /**
         * Minify scripts or not
         * @var bool
         */
        public static $Minify = true;

        /**
         * Max Group Size for minify
         * @var int
         */
        public static $MaxGroups = 20;

        /**
         * Default Hostname
         * @var string default hostname
         */
        public static $Hostname = 'static';

        /**
         * Current Type
         * @var string
         */
        private static $type = self::CSS;

        /**
         * Add File
         * @param string $file single CSS file
         * @param string $mode browser mode
         */
        public static function PushFile( $file, $mode = self::AnyBrowser ) {
            parent::addFile( self::$type, $file, $mode );
        }


        /**
         * Add multiple CSS file
         * @param string[] $files array of CSS files
         * @param string $mode browser mode
         */
        public static function PushFiles( $files, $mode = self::AnyBrowser ) {
            foreach ( $files as $file ) {
                self::PushFile( $file, $mode );
            }
        }

        /**
         * Add multiple CSS grouped files
         * @param $groups array of css grouped files
         * @return void
         */
        public static function PushGroups( $groups ) {
            foreach( $groups as $mode => $files ) {
                self::PushFiles( $files, $mode );
            }
        }

        /**
         * Add CSS line to CSS code
         * @param string $line
         * @param string $mode browser mode
         */
        public static function PushLine( $line, $mode = self::AnyBrowser ) {
            parent::addLine( self::$type, $line, $mode );
        }


        /**
         * Flush All Modes
         * @return string
         */
        public static function Flush() {
            if ( self::$PostProcess ) {
                return self::setFlushPoint( self::$type, self::$Minify, self::$Hostname, self::$MaxGroups );
            }

            $result = '';
            foreach ( self::$BrowserModes as $mode ) {
                $result .= parent::flushMode( self::$type, $mode, self::$Minify, self::$Hostname, self::$MaxGroups );
            }

            return $result;
        }


        /**
         * Remove File from Queue
         * @static
         * @param string $file
         * @param string $mode
         * @return bool
         */
        public static function RemoveFile( $file, $mode = self::AnyBrowser ) {
            return parent::deleteFile( self::$type, $file, $mode );
        }


        /**
         * Init Helper
         * @static
         * @param bool   $minify
         * @param int    $maxGroups
         * @param string $hostname
         * @return void
         */
        public static function Init( $minify = true, $maxGroups = 25, $hostname = 'static' ) {
            self::$Minify    = $minify;
            self::$MaxGroups = $maxGroups;
            self::$Hostname  = $hostname;
        }
    }
?><?php
    define( 'SPAN_YEARS', 0 );
    define( 'SPAN_MONTHES', 1 );
    define( 'SPAN_WEEKS', 2 );
    define( 'SPAN_DAYS', 3 );
    define( 'SPAN_HOURS', 4 );
    define( 'SPAN_MINUTES', 5 );
    define( 'SPAN_SECONDS', 6 );

    /**
     * DateTime Helper
     *
     * @package Eaze
     * @subpackage Eaze.Helpers
     */
    class DateTimeHelper {
        public static $keys = array(
            0 => array(
                1 => 'vt.common.year'
                , 2 => 'vt.common.years'
                , 5 => 'vt.common.manyYears'
            )
            , 1 => array(
                1   => 'vt.common.month'
                , 2 => 'vt.common.monthes'
                , 5 => 'vt.common.manyMonthes'
            )
            , 2 => array(
                1 => 'vt.common.week'
                , 2 => 'vt.common.weeks'
                , 5 => 'vt.common.manyWeeks'
            )
            , 3 => array(
                1   => 'vt.common.day'
                , 2 => 'vt.common.days'
                , 5 => 'vt.common.manyDays'
            )
            , 4 => array(
                1   => 'vt.common.hour'
                , 2 => 'vt.common.hours'
                , 5 => 'vt.common.manyHours'
            )
            , 5 => array(
                1   => 'vt.common.minute'
                , 2 => 'vt.common.minutes'
                , 5 => 'vt.common.manyMinutes'
            )
            , 6 => array(
                1   => 'vt.common.second'
                , 2 => 'vt.common.seconds'
                , 5 => 'vt.common.manySeconds'
            )
        );

        public static $secs = array(
            0   => 31557600 
            , 1 => 2592000
            , 2 => 604800
            , 3 => 86400
            , 4 => 3600
            , 5 => 60
            , 6 => 1
        );

		private static function customDiv( $a, $b ) {
			if (function_exists('gmp_div')) {
				return gmp_strval(gmp_div($a, $b));
			}

            return (int)($a / $b);
		}

		private static function customMod( $a, $b ) {
			if (function_exists('gmp_mod')) {
				return gmp_strval(gmp_mod($a, $b));
			}

            return $a % $b;
		}


        /**
         * Gets interval string
         *
         * @param int   $seconds  Interval time in seconds.
         * @param int   $maxSpan
         * @param array $allowedSpans
         * @return string
         */
        public static function GetIntervalString( $seconds, $maxSpan = SPAN_DAYS, $allowedSpans = array( 0, 1, 2, 3, 4, 5, 6 ) ) {
            $cases  = array (2, 0, 1, 1, 1, 2);
            $date   = self::GetIntervalArray( $seconds, $maxSpan );
            $result = "";

            foreach ( $date as $key => $value ) {
                if ( !in_array($key, $allowedSpans ) ) {
                    continue;
                }


                $val = Convert::ToInt( $value );
                if ( !(empty( $val ) ) ) {
                    $form = ($val%100>4 && $val%100<20)? 2 : $cases[min($val%10, 5)];
                    
                    switch ( $form ) {
                        case 0:
                            $count = 1;
                            break;
                        case 1:
                            $count = 2;
                            break;
                        case 2:
                            $count = 5;
                            break;
                    }

                    $result .= " " . $value . " " . LocaleLoader::Translate( self::$keys[$key][$count] );
                }
            }

            return $result;
        }


        /**
         * Gets the array of time intervals such as weeks, days, minutes and etc.
         *
         * @param int $seconds
         * @param int $maxSpan
         * @return array
         */
        public static function GetIntervalArray( $seconds, $maxSpan = SPAN_DAYS ) {
            $val = $seconds;

            for ( $i = $maxSpan; !empty( self::$secs[$i] ); $i++ ) {
                if ( $i == SPAN_WEEKS ) {
                    continue;
                }
                
                $return[$i] = self::customDiv( $val , self::$secs[$i] );
				$val        = self::customMod($val, self::$secs[$i]);
            }

            return $return;
        }

        /**
         * Respresents given interval as specified time span
         *
         * @param long $seconds
         * @param int $span
         * @return int
         */
        public static function GetInterval( $seconds, $span = SPAN_DAYS ) {
            if ( empty( self::$secs[$span] ) ) {
                return null;
            }

            $secs = self::$secs[$span];

            $result = self::customMod($seconds, $secs);

            return $result;
        }


        /**
         * Is Today
         *
         * @param DateTime $date
         * @return bool
         */
        public static function IsToday( $date ) {
            if ( empty( $date ) ) {
                return false;
            }

			/** Check For Today */
            if ( $date->format("d.m.Y") == date("d.m.Y") ) {
                return true;
            } else {
                return false;
            }
        }


        /**
         * Get Relative Date String
         * @param DateTime $date  the date object
         * @param bool     $useTimeInsteadOfToday
         * @return null|string
         */
        public static function GetRelativeDateString( $date, $useTimeInsteadOfToday = true ) {
            if ( empty( $date ) ) {
                return null;
            }

            /** Check For Today */
            if ( self::IsToday( $date ) ) {
                if ( $useTimeInsteadOfToday ) {
                    return $date->format("G:i");
                } else {
                    return LocaleLoader::Translate("vt.common.today");
                }
            }

            /** Check Month */
            if ( $date->format("Y") == date( "Y" ) ) {
                return strftime( "%e %B", $date->format( "U" ) );
            }

            /** Check Year */
            return $date->format( "d.m.Y" );
        }

        /**
         * ConvertTimeToSeconds
         *
         * @param string $timeString (ex. "02:30:00")
         * @return int
         */
        public static function ConvertTimeToSeconds( $timeString ) {
            if ( empty( $timeString ) ) {
                return 0;
            }

            $values   = explode(':', $timeString);
            
            return $values[2] + 60 * $values[1] + 3600 * $values[0];
        }


        /**
         * Check Intersection of two date ranges
         * @static
         * @param DateTime $r1Start first range start date
         * @param DateTime $r1End first range end date
         * @param DateTime $r2Start second range start date
         * @param DateTime $r2End second range end date
         * @return bool
         */
        public static function Intersects( DateTime $r1Start, DateTime $r1End, DateTime $r2Start, DateTime $r2End)
        {
            return ( $r1Start == $r2Start ) || ( $r1Start > $r2Start ? $r1Start <= $r2End : $r2Start <= $r1End);
        }
    }
?><?php

/* ***** BEGIN LICENSE BLOCK *****
 *
 * This file is part of FirePHP (http://www.firephp.org/).
 *
 * Software License Agreement (New BSD License)
 *
 * Copyright (c) 2006-2008, Christoph Dorn
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 *
 *     * Neither the name of Christoph Dorn nor the names of its
 *       contributors may be used to endorse or promote products derived from this
 *       software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * ***** END LICENSE BLOCK ***** */



/**
 * Sends the given data to the FirePHP Firefox Extension.
 * The data can be displayed in the Firebug Console or in the
 * "Server" request tab.
 *
 * For more informtion see: http://www.firephp.org/
 *
 * @copyright   Copyright (C) 2007-2008 Christoph Dorn
 * @author      Christoph Dorn <christoph@christophdorn.com>
 * @license     http://www.opensource.org/licenses/bsd-license.php
 */

class FirePHP {

  const LOG = 'LOG';
  const INFO = 'INFO';
  const WARN = 'WARN';
  const ERROR = 'ERROR';
  const DUMP = 'DUMP';
  const TRACE = 'TRACE';
  const EXCEPTION = 'EXCEPTION';
  const TABLE = 'TABLE';

  protected static $instance = null;


  /**
   * Get FirePHP
   * @param bool $AutoCreate default false
   * @return FirePHP
   */
  public static function getInstance($AutoCreate=false) {
    if($AutoCreate===true && !self::$instance) {
      self::init();
    }
    return self::$instance;
  }

  public static function init() {
    return self::$instance = new self();
  }



  public function setProcessorUrl($URL)
  {
    $this->setHeader('X-FirePHP-ProcessorURL', $URL);
  }

  public function setRendererUrl($URL)
  {
    $this->setHeader('X-FirePHP-RendererURL', $URL);
  }


  public function log() {
    $args = func_get_args();
    call_user_func_array(array($this,'fb'),array($args,FirePHP::LOG));
  }

  public function dump($Key, $Variable) {
    $args = func_get_args();
    call_user_func_array(array($this,'fb'),array($Variable,$Key,FirePHP::DUMP));
  }

  public function detectClientExtension() {
    /* Check if FirePHP is installed on client */
    if(!@preg_match_all('/\sFirePHP\/([\.|\d]*)\s?/si',$this->getUserAgent(),$m) ||
       !version_compare($m[1][0],'0.0.6','>=')) {
      return false;
    }
    return true;
  }

  public function fb($Object) {

    if (headers_sent($filename, $linenum)) {
        throw $this->newException('Headers already sent in '.$filename.' on line '.$linenum.'. Cannot send log data to FirePHP. You must have Output Buffering enabled via ob_start() or output_buffering ini directive.');
    }

    $Type = null;

    if(func_num_args()==1) {
    } else
    if(func_num_args()==2) {
      switch(func_get_arg(1)) {
        case self::LOG:
        case self::INFO:
        case self::WARN:
        case self::ERROR:
        case self::DUMP:
        case self::TRACE:
        case self::EXCEPTION:
        case self::TABLE:
          $Type = func_get_arg(1);
          break;
        default:
          $Object = array(func_get_arg(1),$Object);
          break;
      }
    } else
    if(func_num_args()==3) {
      $Type = func_get_arg(2);
      $Object = array(func_get_arg(1),$Object);
    } else {
      throw $this->newException('Wrong number of arguments to fb() function!');
    }


    if(!$this->detectClientExtension()) {
      return false;
    }

    if($Object instanceof Exception) {

      $Object = array('Class'=>get_class($Object),
                      'Message'=>$Object->getMessage(),
                      'File'=>$this->_escapeTraceFile($Object->getFile()),
                      'Line'=>$Object->getLine(),
                      'Type'=>'throw',
                      'Trace'=>$this->_escapeTrace($Object->getTrace()));
      $Type = self::EXCEPTION;

    } else
    if($Type==self::TRACE) {

      $trace = debug_backtrace();
      if(!$trace) return false;
      for( $i=0 ; $i<sizeof($trace) ; $i++ ) {

        if($trace[$i]['class']=='FirePHP' &&
           substr($this->_standardizePath($trace[$i+1]['file']),-18,18)=='FirePHPCore/fb.php') {
          /* Skip */
        } else
        if($trace[$i]['function']=='fb') {
          $Object = array('Class'=>$trace[$i]['class'],
                          'Type'=>$trace[$i]['type'],
                          'Function'=>$trace[$i]['function'],
                          'Message'=>$trace[$i]['args'][0],
                          'File'=>$this->_escapeTraceFile($trace[$i]['file']),
                          'Line'=>$trace[$i]['line'],
                          'Args'=>$trace[$i]['args'],
                          'Trace'=>$this->_escapeTrace(array_splice($trace,$i+1)));
          break;
        }
      }

    } else {
      if($Type===null) {
        $Type = self::LOG;
      }
    }

        $this->setHeader('X-FirePHP-Data-100000000001','{');
    if($Type==self::DUMP) {
        $this->setHeader('X-FirePHP-Data-200000000001','"FirePHP.Dump":{');
        $this->setHeader('X-FirePHP-Data-299999999999','"__SKIP__":"__SKIP__"},');
    } else {
        $this->setHeader('X-FirePHP-Data-300000000001','"FirePHP.Firebug.Console":[');
        $this->setHeader('X-FirePHP-Data-399999999999','["__SKIP__"]],');
    }
        $this->setHeader('X-FirePHP-Data-999999999999','"__SKIP__":"__SKIP__"}');

    if($Type==self::DUMP) {
        $msg = '"'.$Object[0].'":'.$this->json_encode($Object[1]).',';
    } else {
        $msg = '["'.$Type.'",'.$this->json_encode($Object).'],';
    }

        foreach( explode("\n",chunk_split($msg, 5000, "\n")) as $part ) {

      if($part) {


        usleep(1); /* Ensure microtime() increments with each loop. Not very elegant but it works */

                $mt = explode(' ',microtime());
                $mt = substr($mt[1],7).substr($mt[0],2);

        $this->setHeader('X-FirePHP-Data-'.(($Type==self::DUMP)?'2':'3').$mt, $part);
      }
        }

    return true;
  }

  protected function _standardizePath($Path) {
    return preg_replace('/\\\\+/','/',$Path);
  }

  protected function _escapeTrace($Trace) {
    if(!$Trace) return $Trace;
    for( $i=0 ; $i<sizeof($Trace) ; $i++ ) {
      $Trace[$i]['file'] = $this->_escapeTraceFile($Trace[$i]['file']);
    }
    return $Trace;
  }

  protected function _escapeTraceFile($File) {
    /* Check if we have a windows filepath */
    if(strpos($File,'\\')) {
      /* First strip down to single \ */

      $file = preg_replace('/\\\\+/','\\',$File);

      return $file;
    }
    return $File;
  }

  protected function setHeader($Name, $Value) {
    return header($Name.': '.$Value);
  }

  protected function getUserAgent() {
    if(!isset($_SERVER['HTTP_USER_AGENT'])) return false;
    return $_SERVER['HTTP_USER_AGENT'];
  }

  protected function newException($Message) {
    return new Exception($Message);
  }


  /**
   * Converts to and from JSON format.
   *
   * JSON (JavaScript Object Notation) is a lightweight data-interchange
   * format. It is easy for humans to read and write. It is easy for machines
   * to parse and generate. It is based on a subset of the JavaScript
   * Programming Language, Standard ECMA-262 3rd Edition - December 1999.
   * This feature can also be found in  Python. JSON is a text format that is
   * completely language independent but uses conventions that are familiar
   * to programmers of the C-family of languages, including C, C++, C#, Java,
   * JavaScript, Perl, TCL, and many others. These properties make JSON an
   * ideal data-interchange language.
   *
   * This package provides a simple encoder and decoder for JSON notation. It
   * is intended for use with client-side Javascript applications that make
   * use of HTTPRequest to perform server communication functions - data can
   * be encoded into JSON notation for use in a client-side javascript, or
   * decoded from incoming Javascript requests. JSON format is native to
   * Javascript, and can be directly eval()'ed with no further parsing
   * overhead
   *
   * All strings should be in ASCII or UTF-8 format!
   *
   * LICENSE: Redistribution and use in source and binary forms, with or
   * without modification, are permitted provided that the following
   * conditions are met: Redistributions of source code must retain the
   * above copyright notice, this list of conditions and the following
   * disclaimer. Redistributions in binary form must reproduce the above
   * copyright notice, this list of conditions and the following disclaimer
   * in the documentation and/or other materials provided with the
   * distribution.
   *
   * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED
   * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
   * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN
   * NO EVENT SHALL CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
   * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
   * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
   * OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
   * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
   * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
   * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
   * DAMAGE.
   *
   * @category
   * @package     Services_JSON
   * @author      Michal Migurski <mike-json@teczno.com>
   * @author      Matt Knapp <mdknapp[at]gmail[dot]com>
   * @author      Brett Stimmerman <brettstimmerman[at]gmail[dot]com>
   * @author      Christoph Dorn <christoph@christophdorn.com>
   * @copyright   2005 Michal Migurski
   * @version     CVS: $Id: JSON.php,v 1.31 2006/06/28 05:54:17 migurski Exp $
   * @license     http://www.opensource.org/licenses/bsd-license.php
   * @link        http://pear.php.net/pepr/pepr-proposal-show.php?id=198
   */


  /**
   * Keep a list of objects as we descend into the array so we can detect recursion.
   */
  private $json_objectStack = array();


 /**
  * convert a string from one UTF-8 char to one UTF-16 char
  *
  * Normally should be handled by mb_convert_encoding, but
  * provides a slower PHP-only method for installations
  * that lack the multibye string extension.
  *
  * @param    string  $utf8   UTF-8 character
  * @return   string  UTF-16 character
  * @access   private
  */
  private function json_utf82utf16($utf8)
  {
      // oh please oh please oh please oh please oh please
      if(function_exists('mb_convert_encoding')) {
          return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
      }

      switch(strlen($utf8)) {
          case 1:
              // this case should never be reached, because we are in ASCII range
              // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
              return $utf8;

          case 2:
              // return a UTF-16 character from a 2-byte UTF-8 char
              // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
              return chr(0x07 & (ord($utf8{0}) >> 2))
                   . chr((0xC0 & (ord($utf8{0}) << 6))
                       | (0x3F & ord($utf8{1})));

          case 3:
              // return a UTF-16 character from a 3-byte UTF-8 char
              // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
              return chr((0xF0 & (ord($utf8{0}) << 4))
                       | (0x0F & (ord($utf8{1}) >> 2)))
                   . chr((0xC0 & (ord($utf8{1}) << 6))
                       | (0x7F & ord($utf8{2})));
      }

      // ignoring UTF-32 for now, sorry
      return '';
  }

 /**
  * encodes an arbitrary variable into JSON format
  *
  * @param    mixed   $var    any number, boolean, string, array, or object to be encoded.
  *                           see argument 1 to Services_JSON() above for array-parsing behavior.
  *                           if var is a strng, note that encode() always expects it
  *                           to be in ASCII or UTF-8 format!
  *
  * @return   mixed   JSON string representation of input var or an error if a problem occurs
  * @access   public
  */
  public function json_encode($var)
  {

    if(is_object($var)) {
      if(in_array($var,$this->json_objectStack)) {
        return '"** Recursion **"';
      }
    }

      switch (gettype($var)) {
          case 'boolean':
              return $var ? 'true' : 'false';

          case 'NULL':
              return 'null';

          case 'integer':
              return (int) $var;

          case 'double':
          case 'float':
              return (float) $var;

          case 'string':
              // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
              $ascii = '';
              $strlen_var = strlen($var);

             /*
              * Iterate over every character in the string,
              * escaping with a slash or encoding to UTF-8 where necessary
              */
              for ($c = 0; $c < $strlen_var; ++$c) {


                  $ord_var_c = ord($var{$c});

                  switch (true) {
                      case $ord_var_c == 0x08:
                          $ascii .= '\b';
                          break;
                      case $ord_var_c == 0x09:
                          $ascii .= '\t';
                          break;
                      case $ord_var_c == 0x0A:
                          $ascii .= '\n';
                          break;
                      case $ord_var_c == 0x0C:
                          $ascii .= '\f';
                          break;
                      case $ord_var_c == 0x0D:
                          $ascii .= '\r';
                          break;

                      case $ord_var_c == 0x22:
                      case $ord_var_c == 0x2F:
                      case $ord_var_c == 0x5C:
                          // double quote, slash, slosh
                          $ascii .= '\\'.$var{$c};
                          break;

                      case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
                          // characters U-00000000 - U-0000007F (same as ASCII)
                          $ascii .= $var{$c};
                          break;

                      case (($ord_var_c & 0xE0) == 0xC0):
                          // characters U-00000080 - U-000007FF, mask 110XXXXX
                          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                          $char = pack('C*', $ord_var_c, ord($var{$c + 1}));
                          $c += 1;
                          $utf16 = $this->json_utf82utf16($char);
                          $ascii .= sprintf('\u%04s', bin2hex($utf16));
                          break;

                      case (($ord_var_c & 0xF0) == 0xE0):
                          // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                          $char = pack('C*', $ord_var_c,
                                       ord($var{$c + 1}),
                                       ord($var{$c + 2}));
                          $c += 2;
                          $utf16 = $this->json_utf82utf16($char);
                          $ascii .= sprintf('\u%04s', bin2hex($utf16));
                          break;

                      case (($ord_var_c & 0xF8) == 0xF0):
                          // characters U-00010000 - U-001FFFFF, mask 11110XXX
                          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                          $char = pack('C*', $ord_var_c,
                                       ord($var{$c + 1}),
                                       ord($var{$c + 2}),
                                       ord($var{$c + 3}));
                          $c += 3;
                          $utf16 = $this->json_utf82utf16($char);
                          $ascii .= sprintf('\u%04s', bin2hex($utf16));
                          break;

                      case (($ord_var_c & 0xFC) == 0xF8):
                          // characters U-00200000 - U-03FFFFFF, mask 111110XX
                          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                          $char = pack('C*', $ord_var_c,
                                       ord($var{$c + 1}),
                                       ord($var{$c + 2}),
                                       ord($var{$c + 3}),
                                       ord($var{$c + 4}));
                          $c += 4;
                          $utf16 = $this->json_utf82utf16($char);
                          $ascii .= sprintf('\u%04s', bin2hex($utf16));
                          break;

                      case (($ord_var_c & 0xFE) == 0xFC):
                          // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                          $char = pack('C*', $ord_var_c,
                                       ord($var{$c + 1}),
                                       ord($var{$c + 2}),
                                       ord($var{$c + 3}),
                                       ord($var{$c + 4}),
                                       ord($var{$c + 5}));
                          $c += 5;
                          $utf16 = $this->json_utf82utf16($char);
                          $ascii .= sprintf('\u%04s', bin2hex($utf16));
                          break;
                  }
              }

              return '"'.$ascii.'"';

          case 'array':
             /*
              * As per JSON spec if any array key is not an integer
              * we must treat the the whole array as an object. We
              * also try to catch a sparsely populated associative
              * array with numeric keys here because some JS engines
              * will create an array with empty indexes up to
              * max_index which can cause memory issues and because
              * the keys, which may be relevant, will be remapped
              * otherwise.
              *
              * As per the ECMA and JSON specification an object may
              * have any string as a property. Unfortunately due to
              * a hole in the ECMA specification if the key is a
              * ECMA reserved word or starts with a digit the
              * parameter is only accessible using ECMAScript's
              * bracket notation.
              */

              // treat as a JSON object
              if (is_array($var) && count($var) && (array_keys($var) !== range(0, sizeof($var) - 1))) {

                  $this->json_objectStack[] = $var;

                  $properties = array_map(array($this, 'json_name_value'),
                                          array_keys($var),
                                          array_values($var));

                  array_pop($this->json_objectStack);

                  foreach($properties as $property) {
                      if($property instanceof Exception) {
                          return $property;
                      }
                  }

                  return '{' . join(',', $properties) . '}';
              }

              $this->json_objectStack[] = $var;

              // treat it like a regular array
              $elements = array_map(array($this, 'json_encode'), $var);

              array_pop($this->json_objectStack);

              foreach($elements as $element) {
                  if($element instanceof Exception) {
                      return $element;
                  }
              }

              return '[' . join(',', $elements) . ']';

          case 'object':
              $vars = get_object_vars($var);

              $this->json_objectStack[] = $var;

              $properties = array_map(array($this, 'json_name_value'),
                                      array_keys($vars),
                                      array_values($vars));

              array_pop($this->json_objectStack);

              foreach($properties as $property) {
                  if($property instanceof Exception) {
                      return $property;
                  }
              }

              return '{'.$this->json_encode('__className') . ':' . $this->json_encode(get_class($var)) .
                     (($properties)?',':'') .
                     join(',', $properties) . '}';

          default:
              return null;
      }
  }

 /**
  * array-walking function for use in generating JSON-formatted name-value pairs
  *
  * @param    string  $name   name of key to use
  * @param    mixed   $value  reference to an array element to be encoded
  *
  * @return   string  JSON-formatted name-value pair, like '"name":value'
  * @access   private
  */
  private function json_name_value($name, $value)
  {
      $encoded_value = $this->json_encode($value);

      if($encoded_value instanceof Exception) {
          return $encoded_value;
      }

      return $this->json_encode(strval($name)) . ':' . $encoded_value;
  }

}


/**
 * Sends the given data to the FirePHP Firefox Extension.
 * The data can be displayed in the Firebug Console or in the
 * "Server" request tab.
 *
 * For more informtion see: http://www.firephp.org/
 *
 * @copyright   Copyright (C) 2007-2008 Christoph Dorn
 * @author      Christoph Dorn <christoph@christophdorn.com>
 * @license     http://www.opensource.org/licenses/bsd-license.php
 *
 * @return Boolean  True if FirePHP was detected and headers were written, false otherwise
 */
function fb() {

  $instance = FirePHP::getInstance(true);

  $args = func_get_args();
  return call_user_func_array(array($instance,'fb'),$args);
}
?><?php
    /**
     * Form Helper (next HtmlHelper)
     * @since 1.2
     * @package Eaze
     * @subpackage Helpers
     * @author sergeyfast
     */
    class FormHelper {

        const Text        = 'text';
        const Password    = 'password';
        const Hidden      = 'hidden';
        const CheckBox    = 'checkbox';
        const RadioButton = 'radio';
        const File        = 'file';
        const Submit      = 'submit';
        const Button      = 'button';


        private static function createInput( $params ) {
            return '<input ' . $params . ' />';
        }

        private static function createSelect( $params, $options ) {
            return '<select ' . $params . '>' . $options . '</select>';
        }

        private static function createTextArea( $params, $value ) {
            return '<textarea ' . $params . '>' . self::RenderToForm( $value ) . '</textarea>';
        }

        private static function createLink( $params, $title ) {
            return '<a ' . $params . '>' . $title  . '</a>';
        }

        private static function generateParams( $type, $name, $value = null, $controlId = null, $class = null, $params = array() ) {
            $params = array(
                'type'    => $type
                , 'name'  => $name
                , 'value' => $value
                , 'id'    => $controlId
                , 'class' => $class
            ) + $params;

            $result = '';
            foreach( $params as $key => $value ) {
                if ( $value === null ) {
                    continue;
                }

                if ( $key == 'value' ) {
                    $value = self::RenderToForm( $value );
                } else if ( $key == 'class' ) {
                    if ( is_array( $value ) ) {
                        $value = implode( ' ', $value );
                    }
                }

                $result .= $key . '="' . $value . '" ';
            }

            return $result;
        }


        public static function FormInput( $name, $value = null, $controlId = null, $class = null, $params = array() ) {
            return self::createInput( self::generateParams( self::Text, $name, $value, $controlId, $class, $params ) );
        }

        public static function FormHidden( $name, $value = null, $controlId = null, $class = null, $params = array() ) {
            return self::createInput( self::generateParams( self::Hidden, $name, $value, $controlId, $class, $params ) );
        }

        public static function FormPassword( $name, $value = null, $controlId = null, $class = null, $params = array() ) {
            return self::createInput( self::generateParams( self::Password, $name, $value, $controlId, $class, $params ) );
        }

        public static function FormFile( $name, $value = null, $controlId = null, $class = null, $params = array() ) {
            return self::createInput( self::generateParams( self::File, $name, $value, $controlId, $class, $params ) );
        }

        public static function FormRadioButton( $name, $value = null, $controlId = null, $class = null, $checked = false, $params = array() ) {
            if ( !empty( $checked ) ) {
                $params['checked'] = 'checked';
            }

            return self::createInput( self::generateParams( self::RadioButton, $name, $value, $controlId, $class, $params ) );
        }

        public static function FormCheckBox( $name, $value = null, $controlId = null, $class = null, $checked = false, $params = array() ) {
            if ( !empty( $checked ) ) {
                $params['checked'] = 'checked';
            }

            return self::createInput( self::generateParams( self::CheckBox, $name, $value, $controlId, $class, $params ) );
        }


        public static function FormTextArea( $name, $value = null, $controlId = null, $class = null, $params = array() )  {
            return self::createTextArea( self::generateParams( null, $name, null, $controlId, $class, $params), $value );
        }


        public static function FormEditor( $name, $value = null, $controlId = null, $class = null, $params = array() )  {
            $editor = 'mceEditor';
            if ( !empty( $class ) ) {
                if ( is_array( $class ) ) {
                    $class[] = $editor;
                } else {
                    $class = array( $editor, $class );
                }
            } else {
                $class = $editor;
            }

            return self::createTextArea( self::generateParams( null, $name, null, $controlId, $class, $params), $value );
        }


        public static function FormSubmit( $name, $value = null, $controlId = null, $class = null, $params = array() ) {
            return self::createInput( self::generateParams( self::Submit, $name, $value, $controlId, $class, $params ) );
        }

        public static function FormButton( $name, $value = null, $controlId = null, $class = null, $params = array() ) {
            return self::createInput( self::generateParams( self::Button, $name, $value, $controlId, $class, $params ) );
        }

        public static function FormLink( $link, $title, $controlId = null, $class = null, $params = array() ) {
            $params['href'] = $link;

            return self::createLink( self::generateParams( null, null, null, $controlId, $class, $params ), $title );
        }



        /**
         * Render To Form
         * Convert special characters to HTML entities
         *
         * @param string $value
         * @return string
         */
        public static function RenderToForm( $value ) {
            return ( htmlspecialchars( trim( $value ) ) );
        }


        /**
         * Form DateTime
         *
         * @param string   $name
         * @param DateTime $value
         * @param string   $format
         * @param string $type
         * @return string
         */
        public static function FormDateTime( $name, $value = null, $format = 'd.m.Y G:i', $type = 'dateTime' ) {
            if ( empty( $value ) ) {
                $value = '';
            } else if ( is_object( $value ) ) {
                $value = $value->format( $format );
            }

            return self::createInput( self::generateParams( self::Hidden, $name, $value, null, 'dtpicker', array( 'rel' => $type ) ) );
        }



        public static function FormDate( $name, $value = null, $format = 'd.m.Y' ) {
            return self::FormDateTime( $name, $value, $format, 'date' );
        }

        public static function FormTime( $name, $value = null, $format = 'G:i' ) {
            return self::FormDateTime( $name, $value, $format, 'time' );
        }


        public static function FormSelect( $name, $data = null
                                        , $dataKey = null
                                        , $dataTitle = null
                                        , $value = null
                                        , $controlId = null
                                        , $class = null
                                        , $nullValue = true
                                        , $callback = null
                                        , $params = array() ) {
            $select = self::generateParams( null, $name, null, $controlId, $class, $params );
            $options = '';

            // allow null value
            if ( $nullValue ) {
                if( is_string( $nullValue ) ) {
                    $options .= sprintf( '<option value="">%s</option>', $nullValue );
                } else {
                    $options .= '<option value=""></option>';
                }
            }

            if (  !empty( $data ) ) {
                foreach ( $data as $index => $element ) {
                    if ( is_object( $element ) ) {
                        $title = $element->$dataTitle;
                        $key   = $element->$dataKey;
                    } else if ( is_array( $element ) ) {
                        $title = $element[$dataTitle];
                        $key   = $element[$dataKey];
                    } else {
                        $title = $element;
                        $key   = $index;
                    }

                    if ( !empty( $callback ) ) {
                        $title = call_user_func_array(  $callback, array( $title, $element ) );
                    }

                    $options .= '<option value="' . $key . '"';

                    if ( !empty( $params['multiple'] ) ) {
                        $options .= ( in_array( $key, $value ) ) ? ' selected="selected">' : '>';
                    } else {
                        $options .= ( !empty($value) && $value == $key ) ? ' selected="selected">' : '>';
                    }

                    $options .=  self::RenderToForm( $title ) . '</option>';
                }
            }

            return self::createSelect( $select, $options );
        }


        public static function FormSelectMultiple( $name
                                                , $data
                                                , $dataKey = null
                                                , $dataTitle = null
                                                , $value = array()
                                                , $controlId = null
                                                , $class = null
                                                , $callback = null
                                                , $params = array() ) {
            $params['multiple'] = 'multiple';
            return self::FormSelect( $name, $data, $dataKey, $dataTitle, $value, $controlId, $class, false, $callback, $params );
        }
    }
?>
<?php
    /**
     * HTML Helper
     *
     * @package Eaze
     * @subpackage Eaze.Helpers
     */
    class HtmlHelper {

        /**
         * Render To Form
         * Convert special characters to HTML entities
         *
         * @param string $value
         * @return string
         */
        public static function RenderToForm( $value ) {
            return FormHelper::RenderToForm( $value );
        }

        /**
         * @static Generate File Upload Form control
         * @param string $controlName
         * @param string $value
         * @param string $controlId
         * @param string  $className
         * @return string
         */
        public static function FormFile( $controlName, $value = "", $controlId = null, $className = null ) {
            return FormHelper::FormFile( $controlName, $value, $controlId, $className );
        }


        /**
         * Form Text Area
         *
         * @param string  $controlName
         * @param string  $value
         * @param int $rows
         * @param int $cols
         * @param string  $controlId
         * @param null $readonly
         * @param string  $className
         * @return string
         */
        public static function FormTextArea( $controlName, $value = "", $rows = 5, $cols = 80, $controlId = null, $readonly = null, $className = null ) {
            return FormHelper::FormTextArea( $controlName, $value, $controlId, $className, array( 'rows' => $rows, 'cols' => $cols, 'readonly' => $readonly ) );
        }


        public static function FormEditor( $controlName, $value, $rows = 10, $cols = 80, $controlId = null ) {
            return FormHelper::FormEditor( $controlName, $value, $controlId, null, array( 'rows' => $rows, 'cols' => $cols ) );
        }


        /**
         * Form Checkbox
         *
         * @param string  $controlName
         * @param string $checked
         * @param integer $controlId
         * @param string  $value
         * @param string $class
         * @return string
         */
        public static function FormCheckBox( $controlName, $checked = null, $controlId = null, $value = null, $class = null ) {
            return FormHelper::FormCheckBox( $controlName, $value, $controlId, $class, $checked );
        }


        /**
         * Form RadioButton
         *
         * @param string  $controlName
         * @param string  $value
         * @param bool $checked
         * @param integer $controlId
         * @return string
         */
        public static function FormRadioButton( $controlName, $value = null, $checked = false, $controlId = null ) {
            return FormHelper::FormRadioButton( $controlName, $value, $controlId, null, $checked );
        }


        /**
         * Form Input
         *
         * @param string  $controlName
         * @param string  $value
         * @param integer $size
         * @param integer $controlId
         * @param null $class
         * @param bool $disabled
         * @return string
         */
        public static function FormInput( $controlName, $value = "", $size = 80, $controlId = null, $class = null, $disabled = false ) {
            return FormHelper::FormInput( $controlName, $value, $controlId, $class, array( 'disabled' => $disabled ? 'disabled' : null, 'size' => $size ) );
        }


        /**
         * Form Hidden
         *
         * @param string  $controlName
         * @param string  $value
         * @param string $controlId
         * @param null $className
         * @return string
         */
        public static function FormHidden( $controlName, $value = "", $controlId = null, $className = null ) {
            return FormHelper::FormHidden( $controlName, $value, $controlId, $className );
        }


        /**
         * Form Password
         *
         * @param string  $controlName
         * @param string  $value
         * @param integer $size
         * @param integer $controlId
         * @param string $class
         * @return string
         */
        public static function FormPassword( $controlName, $value = "", $size = 80, $controlId = null, $class = null ) {
            return FormHelper::FormPassword( $controlName, $value, $controlId, $class, array( 'size' => $size ) );
        }


        /**
         * Form DateTime
         *
         * @param string   $controlName
         * @param DateTime $value
         * @param string   $format
         * @param string $type
         * @return string
         */
        public static function FormDateTime( $controlName, $value = null, $format = 'd.m.Y G:i', $type = 'dateTime' ) {
            return FormHelper::FormDateTime( $controlName, $value, $format, $type );
        }



        public static function FormDate( $controlName, $value = null, $format = 'd.m.Y' ) {
            return self::FormDateTime( $controlName, $value, $format, 'date' );
        }

        public static function FormTime( $controlName, $value = null, $format = 'G:i' ) {
            return self::FormDateTime( $controlName, $value, $format, 'time' );
        }


        /**
         * Form Select Control
         *
         * @param string    $controlName
         * @param array     $data
         * @param string    $dataKey
         * @param string    $dataTitle
         * @param string    $currentId
         * @param bool      $nullValue
         * @param null $callback
         * @param null $class
         * @param null $controlId
         * @return string
         */
        public static function FormSelect( $controlName, $data = null, $dataKey = null, $dataTitle = null, $currentId = null, $nullValue = true, $callback = null, $class = null, $controlId = null ) {
            return FormHelper::FormSelect( $controlName, $data, $dataKey, $dataTitle, $currentId, $controlId, $class, $nullValue, $callback );
        }


        /**
         * Form Select Multiply Control
         *
         * @param string    $controlName
         * @param array     $data
         * @param null $dataKey
         * @param null $dataTitle
         * @param array     $selectedIds
         * @param integer   $size
         * @param null $callback
         * @param null $class
         * @param null $controlId
         * @return string
         */
        public static function FormSelectMultiple( $controlName, $data, $dataKey = null, $dataTitle = null, $selectedIds = array(), $size = 10, $callback = null, $class = null, $controlId = null ) {
            return FormHelper::FormSelectMultiple( $controlName, $data, $dataKey, $dataTitle, $selectedIds, $controlId, $class, $callback, array( 'size' => $size ) );
        }
    }
?><?php
    /**
     * Image Helper
     *
     * @package Eaze
     * @subpackage Eaze.Helpers
     */
    class ImageHelper {

        /**
         * Resize JPEG  Image
         *
         * @param string       $original    the original file path
         * @param string       $thumbnail   the thumbnail path
         * @param integer      $max_width   thumb width
         * @param integer      $max_height  thumb height
         * @param integer      $quality     jpeg queality
         * @param boolean      $scale       scale thumb (true) or fixed size (false)
         * @return boolena operation result
         */
        public static function Resize( $original, $thumbnail, $max_width, $max_height, $quality, $scale = true ) {
            $imagetype = self::IsImage( $original );
            if ( false === $imagetype ) {
                return false;
            }

            list ($src_width, $src_height, $type, $w) = getimagesize($original);

            $srcImage = false;
            switch ($imagetype) {
                case "JPEG":
                    $srcImage = imagecreatefromjpeg($original);
                    break;
                case "PNG":
                    $srcImage = imagecreatefrompng($original);
                    break;
                case "GIF":
                    $srcImage = imagecreatefromgif($original);
                    break;
                case "BMP":
                    $srcImage = imagecreatefromwbmp($original);
                    break;
                default:
                    $srcImage = imagecreatefromgd2($original);
                    break;
            }


            if (!$srcImage ) {
                return false;
            }

            # image resizes to natural height and width
            if ($scale == true) {
                if( empty( $max_width ) || empty( $max_height ) || empty( $src_width ) || empty( $src_height ) ) {
                    return false;
                }

                $src_proportion     = $src_width / $src_height;
                $target_propotion   = $max_width / $max_height;

                if ( $src_height <= $max_height && $src_width <= $max_width  ) {
                    $thumb_width  = $src_width;
                    $thumb_height = $src_height;
                } else if( $src_proportion >= $target_propotion ) {
                    $thumb_width    = $max_width;
                    $thumb_height   = floor($src_height * ($max_width / $src_width));
                } else if( $src_proportion < $target_propotion ) {
                    $thumb_height   = $max_height;
                    $thumb_width    = floor($src_width * ($max_height / $src_height));
                } else {
                    $thumb_width = $max_height;
                    $thumb_height = $max_height;
                }

                if (!@$destImage = imagecreatetruecolor($thumb_width, $thumb_height)) {
                    return false;
                }

                if (!@imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $thumb_width, $thumb_height, $src_width, $src_height)) {
                    return false;
                }

            # image is fixed to supplied width and height and cropped
            } else if ($scale == false) {

                $ratio = $max_width / $max_height;

                 # thumbnail is not a square
                 if ($ratio != 1) {

                    $ratio_width = $src_width / $max_width;
                    $ratio_height = $src_height / $max_height;
                    if ($ratio_width > $ratio_height) {
                        $thumb_width = $src_width / $ratio_height;
                        $thumb_height = $max_height;
                    } else {
                        $thumb_width = $max_width;
                        $thumb_height = $src_height / $ratio_width;
                    }

                    $off_w = round( ( $thumb_width - $max_width ) / 2 );
                    $off_h = round( ( $thumb_height - $max_height ) / 2 );

                    if (!@$destImage = imagecreatetruecolor($max_width, $max_height)) {
                        return false;
                    }

                    if (!@imagecopyresampled($destImage, $srcImage, 0, 0, $off_w, $off_h, $thumb_width, $thumb_height, $src_width, $src_height)) {
                        return false;
                    }

                 # thumbnail is square
                 } else {
                    if ($src_width > $src_height) {
                        $off_w = ($src_width - $src_height) / 2;
                        $off_h = 0;
                        $src_width = $src_height;
                    } else if ($src_height > $src_width) {
                        $off_w = 0;
                        $off_h = ($src_height - $src_width) / 2;
                        $src_height = $src_width;
                    } else {
                        $off_w = 0;
                        $off_h = 0;
                    }

                    if (!@$destImage = imagecreatetruecolor($max_width, $max_height)) {
                        return false;
                    }

                    if (!@imagecopyresampled($destImage, $srcImage, 0, 0, $off_w, $off_h, $max_width, $max_height, $src_width, $src_height)) {
                        return false;
                    }
                 }
            }

            @imagedestroy($srcImage);

            if( function_exists( "imageantialias" ) ) {
                if (!@imageantialias($destImage, true)) {
                    return false;
                }
            }

            if ( !empty( $thumbnail ) ) {
                if (!@imagejpeg($destImage, $thumbnail, $quality)) {
                    return false;
                }

                @imagedestroy($destImage);
            } else {
                ob_start();
                imagejpeg($destImage, null, $quality);
                $result = ob_get_clean();

                imagedestroy($destImage);
                return $result;
            }

            return true;
        }

        /**
         * Crop JPEG image
         *
         * @static
         * @param string        $original       the original file path
         * @param string        $thumbnail      the thumbnail path
         * @param integer       $x              offset x
         * @param integer       $y              offset y
         * @param integer       $width          crop width
         * @param integer       $height         crop height
         * @param integer       $quality        jpeg quality
         * @return bool|string
         */
        public static function Crop( $original, $thumbnail, $x, $y, $width, $height, $quality ) {
            $imagetype = self::IsImage( $original );
            if ( false === $imagetype ) {
                return false;
            }

            list ($src_width, $src_height, $type, $w) = getimagesize($original);

            $srcImage = false;
            switch ($imagetype) {
                case "JPEG":
                    $srcImage = imagecreatefromjpeg($original);
                    break;
                case "PNG":
                    $srcImage = imagecreatefrompng($original);
                    break;
                case "GIF":
                    $srcImage = imagecreatefromgif($original);
                    break;
                case "BMP":
                    $srcImage = imagecreatefromwbmp($original);
                    break;
                default:
                    $srcImage = imagecreatefromgd2($original);
                    break;
            }


            if (!$srcImage ) {
                return false;
            }

            if (!@$destImage = imagecreatetruecolor($width, $height)) {
                return false;
            }

            if (!@imagecopyresampled($destImage, $srcImage, 0, 0, $x, $y, $width, $height, $width, $height)) {
                return false;
            }

            @imagedestroy($srcImage);

            if( function_exists( "imageantialias" ) ) {
                if (!@imageantialias($destImage, true)) {
                    return false;
                }
            }

            if ( !empty( $thumbnail ) ) {
                if (!@imagejpeg($destImage, $thumbnail, $quality)) {
                    return false;
                }

                @imagedestroy($destImage);
            } else {
                ob_start();
                imagejpeg($destImage, null, $quality);
                $result = ob_get_clean();

                imagedestroy($destImage);
                return $result;
            }

            return true;
        }

        /**
         * Is Image
         *
         * @param string $file
         */
        public static function IsImage( $file ) {
            $file_format = false;

            if ( !file_exists( $file ) ) {
                return $file_format;
            }

            //grab first 8 bytes, should be enough for most formats
            $image_data = fopen($file, "rb");
            $header_bytes = fread($image_data, 8);
            fclose ($image_data);

            //compare header to known signatures
            if (!strncmp ($header_bytes, "\xFF\xD8", 2))
                $file_format = "JPEG";
            else if (!strncmp ($header_bytes, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A", 8)) {
                $file_format = "PNG";
            } else if (!strncmp ($header_bytes, "FWS", 3) || !strncmp ($header_bytes, "CWS", 3 ) ) {
                $file_format = "SWF";
            } else if (!strncmp ($header_bytes, "BM", 2)) {
                $file_format = "BMP";
            }  else if (!strncmp ($header_bytes, "\x50\x4b\x03\x04", 4)) {
                $file_format = "ZIP";
            }  else if (!strncmp ($header_bytes, "GIF", 3)) {
                $file_format = "GIF";
            }  else if(!strncmp ($header_bytes, "\x49\x49\x2a\x00",4)) {
                $file_format = "TIF";
            } else if(!strncmp ($header_bytes, "\x4D\x4D\x00\x2a",4)) {
                $file_format = "TIF";
            }

            return $file_format;
        }


        /**
         * @static Returns dimensions of an image
         * @param string $filePath filesystem path to the image file
         * @return array Array with fields 'width' & 'height'
         */
        public static function GetImageSizes( $filePath ) {
            list ( $width, $height ) = getimagesize( $filePath );
            return array( 'width' => $width, 'height' => $height );
        }
    }
?><?php
    /**
     * Helps to render JS
     *
     * @package Eaze
     * @subpackage Helpers
     * @since 1.3
     * @author sergeyfast
     * @static
     */
    class JsHelper extends AssetHelper {

        /**
         * Minify scripts or not
         * @var bool
         */
        public static $Minify = true;

        /**
         * Max Group Size for minify
         * @var int
         */
        public static $MaxGroups = 20;

        /**
         * Default Hostname
         * @var string default hostname
         */
        public static $Hostname = 'static';

        /**
         * Current Type
         * @var string
         */
        private static $type = self::JS;

        /**
         * Add File
         * @param string $file single JS file
         * @param string $mode browser mode
         */
        public static function PushFile( $file, $mode = self::AnyBrowser ) {
            parent::addFile( self::$type, $file, $mode );
        }


        /**
         * Add multiple JS file
         * @param string[] $files array of JS files
         * @param string $mode browser mode
         */
        public static function PushFiles( $files, $mode = self::AnyBrowser ) {
            foreach ( $files as $file ) {
                self::PushFile( $file, $mode );
            }
        }


        /**
         * Add multiple JS grouped files
         * @param $groups array of js grouped files
         * @return void
         */
        public static function PushGroups( $groups ) {
            foreach( $groups as $mode => $files ) {
                self::PushFiles( $files, $mode );
            }
        }


        /**
         * Add JS line to JS code
         * @param string $line
         * @param string $mode browser mode
         */
        public static function PushLine( $line, $mode = self::AnyBrowser ) {
            parent::addLine( self::$type, $line, $mode );
        }


        /**
         * Flush All Modes
         * @return string
         */
        public static function Flush() {
            if ( self::$PostProcess ) {
                return self::setFlushPoint( self::$type, self::$Minify, self::$Hostname, self::$MaxGroups );
            }

            $result = '';
            foreach ( self::$BrowserModes as $mode ) {
                $result .= parent::flushMode( self::$type, $mode, self::$Minify, self::$Hostname, self::$MaxGroups );
            }

            return $result;
        }


        /**
         * Remove File from Queue
         * @static
         * @param string $file
         * @param string $mode
         * @return bool
         */
        public static function RemoveFile( $file, $mode = self::AnyBrowser ) {
            return parent::deleteFile( self::$type, $file, $mode );
        }


        /**
         * Init Helper
         * @static
         * @param bool   $minify
         * @param int    $maxGroups
         * @param string $hostname
         * @return void
         */
        public static function Init( $minify = true, $maxGroups = 25, $hostname = 'static' ) {
            self::$Minify    = $minify;
            self::$MaxGroups = $maxGroups;
            self::$Hostname  = $hostname;
        }
    }
?><?php
    /**
     * Object Helper
     * @package Eaze
     * @subpackage Helpers
     * @author sergeyfast
     */
    class ObjectHelper {

        /**
         * Data to JSON
         * @param mixed $object
         * @return string
         */
        public static function ToJSON( $object ) {
            if ( function_exists( 'json_encode' ) ) {
                return json_encode( $object );
            } else {
                $f = FirePHP::getInstance( true );
                return $f->json_encode( $object );
            }
        }


        /**
         * Data from JSON
         * @param string $string
         * @return mixed
         */
        public static function FromJSON( $string ) {
            return json_decode( $string );
        }
    }

?><?php
    /**
     * Text Helper
     *
     * @package Eaze
     * @subpackage Helpers
     * @author sergeyfast
     */
    class TextHelper {

        /**
         * Translit
         *
         * @param string $cyrString
         * @return string
         */
        public static function Translit( $cyrString ) {
            static $replacement;

            if ( empty( $replacement ) ) {
                $replacement = array(
                    'ё' => 'yo', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ь' => '', 'ю' => 'yu', 'я' => 'ya',
                    'Ё' => 'Yo', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch', 'Ъ' => '', 'Ь' => '', 'Ю' => 'Yu', 'Я' => 'Ya'
                );

                $replacement += array_combine(
                    preg_split('/(?<!^)(?!$)/u', 'абвгдежзийклмнопрстуфхыэАБВГДЕЖЗИЙКЛМНОПРСТУФХЫЭ' )
                    , preg_split('/(?<!^)(?!$)/u', 'abvgdegziyklmnoprstufhieABVGDEGZIYKLMNOPRSTUFHIE' )
                );
            }

            return strtr( $cyrString, $replacement );
        }


        /**
         * Replace Cyr
         * @static
         * @param string $cyrString
         * @return string
         */
        public static function ReplaceCyr( $cyrString ) {
            $tr = array(
                'А'   => 'A', 'Е' => 'E'
                , 'К' => 'K', 'М' => 'M'
                , 'О' => 'O', 'Т' => 'T'
                , 'а' => 'a', 'е' => 'e'
                , 'к' => 'k', 'о' => 'o'
            );

            return strtr( $cyrString, $tr );
        }


        /**
         * Get First Difference
         *
         * @param string $firstString
         * @param string $secondString
         * @return array
         */
        public function FirstDifference( $firstString, $secondString ) {
            $result = array(
                'difference'  => -1
                , 'message'   => 'Empty string'
                , 'chars'     => ''
                , 'identical' => true
            );

            if ( ( true == empty( $firstString ) )
                 || ( true == empty( $secondString ) )
            ) {
                $result['identical'] = false;

                return $result;
            }

            // For
            for ( $i = 0; $i < strlen( $firstString ); $i++ ) {
                if ( strlen( $secondString ) == $i ) {
                    $result['difference'] = $i;
                    break;
                }

                if ( $firstString[$i] != $secondString[$i] ) {
                    $result['difference'] = $i;
                    $result['identical'] = false;
                    $result['chars'] = sprintf( '[%d]!=[%d]', ord( $firstString[$i] ), ord( $secondString[$i] ) );

                    break;
                }
            }

            // Check Length
            if ( strlen( $firstString ) != strlen( $secondString ) ) {
                $result['difference'] = strlen( $firstString );
                $result['identical']  = false;
                $result['message']    = 'Invalid length. Data: ' . $firstString . ' != ' . $secondString;

                return $result;
            }

            if ( $result['identical'] == false ) {
                $result['message'] = sprintf( 'First Difference in %s char: (%s). Data: %s != %s) ', $result['difference'], $result['chars'], $firstString, $secondString );
            } else {
                $result['message'] = 'Ok';
            }

            return $result;
        }


        /**
         * Convert Text To UTF-8
         * @static
         * @param string $string
         * @param string $sourceCharset
         * @return string
         */
        public static function ToUTF8( $string, $sourceCharset = 'CP1251' ) {
            return iconv( $sourceCharset, 'UTF-8', $string );
        }


        /**
         * Convert Text From UTF-8 to CP1251
         * @static
         * @param string $string
         * @param string $sourceCharset
         * @param string $destCharset
         * @return string
         */
        public static function FromUTF8( $string, $sourceCharset = 'UTF-8', $destCharset = 'CP1251' ) {
            return iconv( $sourceCharset, $destCharset, $string );
        }


        /**
         * Get Percent String
         * @static
         * @param  number $value
         * @param  number $maxValue
         * @param bool $append append % char
         * @param int $decimals
         * @param string $decPoint decimal separator
         * @return int|float|string
         */
        public static function GetPercentString( $value, $maxValue, $append = false, $decimals = 2, $decPoint = '.' ) {
            $result = 0;

            if ( $maxValue != 0 ) {
                $result = $value / $maxValue * 100;
            }

            $result = number_format( $result, $decimals, $decPoint, '' ) . (( $append ) ? '%' : '');

            return $result;
        }


        /**
         * Get Declension for Value
         * @static
         * @param float $value
         * @return int 1|2|5
         */
        public static function GetDeclension( $value ) {
            $val = Convert::ToInt( $value );
            if ( !(empty( $val ) ) ) {
                if ( $val == 1 ) {
                    $count = 1;
                } elseif( $val < 5 ) {
                    $count = 2;
                } elseif ( $val < 21 ) {
                    $count = 5;
                } else {
                    if ( $val % 10 == 1 ) {
                        $count = 1;
                    } elseif ( $val % 10 < 5 && $val % 10 != 0) {
                        $count = 2;
                    } else {
                        $count = 5;
                    }
                }

                return $count;
            }

            return 5;
        }
    }

?><?php
    /**
     * Xml Lookup
     * @package Eaze
     * @subpackage Helpers
     * @author sergeyfast
     */
    class XmlLookup {

        /**
         * @var DOMDocument
         */
        private $doc;

        /**
         * @var DOMXPath
         */
        private $xpath;


        /**
         * Evaluate Xpath
         * @param  string $path xpath expression
         * @return DOMNodeList|mixed returns list or typed value
         */
        public function Get( $path ) {
            $result = $this->xpath->evaluate( $path );

            return $result;
        }


        /**
         * Gets single node value by XPath.
         *
         * @param string $path  XPath expression.
         * @return string
         */
        public function GetSingleValue( $path ) {
            $path = trim( $path );
            if ( empty( $path ) ) {
                return ( $this->doc->childNodes->item( 0 )->nodeValue );
            }

            $list = $this->Get( $path );

            if ( $list->length == 0 ) {
                return null;
            }

            return $list->item( 0 )->nodeValue;
        }


        /**
         * Gets array of node values selected by XPath
         *
         * @param string $path  XPath expression.
         * @return string
         */
        public function GetValues( $path ) {
            $list = $this->Get( $path );
            $result = array();

            for ( $i = 0; $i < $list->length; $i++ ) {
                $result[$list->item( $i )->nodeName] = $list->item( $i )->nodeValue;
            }

            return $result;
        }


        public function Dump( $path ) {
            $result = $this->Get( $path );

            if ( $result instanceof DOMNodeList ) {
                foreach ( $result as $node ) {
                    XmlHelper::Dump( $node );
                }
            } else {
                XmlHelper::Dump( $result );
            }
        }


        public function __construct( $node ) {
            if ( $node instanceof DOMDocument ) {
                $this->doc = $node;
            } else {
                $this->doc = new DOMDocument();
                $this->doc->preserveWhiteSpace = false;
                $this->doc->appendChild( $this->doc->importNode( $node, true ) );
            }

            $this->xpath = new DOMXPath( $this->doc );
        }
    }


    /**
     * XmlHelper
     * @package Eaze
     * @subpackage Helpers
     * @author sergeyfast
     */
    class XmlHelper {

        public static function DumpFromString( $result, $stripTags = true ) {
            $doc = new DOMDocument();
            $doc->loadXML( $result );
            $doc->formatOutput = true;
            $result = $doc->saveXML();


            if ( $stripTags ) {
                $result = htmlspecialchars( $result );
            }

            printf( "<pre>%s</pre>", $result );
        }


        /**
         * Dump DOM Element
         * @static
         * @param DOMElement $node
         * @param bool $stripTags
         * @return void
         */
        public static function Dump( DOMElement $node, $stripTags = true ) {
            $doc = new DOMDocument();
            $doc->appendChild( $doc->importNode( $node, true ) );

            $result = $doc->saveXML();

            printf( '<pre>%s</pre>', ($stripTags) ? htmlspecialchars($result) : $result );
        }


        /**
         * Get Child node by tag name
         *
         * @params string $nodeName
         * @param $nodeName
         * @param DOMNode $node
         * @return DOMNode
         */
        public static function GetChildNode( $nodeName, DOMNode $node ) {
            if ( $node->hasChildNodes() ) {
                foreach ( $node->childNodes as $childNode ) {
                    if ( $childNode->nodeName === $nodeName ) {
                        return $childNode;
                    }
                }
            }

            return null;
        }


        /**
         * Get Xml Lookup
         *
         * @param DOMElement $node
         * @return XmlLookup
         */
        public static function GetLookup( DOMElement $node ) {
            return new XmlLookup( $node );
        }


        /**
         * Merges two xml tree
         * if first has attribute and second also has it then sets
         * first elemnt attribute value as second element attribute value.
         *
         * @author Anton Lyzin
         * @param DOMNode $parent   the source tree
         * @param DOMNode $child    the second tree
         * @return mixed  result tree
         */
        public static function MergeNodes( $parent, $child ) {
            $main = new DOMDocument();

            $main->appendChild( $main->importNode( $parent, true ) );

            $rootElement = $main->childNodes->item( 0 );

            if ( $child instanceof DomText ) {
                $rootElement->deleteData( 0, strlen( $rootElement->data ) );
                $rootElement->insertData( 0, $child->data );
            }

            if ( !is_null( $child ) && !is_null( $child->childNodes ) ) {
                $i = 0;
                foreach ( $child->childNodes as $childNode ) {
                    if ( $childNode instanceof DOMComment ) {
                        continue;
                    }

                    $find = false;

                    if ( false == is_null( $rootElement->childNodes ) ) {
                        foreach ( $rootElement->childNodes as $pChildNode ) {
                            if ( $pChildNode instanceof DOMComment ) {
                                continue;
                            }

                            if ( ( $pChildNode->nodeName === $childNode->nodeName )
                                 && (
                                    ( $childNode instanceof DomText )
                                    || ( ( $pChildNode->getAttribute( "name" ) == $childNode->getAttribute( "name" ) )
                                         && ( trim( $pChildNode->getAttribute( "name" ) ) != "" )
                                         && ( trim( $childNode->getAttribute( "name" ) ) != "" ) )
                                    || ( ( $pChildNode->getAttribute( "alias" ) == $childNode->getAttribute( "alias" ) )
                                         && ( trim( $pChildNode->getAttribute( "alias" ) ) != "" )
                                         && ( trim( $childNode->getAttribute( "alias" ) ) != "" ) )
                                    || ( ( false == $childNode->hasAttribute( "name" ) )
                                         && ( false == $pChildNode->hasAttribute( "name" ) )
                                         && ( ( false == $childNode->hasAttribute( "alias" ) )
                                              && ( false == $pChildNode->hasAttribute( "alias" ) ) ) )
                                )
                            ) {
                                $find = true;

                                $element = self::MergeNodes( $pChildNode, $childNode );

                                $rootElement->replaceChild( $main->importNode( $element, true ), $pChildNode );

                                break;
                            }
                        }
                    }

                    if ( false == $find ) {
                        $main->childNodes->item( 0 )->appendChild( $main->importNode( $childNode, true ) );
                    }
                }
            }

            if ( false == is_null( $child ) && false == is_null( $child->attributes ) ) {
                // For all nodes in child
                foreach ( $child->attributes as $childNode ) {
                    if ( $rootElement->hasAttribute( $childNode->nodeName ) ) {
                        $rootElement->removeAttribute( $childNode->nodeName );
                    }

                    $rootElement->setAttribute( $childNode->nodeName, $childNode->value );
                }
            }

            // TODO add merging attributes
            $main->removeChild( $rootElement );
            $main->appendChild( $rootElement );

            return ( $main->childNodes->item( 0 ) );
        }
    }

?>