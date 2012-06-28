<?php
    /**
     * Simple Auth Utility
     */
    class AuthUtility {

        /**
         * Salt
         * @var string
         */
        public static $Salt = 'saltedp@$$-';

        /**
         * Login Cookie Lifetime in seconds
         */
        const LoginCookieLifeTime = 180000;



        /**
         * Get User By Credentials
         *
         * @param string $login
         * @param string $password
         * @param string $class
         * @param string $connectionName
         * @return User
         */
        public static function GetByCredentials( $login, $password, $class, $connectionName = null ) {
            if ( ( empty( $login ) ) || empty( $password ) ) {
                return null;
            }

            $factory     = BaseFactory::GetInstance( $class . 'Factory' );
            $searchArray = array( "login" => $login, "password" => $password );
            $options     = array( BaseFactory::WithoutDisabled => true, BaseFactory::WithLists => true );
            
            $object      = $factory->GetOne( $searchArray, $options, $connectionName );

            return $object;
        }


        /**
         * Encode / Salt Password
         * 
         * @param string $password  password
         * @param string $type      salt or md5
         * @return string
         */
        public static function EncodePassword( $password, $type = 'salt' ) {
            switch( $type ) {
                case 'salt':
                    return md5( self::$Salt . md5(  self::$Salt . $password  ));
                case 'md5':
                    return md5( $password );
            }

            return $password;
        }


        /**
         * Get Current User
         *
         * @param string $class
         * @return object
         */
        public static function GetCurrentUser( $class ) {
            $user = Session::getParameter( $class );

            return $user;
        }


        /**
         * Login User
         *
         * @param object $user
         * @param string $class
         */
        public static function Login( $user, $class ) {
            Cookie::setCookie(  $class . "[login]",    $user->login,    time() + self::LoginCookieLifeTime );
            Cookie::setCookie(  $class . "[password]", $user->password, time() + self::LoginCookieLifeTime );

            Session::setParameter( $class,           $user );
            Session::setParameter( $class . "Logged", true);
        }


        /**
         * Logout User
         *
         * @param string $class
         */
        public static function Logout( $class ) {
            Cookie::setCookie(  $class . "[login]",    "", time() - 1024 );
            Cookie::setCookie(  $class . "[password]", "", time() - 1024 );

            Session::setParameter( $class,            null );
            Session::setParameter( $class . "Logged", false);
        }


        /**
         * Set Variables To Response
         *
         * @param object $user
         * @param string $class
         */
        public static function ToResponse( $user, $class ) {
            Response::setObject( "__" . $class, $user );
            Response::setBoolean( "__" . $class . "Logged", true );
        }
    }
?>