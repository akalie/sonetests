<?php
    class DaemonUtility {
        
        /**
         * Format Parameters
         *
         * @param array $parameters
         * @return bool
         */
        private static function formatParameters( &$parameters) {
            
            /**
             * Structure (value indicates required attribute)
             */
            $structure = array(
                "package"            => true
                , "method"           => true
                , "title"            => true
                , "maxExecutionTime" => false // default 00:03:00
                , "startDate"        => false // default null
                , "endDate"          => false // default null
                , "active"           => false // default true
                , "params"           => false // default null
            );
            
            foreach ( $structure as $key => $value ) {
                if ( !isset( $parameters[$key] ) ) {
                    $parameters[$key] = null;
                }
                
                if ( is_null( $parameters[$key] ) && $value ) {
                    return false;
                }
            }
            
            return true;
        }
        
        
        /**
         * Initialize Daemon
         *
         * @param array $parameters
         * @return Daemon
         */
        public function Init( $parameters ) {
            // check parameters value
            if ( empty( $parameters ) ) {
                Logger::Debug( __METHOD__, "Parameters is null");
                return null;
            }
            
            // check parameters structure
            $result = self::formatParameters( $parameters );
            if ( $result == false ) {
                Logger::Debug( __METHOD__, "Parameters array is corrupted");
                return null;
            }
            
            $daemon = Daemon::GetInstance( $parameters );
            if(  empty( $daemon) ) {
                return false;
            }
            
            return $daemon;
        }
        
        
        /**
         * Run
         *
         * @param unknown_type $parameters
         * @return bool
         */
        public function Run( $parameters ) {
            Logger::LogLevel( ELOG_DEBUG );
            
            $daemon = self::Init( $parameters );
            
            if (empty($daemon) ){
                Logger::Warning( __METHOD__, "Run failed");
                return false;
            }
            
            $daemon->Run();

            return true;
        }
    }
?>