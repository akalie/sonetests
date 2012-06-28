<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * AuditEvent
     *
     * @package SPS
     * @subpackage Common
     */
    class AuditEvent {

        /** @var int */
        public $auditEventId;

        /** @var string */
        public $object;

        /** @var string */
        public $objectId;

        /** @var string */
        public $message;

        /** @var DateTimeWrapper */
        public $createdAt;

        /** @var int */
        public $auditEventTypeId;

        /** @var AuditEventType */
        public $auditEventType;
    }
?><?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.Common' );

    /**
     * AuditEvent Factory
     *
     * @package SPS
     * @subpackage Common
     */
    class AuditEventFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** AuditEvent instance mapping  */
        public static $mapping = array (
            'class'       => 'AuditEvent'
            , 'table'     => 'auditEvents'
            , 'view'      => 'getAuditEvents'
            , 'flags'     => array( 'CanPages' => 'CanPages', 'ReadOnlyTemplates' => 'ReadOnlyTemplates' )
            , 'cacheDeps' => array( 'auditEventTypes' )
            , 'fields'    => array(
                'auditEventId' => array(
                    'name'          => 'auditEventId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'object' => array(
                    'name'          => 'object'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 100
                )
                ,'objectId' => array(
                    'name'          => 'objectId'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 200
                )
                ,'message' => array(
                    'name'          => 'message'
                    , 'type'        => TYPE_STRING
                )
                ,'createdAt' => array(
                    'name'          => 'createdAt'
                    , 'type'        => TYPE_DATETIME
                    , 'nullable'    => 'No'
                )
                ,'auditEventTypeId' => array(
                    'name'          => 'auditEventTypeId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'AuditEventType'
                ))
            , 'lists'     => array()
            , 'search'    => array(
                'page' => array(
                    'name'         => 'page'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 0
                )
                ,'pageSize' => array(
                    'name'         => 'pageSize'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 25
                ))
        );
        
        /** @return array */
        public static function Validate( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Validate( $object, self::$mapping, $options, $connectionName );
        }

        /** @return array */
        public static function ValidateSearch( $search, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::ValidateSearch( $search, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool|array */
        public static function UpdateByMask( $object, $changes, $searchArray = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateByMask( $object, $changes, $searchArray, self::$mapping, $connectionName );
        }

        public static function SaveArray( $objects, $originalObjects = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::SaveArray( $objects, $originalObjects, self::$mapping, $connectionName );
        }

        public static function CanPages() {
            return BaseFactory::CanPages( self::$mapping );
        }        
        
        /** @return bool|array */
        public static function Add( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Add( $object, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool */
        public static function AddRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::AddRange( $objects, self::$mapping, $options, $connectionName );
        }

        /** @return bool|array */
        public static function Update( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Update( $object, self::$mapping, $options, $connectionName );
        }

        /** @return bool */
        public static function UpdateRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateRange( $objects, self::$mapping, $options, $connectionName );
        }

        public static function Count( $searchArray, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Count( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return AuditEvent[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return AuditEvent */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return AuditEvent */
        public static function GetOne( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetOne( $searchArray, self::$mapping, $options, $connectionName );
        }
        
        public static function GetCurrentId( $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetCurrentId( self::$mapping, $connectionName );
        }

        public static function Delete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Delete( $object, self::$mapping, $connectionName );
        }

        public static function DeleteByMask( $searchArray, $connectionName = self::DefaultConnection ) {
            return BaseFactory::DeleteByMask( $searchArray, self::$mapping, $connectionName );
        }

        public static function PhysicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::PhysicalDelete( $object, self::$mapping, $connectionName );
        }

        public static function LogicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::LogicalDelete( $object, self::$mapping, $connectionName );
        }

        /** @return AuditEvent */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * AuditEventType
     *
     * @package SPS
     * @subpackage Common
     */
    class AuditEventType {

        /** @var int */
        public $auditEventTypeId;

        /** @var string */
        public $title;

        /** @var string */
        public $alias;
    }
?><?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.Common' );

    /**
     * AuditEventType Factory
     *
     * @package SPS
     * @subpackage Common
     */
    class AuditEventTypeFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** AuditEventType instance mapping  */
        public static $mapping = array (
            'class'       => 'AuditEventType'
            , 'table'     => 'auditEventTypes'
            , 'view'      => 'getAuditEventTypes'
            , 'flags'     => array( 'CanCache' => 'CanCache', 'WithoutTemplates' => 'WithoutTemplates' )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'auditEventTypeId' => array(
                    'name'          => 'auditEventTypeId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'title' => array(
                    'name'          => 'title'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 1000
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'alias' => array(
                    'name'          => 'alias'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 1000
                    , 'nullable'    => 'CheckEmpty'
                ))
            , 'lists'     => array()
            , 'search'    => array()
        );
        
        /** @return array */
        public static function Validate( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Validate( $object, self::$mapping, $options, $connectionName );
        }

        /** @return array */
        public static function ValidateSearch( $search, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::ValidateSearch( $search, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool|array */
        public static function UpdateByMask( $object, $changes, $searchArray = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateByMask( $object, $changes, $searchArray, self::$mapping, $connectionName );
        }

        public static function SaveArray( $objects, $originalObjects = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::SaveArray( $objects, $originalObjects, self::$mapping, $connectionName );
        }

        public static function CanPages() {
            return BaseFactory::CanPages( self::$mapping );
        }        
        
        /** @return bool|array */
        public static function Add( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Add( $object, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool */
        public static function AddRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::AddRange( $objects, self::$mapping, $options, $connectionName );
        }

        /** @return bool|array */
        public static function Update( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Update( $object, self::$mapping, $options, $connectionName );
        }

        /** @return bool */
        public static function UpdateRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateRange( $objects, self::$mapping, $options, $connectionName );
        }

        public static function Count( $searchArray, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Count( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return AuditEventType[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return AuditEventType */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return AuditEventType */
        public static function GetOne( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetOne( $searchArray, self::$mapping, $options, $connectionName );
        }
        
        public static function GetCurrentId( $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetCurrentId( self::$mapping, $connectionName );
        }

        public static function Delete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Delete( $object, self::$mapping, $connectionName );
        }

        public static function DeleteByMask( $searchArray, $connectionName = self::DefaultConnection ) {
            return BaseFactory::DeleteByMask( $searchArray, self::$mapping, $connectionName );
        }

        public static function PhysicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::PhysicalDelete( $object, self::$mapping, $connectionName );
        }

        public static function LogicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::LogicalDelete( $object, self::$mapping, $connectionName );
        }

        /** @return AuditEventType */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
    /**
     * AuditUtility
     * @author Shuler
     */
    class AuditUtility {
        public static function CreateEvent( $type, $object, $objectId, $message = '' ) {
            $eventType = AuditEventTypeFactory::GetOne( array( 'alias' => $type ) );
            if( empty( $eventType ) ) {
                return;
            }

            $event = new AuditEvent();
            $event->object = $object;
            $event->objectId = $objectId;
            $event->auditEventTypeId = $eventType->auditEventTypeId;
            $event->message = $message;
            $event->createdAt = DateTimeWrapper::Now();

            AuditEventFactory::Add( $event );
        }
    }
?><?php
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
?><?php
    /**
     * Daemon
     * 
     */
    class Daemon {
        public $name = null;
        
        public $package = null;
        
        public $method = array();
        
        public $startDate = null;
        
        public $endDate = null;
        
        public $maxExecutionTime = null;
        
        public $params = array();
        
        public $active = null;
        
        /**
         * Get Instance
         *
         * @param Daemon $parameters
         */
        public static function GetInstance( $parameters ) {
            $daemon = new Daemon();
            
            $daemon->package          = $parameters["package"];
            $daemon->method           = $parameters["method"];
            $daemon->name             = $parameters["title"];
            $daemon->maxExecutionTime = $parameters["maxExecutionTime"];
            $daemon->startDate        = $parameters["startDate"];
            $daemon->endDate          = $parameters["endDate"];
            $daemon->active           = $parameters["active"];
            $daemon->params           = $parameters["params"];
            
            if( $daemon->active === null ) {
                $daemon->active = true;
            }
            
            if ( $daemon->maxExecutionTime == null ) {
                $daemon->maxExecutionTime = '00:03:00';
            }
            
            return $daemon;
        }
        
        
        /**
         * Can Run?
         *
         * @return bool
         */
        public function CanRun() {
            if ( !$this->active )  {
                return false;
            }
            
            $now = DateTimeWrapper::Now();
            
            if ( !empty( $this->startDate ) ) {
                if (  $now <= Convert::ToDateTime( $this->startDate ) ) {
                    return false;
                }
            }
            
            if ( !empty( $this->endDate ) ) {
                if ( $now >= Convert::ToDateTime( $this->endDate ) ) {
                    return false;
                }
            }

            return true;           
        }
        
        
        /**
         * Get Method Name
         *
         * @return unknown
         */
        public function GetMethodName() {
            if ( is_array( $this->method ) ) {
                list($m, $n) = $this->method;
                
                return $m . "::" . $n;
            } else {
                return $this->method;
            }
        }
        
        
        /**
         * Get Daemon Lock
         *
         * @return DaemonLock
         */
        public function GetDaemonLock()  {
            $daemonLock = new DaemonLock();
            $daemonLock->maxExecutionTime = $this->maxExecutionTime;
            $daemonLock->methodName       = $this->GetMethodName();
            $daemonLock->packageName      = $this->package;
            $daemonLock->title            = $this->name;
            $daemonLock->isActive         = null;
            
            return $daemonLock;
        }
        
        
        /**
         * Lock
         * @return bool
         */
        public function Lock() {
            $lock = $this->CheckLock();
            if ( !empty( $lock ) ) {
                // check lock for active
                if ( $lock->isActive ) {
                    Logger::Info( "Lock {$lock->title} is active");
                    return false;
                } else {
                    Logger::Warning( "Flusing inactive lock {$lock->title}" );
                    DaemonLockFactory::Delete( $lock );
                }
            }

            $result = DaemonLockFactory::Add( $this->GetDaemonLock() );
            Logger::Info( "Locked {$this->name}");
            
            return true;
        }
        
        
        /**
         * Unlock
         *
         */
        public function Unlock() {
            $lock = $this->CheckLock();
            if ( !empty( $lock ) ) {
                DaemonLockFactory::Delete( $lock );
            }
            
            return true;
        }
        
        
        /**
         * Check Lock
         *
         * @return DaemonLock
         */
        public function CheckLock() {
            $dl = $this->GetDaemonLock();
            
            $lock = DaemonLockFactory::GetOne( 
                array(
                    "packageName"   => $dl->packageName
                    , "methodName"  => $dl->methodName
                    , "title"       => $dl->title
                )
            );
            
            return $lock;
        }
        
        
        
        /**
         * Run Daemon
         *
         */
        public function Run() {
            if ( !$this->CanRun() ) {
                Logger::Info( "{$this->name} couldn't be run.");
                return false;
            }
            
            set_time_limit( 0 );
            
            if ( !$this->Lock() ) {
                Logger::Warning( "Failed to lock {$this->name}");
                return false;
            }
            
            try {
                Package::Load( $this->package );
                call_user_func_array( $this->method, array( $this->params ) );
            } catch ( Exception $e ) {
                Logger::Error( "{$this->name}: exeption in {$e->getMessage()}" );
            }
            
            $this->Unlock();
            
            return true;
        }
    }
?><?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * DaemonLock
     */
    class DaemonLock {

        /** @var int */
        public $daemonLockId;

        /** @var string */
        public $title;

        /** @var string */
        public $packageName;

        /** @var string */
        public $methodName;

        /** @var DateTimeWrapper */
        public $runAt;

        /** @var DateTimeWrapper */
        public $maxExecutionTime;

        /** @var bool */
        public $isActive;
    }
?><?php
    /**
     * DaemonLock Factory
     */
    class DaemonLockFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** DaemonLock instance mapping  */
        public static $mapping = array (
            'class'       => 'DaemonLock'
            , 'table'     => 'daemonLocks'
            , 'view'      => 'getDaemonLocks'
            , 'flags'     => array( 'WithoutTemplates' => 'WithoutTemplates' )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'daemonLockId' => array(
                    'name'          => 'daemonLockId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                    , 'addable'     => false
                )
                ,'title' => array(
                    'name'          => 'title'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'packageName' => array(
                    'name'          => 'packageName'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'methodName' => array(
                    'name'          => 'methodName'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'runAt' => array(
                    'name'          => 'runAt'
                    , 'type'        => TYPE_DATETIME
                    , 'updatable'   => false
                    , 'addable'     => false
                )
                ,'maxExecutionTime' => array(
                    'name'          => 'maxExecutionTime'
                    , 'type'        => TYPE_TIME
                    , 'nullable'    => 'No'
                )
                ,'isActive' => array(
                    'name'          => 'isActive'
                    , 'type'        => TYPE_BOOLEAN
                    , 'updatable'   => false
                    , 'addable'     => false
                ))
            , 'lists'     => array()
            , 'search'    => array()
        );
        
        /** @return array */
        public static function Validate( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Validate( $object, self::$mapping, $options, $connectionName );
        }

        /** @return array */
        public static function ValidateSearch( $search, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::ValidateSearch( $search, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool|array */
        public static function UpdateByMask( $object, $changes, $searchArray = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateByMask( $object, $changes, $searchArray, self::$mapping, $connectionName );
        }

        public static function SaveArray( $objects, $originalObjects = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::SaveArray( $objects, $originalObjects, self::$mapping, $connectionName );
        }

        public static function CanPages() {
            return BaseFactory::CanPages( self::$mapping );
        }        
        
        /** @return bool|array */
        public static function Add( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Add( $object, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool */
        public static function AddRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::AddRange( $objects, self::$mapping, $options, $connectionName );
        }

        /** @return bool|array */
        public static function Update( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Update( $object, self::$mapping, $options, $connectionName );
        }

        /** @return bool */
        public static function UpdateRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateRange( $objects, self::$mapping, $options, $connectionName );
        }

        public static function Count( $searchArray, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Count( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return DaemonLock[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return DaemonLock */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return DaemonLock */
        public static function GetOne( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetOne( $searchArray, self::$mapping, $options, $connectionName );
        }
        
        public static function GetCurrentId( $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetCurrentId( self::$mapping, $connectionName );
        }

        public static function Delete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Delete( $object, self::$mapping, $connectionName );
        }

        public static function DeleteByMask( $searchArray, $connectionName = self::DefaultConnection ) {
            return BaseFactory::DeleteByMask( $searchArray, self::$mapping, $connectionName );
        }

        public static function PhysicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::PhysicalDelete( $object, self::$mapping, $connectionName );
        }

        public static function LogicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::LogicalDelete( $object, self::$mapping, $connectionName );
        }

        /** @return DaemonLock */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
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
?><?php
    /**
     * MetaDetail
     */
    class MetaDetail {

        /** @var int */
        public $metaDetailId;

        /** @var string */
        public $url;

        /** @var string */
        public $pageTitle;

        /** @var string */
        public $metaKeywords;

        /** @var string */
        public $metaDescription;

        /** @var string */
        public $alt;

        /** @var bool */
        public $isInheritable;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?><?php
    /**
     * MetaDetail Factory
     */
    class MetaDetailFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** MetaDetail instance mapping  */
        public static $mapping = array (
            'class'       => 'MetaDetail'
            , 'table'     => 'metaDetails'
            , 'view'      => 'getMetaDetails'
            , 'flags'     => array( 'CanPages' => 'CanPages', 'CanCache' => 'CanCache' )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'metaDetailId' => array(
                    'name'          => 'metaDetailId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'url' => array(
                    'name'          => 'url'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'pageTitle' => array(
                    'name'          => 'pageTitle'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'searchType'  => SEARCHTYPE_LIKE
                )
                ,'metaKeywords' => array(
                    'name'          => 'metaKeywords'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 1024
                    , 'searchType'  => SEARCHTYPE_LIKE
                )
                ,'metaDescription' => array(
                    'name'          => 'metaDescription'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 1024
                    , 'searchType'  => SEARCHTYPE_LIKE
                )
                ,'alt' => array(
                    'name'          => 'alt'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'searchType'  => SEARCHTYPE_LIKE
                )
                ,'isInheritable' => array(
                    'name'          => 'isInheritable'
                    , 'type'        => TYPE_BOOLEAN
                    , 'nullable'    => 'No'
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                ))
            , 'lists'     => array()
            , 'search'    => array(
                'startUrl' => array(
                    'name'         => 'url'
                    , 'type'       => TYPE_STRING
                    , 'searchType' => SEARCHTYPE_RIGHT_ILIKE
                )
                ,'page' => array(
                    'name'         => 'page'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 0
                )
                ,'pageSize' => array(
                    'name'         => 'pageSize'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 25
                ))
        );
        
        /** @return array */
        public static function Validate( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Validate( $object, self::$mapping, $options, $connectionName );
        }

        /** @return array */
        public static function ValidateSearch( $search, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::ValidateSearch( $search, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool|array */
        public static function UpdateByMask( $object, $changes, $searchArray = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateByMask( $object, $changes, $searchArray, self::$mapping, $connectionName );
        }

        public static function SaveArray( $objects, $originalObjects = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::SaveArray( $objects, $originalObjects, self::$mapping, $connectionName );
        }

        public static function CanPages() {
            return BaseFactory::CanPages( self::$mapping );
        }        
        
        /** @return bool|array */
        public static function Add( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Add( $object, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool */
        public static function AddRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::AddRange( $objects, self::$mapping, $options, $connectionName );
        }

        /** @return bool|array */
        public static function Update( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Update( $object, self::$mapping, $options, $connectionName );
        }

        /** @return bool */
        public static function UpdateRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateRange( $objects, self::$mapping, $options, $connectionName );
        }

        public static function Count( $searchArray, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Count( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return MetaDetail[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return MetaDetail */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return MetaDetail */
        public static function GetOne( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetOne( $searchArray, self::$mapping, $options, $connectionName );
        }
        
        public static function GetCurrentId( $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetCurrentId( self::$mapping, $connectionName );
        }

        public static function Delete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Delete( $object, self::$mapping, $connectionName );
        }

        public static function DeleteByMask( $searchArray, $connectionName = self::DefaultConnection ) {
            return BaseFactory::DeleteByMask( $searchArray, self::$mapping, $connectionName );
        }

        public static function PhysicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::PhysicalDelete( $object, self::$mapping, $connectionName );
        }

        public static function LogicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::LogicalDelete( $object, self::$mapping, $connectionName );
        }

        /** @return MetaDetail */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
    /**
     * Navigation
     *
     * @package Panda
     * @subpackage Common
     */
    class Navigation {

        /** @var int */
        public $navigationId;

        /** @var string */
        public $title;

        /** @var int */
        public $orderNumber;

        /** @var int */
        public $navigationTypeId;

        /** @var NavigationType */
        public $navigationType;

        /** @var int */
        public $staticPageId;

        /** @var StaticPage */
        public $staticPage;

        /** @var string */
        public $url;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status = null;

        public function getLink() {
            if (!empty( $this->staticPageId ) && !empty( $this->staticPage->url ) ) {
                return Site::GetWebPath($this->staticPage->url);
            } else  if (!empty( $this->url)) {
                if ( mb_strpos($this->url, 'http') === 0 ) {
                    return $this->url;
                } else {
                    return Site::GetWebPath($this->url);
                }
            } else {
                return '/404';
            }
        }
    }
?><?php
    /**
     * Navigation Factory
     */
    class NavigationFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** Navigation instance mapping  */
        public static $mapping = array (
            'class'       => 'Navigation'
            , 'table'     => 'navigations'
            , 'view'      => 'getNavigations'
            , 'flags'     => array( 'CanCache' => 'CanCache', 'IsLocked' => 'IsLocked' )
            , 'cacheDeps' => array( 'navigationTypes', 'staticPages' )
            , 'fields'    => array(
                'navigationId' => array(
                    'name'          => 'navigationId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'title' => array(
                    'name'          => 'title'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                )
                ,'orderNumber' => array(
                    'name'          => 'orderNumber'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'No'
                )
                ,'navigationTypeId' => array(
                    'name'          => 'navigationTypeId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'NavigationType'
                )
                ,'staticPageId' => array(
                    'name'          => 'staticPageId'
                    , 'type'        => TYPE_INTEGER
                    , 'foreignKey'  => 'StaticPage'
                )
                ,'url' => array(
                    'name'          => 'url'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                )
                ,'nodes' => array(
                    'name'          => 'nodes'
                    , 'type'        => TYPE_ARRAY
                    , 'updatable'   => false
                    , 'addable'     => false
                ))
            , 'lists'     => array()
            , 'search'    => array(
                'navigationType.alias' => array(
                    'name'         => 'navigationType.alias'
                    , 'type'       => TYPE_STRING
                ))
        );
        
        /** @return array */
        public static function Validate( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Validate( $object, self::$mapping, $options, $connectionName );
        }

        /** @return array */
        public static function ValidateSearch( $search, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::ValidateSearch( $search, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool|array */
        public static function UpdateByMask( $object, $changes, $searchArray = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateByMask( $object, $changes, $searchArray, self::$mapping, $connectionName );
        }

        public static function SaveArray( $objects, $originalObjects = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::SaveArray( $objects, $originalObjects, self::$mapping, $connectionName );
        }

        public static function CanPages() {
            return BaseFactory::CanPages( self::$mapping );
        }        
        
        /** @return bool|array */
        public static function Add( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Add( $object, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool */
        public static function AddRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::AddRange( $objects, self::$mapping, $options, $connectionName );
        }

        /** @return bool|array */
        public static function Update( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Update( $object, self::$mapping, $options, $connectionName );
        }

        /** @return bool */
        public static function UpdateRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateRange( $objects, self::$mapping, $options, $connectionName );
        }

        public static function Count( $searchArray, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Count( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return Navigation[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return Navigation */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return Navigation */
        public static function GetOne( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetOne( $searchArray, self::$mapping, $options, $connectionName );
        }
        
        public static function GetCurrentId( $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetCurrentId( self::$mapping, $connectionName );
        }

        public static function Delete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Delete( $object, self::$mapping, $connectionName );
        }

        public static function DeleteByMask( $searchArray, $connectionName = self::DefaultConnection ) {
            return BaseFactory::DeleteByMask( $searchArray, self::$mapping, $connectionName );
        }

        public static function PhysicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::PhysicalDelete( $object, self::$mapping, $connectionName );
        }

        public static function LogicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::LogicalDelete( $object, self::$mapping, $connectionName );
        }

        /** @return Navigation */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
    /**
     * NavigationType
     */
    class NavigationType {

        /** @var int */
        public $navigationTypeId;

        /** @var string */
        public $title;

        /** @var string */
        public $alias;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?><?php
    /**
     * NavigationType Factory
     */
    class NavigationTypeFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** NavigationType instance mapping  */
        public static $mapping = array (
            'class'       => 'NavigationType'
            , 'table'     => 'navigationTypes'
            , 'view'      => 'getNavigationTypes'
            , 'flags'     => array( 'CanCache' => 'CanCache' )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'navigationTypeId' => array(
                    'name'          => 'navigationTypeId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'title' => array(
                    'name'          => 'title'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'nullable'    => 'CheckEmpty'
                    , 'searchType'  => SEARCHTYPE_ILIKE
                )
                ,'alias' => array(
                    'name'          => 'alias'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 32
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                ))
            , 'lists'     => array()
            , 'search'    => array(
                '_navigationTypeId' => array(
                    'name'         => 'navigationTypeId'
                    , 'type'       => TYPE_INTEGER
                    , 'searchType' => SEARCHTYPE_ARRAY
                ))
        );
        
        /** @return array */
        public static function Validate( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Validate( $object, self::$mapping, $options, $connectionName );
        }

        /** @return array */
        public static function ValidateSearch( $search, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::ValidateSearch( $search, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool|array */
        public static function UpdateByMask( $object, $changes, $searchArray = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateByMask( $object, $changes, $searchArray, self::$mapping, $connectionName );
        }

        public static function SaveArray( $objects, $originalObjects = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::SaveArray( $objects, $originalObjects, self::$mapping, $connectionName );
        }

        public static function CanPages() {
            return BaseFactory::CanPages( self::$mapping );
        }        
        
        /** @return bool|array */
        public static function Add( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Add( $object, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool */
        public static function AddRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::AddRange( $objects, self::$mapping, $options, $connectionName );
        }

        /** @return bool|array */
        public static function Update( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Update( $object, self::$mapping, $options, $connectionName );
        }

        /** @return bool */
        public static function UpdateRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateRange( $objects, self::$mapping, $options, $connectionName );
        }

        public static function Count( $searchArray, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Count( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return NavigationType[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return NavigationType */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return NavigationType */
        public static function GetOne( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetOne( $searchArray, self::$mapping, $options, $connectionName );
        }
        
        public static function GetCurrentId( $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetCurrentId( self::$mapping, $connectionName );
        }

        public static function Delete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Delete( $object, self::$mapping, $connectionName );
        }

        public static function DeleteByMask( $searchArray, $connectionName = self::DefaultConnection ) {
            return BaseFactory::DeleteByMask( $searchArray, self::$mapping, $connectionName );
        }

        public static function PhysicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::PhysicalDelete( $object, self::$mapping, $connectionName );
        }

        public static function LogicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::LogicalDelete( $object, self::$mapping, $connectionName );
        }

        /** @return NavigationType */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
    /**
     * SiteParam
     */
    class SiteParam {

        /** @var int */
        public $siteParamId;

        /** @var string */
        public $alias;

        /** @var string */
        public $value;

        /** @var string */
        public $description;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?><?php
    /**
     * SiteParam Factory
     */
    class SiteParamFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** SiteParam instance mapping  */
        public static $mapping = array (
            'class'       => 'SiteParam'
            , 'table'     => 'siteParams'
            , 'view'      => 'getSiteParams'
            , 'flags'     => array( 'CanPages' => 'CanPages', 'CanCache' => 'CanCache' )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'siteParamId' => array(
                    'name'          => 'siteParamId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'alias' => array(
                    'name'          => 'alias'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 32
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'value' => array(
                    'name'          => 'value'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'description' => array(
                    'name'          => 'description'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                ))
            , 'lists'     => array()
            , 'search'    => array(
                '_alias' => array(
                    'name'         => 'alias'
                    , 'type'       => TYPE_STRING
                    , 'searchType' => SEARCHTYPE_ARRAY
                )
                ,'page' => array(
                    'name'         => 'page'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 0
                )
                ,'pageSize' => array(
                    'name'         => 'pageSize'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 25
                ))
        );
        
        /** @return array */
        public static function Validate( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Validate( $object, self::$mapping, $options, $connectionName );
        }

        /** @return array */
        public static function ValidateSearch( $search, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::ValidateSearch( $search, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool|array */
        public static function UpdateByMask( $object, $changes, $searchArray = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateByMask( $object, $changes, $searchArray, self::$mapping, $connectionName );
        }

        public static function SaveArray( $objects, $originalObjects = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::SaveArray( $objects, $originalObjects, self::$mapping, $connectionName );
        }

        public static function CanPages() {
            return BaseFactory::CanPages( self::$mapping );
        }        
        
        /** @return bool|array */
        public static function Add( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Add( $object, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool */
        public static function AddRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::AddRange( $objects, self::$mapping, $options, $connectionName );
        }

        /** @return bool|array */
        public static function Update( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Update( $object, self::$mapping, $options, $connectionName );
        }

        /** @return bool */
        public static function UpdateRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateRange( $objects, self::$mapping, $options, $connectionName );
        }

        public static function Count( $searchArray, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Count( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return SiteParam[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return SiteParam */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return SiteParam */
        public static function GetOne( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetOne( $searchArray, self::$mapping, $options, $connectionName );
        }
        
        public static function GetCurrentId( $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetCurrentId( self::$mapping, $connectionName );
        }

        public static function Delete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Delete( $object, self::$mapping, $connectionName );
        }

        public static function DeleteByMask( $searchArray, $connectionName = self::DefaultConnection ) {
            return BaseFactory::DeleteByMask( $searchArray, self::$mapping, $connectionName );
        }

        public static function PhysicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::PhysicalDelete( $object, self::$mapping, $connectionName );
        }

        public static function LogicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::LogicalDelete( $object, self::$mapping, $connectionName );
        }

        /** @return SiteParam */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
    /**
     * StaticPage
     */
    class StaticPage {

        /** @var int */
        public $staticPageId;

        /** @var string */
        public $title;

        /** @var string */
        public $url;

        /** @var string */
        public $content;

        /** @var string */
        public $pageTitle;

        /** @var string */
        public $metaKeywords;

        /** @var string */
        public $metaDescription;

        /** @var int */
        public $orderNumber;

        /** @var int */
        public $parentStaticPageId;

        /** @var StaticPage */
        public $parentStaticPage;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;

        /** @var array */
        public $nodes;
    }
?><?php
    /**
     * StaticPage Factory
     */
    class StaticPageFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** StaticPage instance mapping  */
        public static $mapping = array (
            'class'       => 'StaticPage'
            , 'table'     => 'staticPages'
            , 'view'      => 'getStaticPages'
            , 'flags'     => array( 'CanPages' => 'CanPages', 'CanCache' => 'CanCache' )
            , 'cacheDeps' => array( 'staticPages' )
            , 'fields'    => array(
                'staticPageId' => array(
                    'name'          => 'staticPageId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'title' => array(
                    'name'          => 'title'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'nullable'    => 'CheckEmpty'
                    , 'searchType'  => SEARCHTYPE_ILIKE
                )
                ,'url' => array(
                    'name'          => 'url'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'content' => array(
                    'name'          => 'content'
                    , 'type'        => TYPE_STRING
                    , 'searchType'  => SEARCHTYPE_ILIKE
                )
                ,'pageTitle' => array(
                    'name'          => 'pageTitle'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                )
                ,'metaKeywords' => array(
                    'name'          => 'metaKeywords'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 2048
                )
                ,'metaDescription' => array(
                    'name'          => 'metaDescription'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 2048
                )
                ,'orderNumber' => array(
                    'name'          => 'orderNumber'
                    , 'type'        => TYPE_INTEGER
                )
                ,'parentStaticPageId' => array(
                    'name'          => 'parentStaticPageId'
                    , 'type'        => TYPE_INTEGER
                    , 'foreignKey'  => 'StaticPage'
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                )
                ,'nodes' => array(
                    'name'          => 'nodes'
                    , 'type'        => TYPE_ARRAY
                    , 'updatable'   => false
                    , 'addable'     => false
                ))
            , 'lists'     => array()
            , 'search'    => array(
                'page' => array(
                    'name'         => 'page'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 0
                )
                ,'pageSize' => array(
                    'name'         => 'pageSize'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 25
                ))
        );
        
        /** @return array */
        public static function Validate( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Validate( $object, self::$mapping, $options, $connectionName );
        }

        /** @return array */
        public static function ValidateSearch( $search, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::ValidateSearch( $search, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool|array */
        public static function UpdateByMask( $object, $changes, $searchArray = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateByMask( $object, $changes, $searchArray, self::$mapping, $connectionName );
        }

        public static function SaveArray( $objects, $originalObjects = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::SaveArray( $objects, $originalObjects, self::$mapping, $connectionName );
        }

        public static function CanPages() {
            return BaseFactory::CanPages( self::$mapping );
        }        
        
        /** @return bool|array */
        public static function Add( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Add( $object, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool */
        public static function AddRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::AddRange( $objects, self::$mapping, $options, $connectionName );
        }

        /** @return bool|array */
        public static function Update( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Update( $object, self::$mapping, $options, $connectionName );
        }

        /** @return bool */
        public static function UpdateRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateRange( $objects, self::$mapping, $options, $connectionName );
        }

        public static function Count( $searchArray, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Count( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return StaticPage[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return StaticPage */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return StaticPage */
        public static function GetOne( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetOne( $searchArray, self::$mapping, $options, $connectionName );
        }
        
        public static function GetCurrentId( $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetCurrentId( self::$mapping, $connectionName );
        }

        public static function Delete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Delete( $object, self::$mapping, $connectionName );
        }

        public static function DeleteByMask( $searchArray, $connectionName = self::DefaultConnection ) {
            return BaseFactory::DeleteByMask( $searchArray, self::$mapping, $connectionName );
        }

        public static function PhysicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::PhysicalDelete( $object, self::$mapping, $connectionName );
        }

        public static function LogicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::LogicalDelete( $object, self::$mapping, $connectionName );
        }

        /** @return StaticPage */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
    /**
     * Static Page Helper
     */
    class StaticPageHelper {
        /**
    	 * Form Pages
    	 */
    	public static function FormSelect( $controlName, $pages, $value, $showAll = true ) {
			$xhtml = sprintf( '<select name="%s"><option></option>'
				, $controlName
			);

            $tree = StaticPageUtility::Collapse($pages);
			self::getSubPages( $tree, 0, $value, $showAll, $xhtml );

			$xhtml .= "</select>";
			return $xhtml;
    	}

        /*
    	 * Get Sub Pages
    	 */
		private static function getSubPages($pages, $level, $selectedId, $showAll, &$xhtml) {
			foreach ($pages as $page ) {

				$xhtml .= sprintf( '<option value="%s" %s> %s %s</option>'
					, $page->staticPageId
					, ($selectedId == $page->staticPageId) ? 'selected="selected"' : ""
					, ($level == 0) ? "" : "&nbsp;".  str_repeat( "--", $level ) . "|"
					, $page->title
				);

				if (!empty($page->nodes) && ($showAll || $selectedId != $page->staticPageId)) {
					self::getSubPages($page->nodes, $level + 1, $selectedId, $showAll, $xhtml );
				}
			}
		}
    }
?><?php
    /**
     * Static Page Utility
     */
    class StaticPageUtility {

        /**
         * Get All Static Pages (staticPageId, title, parentStaticPageId )
         * 
         * @return array
         */
        public static function GetData(){
            $conn = ConnectionFactory::Get();
            $columns  = array( "staticPageId", "title", "parentStaticPageId");
            for ( $i = 0; $i < count( $columns ); $i ++ ) {
                $columns[$i] = $conn->quote( $columns[$i] );
            }
            
            return StaticPageFactory::Get( null,
                array( BaseFactory::WithoutPages => true, BaseFactory::WithColumns => implode( ",", $columns ))
            );
        }


        /**
         * Get Collapsed Static Pages (staticPageId, title, parentStaticPageId )
         * @return array collapsed static pages
         */
        public static function GetCollapsedData() {
            return self::Collapse(self::GetData());
        }


        /**
         * Collapse Static Pages to Nodes
         * @param  $pages
         * @return array
         */
        public static function Collapse( $pages ) {
            $tree = array();
            foreach ( $pages as $page ) {
                $id  = $page->staticPageId;
                $pid = $page->parentStaticPageId;
                if ( is_null($page->nodes) ) {
                    $page->nodes = array();
                }

                if ( empty( $pid ) ) {
                    $tree[$id] = $page;
                } else {
                    $pages[$pid]->nodes[$id] = $page;
                }
            }
            return $tree;
        }
    }
?><?php
    /**
     * Status
     */
    class Status {

        /** @var int */
        public $statusId;

        /** @var string */
        public $title;

        /** @var string */
        public $alias;
    }
?><?php
    /**
     * Status Factory
     */
    class StatusFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** Status instance mapping  */
        public static $mapping = array (
            'class'       => 'Status'
            , 'table'     => 'statuses'
            , 'view'      => 'getStatuses'
            , 'flags'     => array( 'WithoutTemplates' => 'WithoutTemplates' )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'title' => array(
                    'name'          => 'title'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'alias' => array(
                    'name'          => 'alias'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 64
                    , 'nullable'    => 'CheckEmpty'
                ))
            , 'lists'     => array()
            , 'search'    => array()
        );
        
        /** @return array */
        public static function Validate( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Validate( $object, self::$mapping, $options, $connectionName );
        }

        /** @return array */
        public static function ValidateSearch( $search, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::ValidateSearch( $search, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool|array */
        public static function UpdateByMask( $object, $changes, $searchArray = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateByMask( $object, $changes, $searchArray, self::$mapping, $connectionName );
        }

        public static function SaveArray( $objects, $originalObjects = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::SaveArray( $objects, $originalObjects, self::$mapping, $connectionName );
        }

        public static function CanPages() {
            return BaseFactory::CanPages( self::$mapping );
        }        
        
        /** @return bool|array */
        public static function Add( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Add( $object, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool */
        public static function AddRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::AddRange( $objects, self::$mapping, $options, $connectionName );
        }

        /** @return bool|array */
        public static function Update( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Update( $object, self::$mapping, $options, $connectionName );
        }

        /** @return bool */
        public static function UpdateRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateRange( $objects, self::$mapping, $options, $connectionName );
        }

        public static function Count( $searchArray, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Count( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return Status[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return Status */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return Status */
        public static function GetOne( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetOne( $searchArray, self::$mapping, $options, $connectionName );
        }
        
        public static function GetCurrentId( $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetCurrentId( self::$mapping, $connectionName );
        }

        public static function Delete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Delete( $object, self::$mapping, $connectionName );
        }

        public static function DeleteByMask( $searchArray, $connectionName = self::DefaultConnection ) {
            return BaseFactory::DeleteByMask( $searchArray, self::$mapping, $connectionName );
        }

        public static function PhysicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::PhysicalDelete( $object, self::$mapping, $connectionName );
        }

        public static function LogicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::LogicalDelete( $object, self::$mapping, $connectionName );
        }

        /** @return Status */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
    /**
     * Status Utility
     *
     */
    class StatusUtility {

        const Queued = 4;
        const Finished = 5;

        /**
         * Common Statuses
         *
         * @var array
         */
        public static $Common = array(
            "en" => array(
                1   => "Enabled"
                , 2 => "Disabled"
            )
            , "ru" => array(
                1   => "Опубликован"
                , 2 => "Не опубликован"
			)
        );

        /**
         * Common Statuses
         *
         * @var array
         */
        public static $Queue = array(
            "en" => array(
                1   => "Waiting"
                , 4 => "Queued"
                , 5 => "Finished"
            )
            , "ru" => array(
                1   => "В ожидании"
                , 4 => "Обрабатывается"
                , 5 => "Отправлена"
            )
        );
        
        /**
         * Get Status Template
         *
         * @param int $statusId
         * @return string
         */
        public static function GetStatusTemplate( $statusId ) {
            $status = self::$Common[LocaleLoader::$CurrentLanguage][$statusId];

            switch ($statusId) {
            	case 1:
            	    return sprintf( '<span class="status green" title="%s">%s</span>', $status, $status);
                case 5:
            	    return sprintf( '<span class="status blue" title="%s">%s</span>', $status, $status);
            	default:
            	    return sprintf( '<span class="status" title="%s">%s</span>', $status, $status);
            }
        }

        /**
         * Get Queue Status Template
         *
         * @param int $statusId
         * @return string
         */
        public static function GetQueueStatusTemplate( $statusId ) {
            $status = self::$Queue[LocaleLoader::$CurrentLanguage][$statusId];

            switch ($statusId) {
                case 1:
                    return sprintf( '<span class="status" title="%s">%s</span>', $status, $status);
                case 4:
                    return sprintf( '<span class="status red" title="%s">%s</span>', $status, $status);
                case 5:
                    return sprintf( '<span class="status green" title="%s">%s</span>', $status, $status);
                default:
                    return sprintf( '<span class="status" title="%s">%s</span>', $status, $status);
            }
        }

        /**
         * Get Bool Template
         *
         * @param $bool bool  The bool Value
         * @return string
         */
        public static function GetBoolTemplate( $bool = false ) {
            if ( $bool ) {
                return sprintf( '<span class="status green" title="%s">%s</span>', "Да", "Да");
            } else {
                return sprintf( '<span class="status" title="%s">%s</span>', "Нет", "Нет");
            }
        }        
    }
?><?php
    /**
     * User
     */
    class User {

        /** @var int */
        public $userId;

        /** @var string */
        public $login;

        /** @var string */
        public $password;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?><?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( '%project%.Common' );

    /**
     * User Factory
     *
     * @package %project%
     * @subpackage Common
     */
    class UserFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** User instance mapping  */
        public static $mapping = array (
            'class'       => 'User'
            , 'table'     => 'users'
            , 'view'      => 'getUsers'
            , 'flags'     => array( 'CanPages' => 'CanPages', 'CanCache' => 'CanCache' )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'userId' => array(
                    'name'          => 'userId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'login' => array(
                    'name'          => 'login'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 64
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'password' => array(
                    'name'          => 'password'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 64
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                ))
            , 'lists'     => array()
            , 'search'    => array(
                'page' => array(
                    'name'         => 'page'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 0
                )
                ,'pageSize' => array(
                    'name'         => 'pageSize'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 25
                ))
        );
        
        /** @return array */
        public static function Validate( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Validate( $object, self::$mapping, $options, $connectionName );
        }

        /** @return array */
        public static function ValidateSearch( $search, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::ValidateSearch( $search, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool|array */
        public static function UpdateByMask( $object, $changes, $searchArray = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateByMask( $object, $changes, $searchArray, self::$mapping, $connectionName );
        }

        public static function SaveArray( $objects, $originalObjects = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::SaveArray( $objects, $originalObjects, self::$mapping, $connectionName );
        }

        public static function CanPages() {
            return BaseFactory::CanPages( self::$mapping );
        }        
        
        /** @return bool|array */
        public static function Add( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Add( $object, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool */
        public static function AddRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::AddRange( $objects, self::$mapping, $options, $connectionName );
        }

        /** @return bool|array */
        public static function Update( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Update( $object, self::$mapping, $options, $connectionName );
        }

        /** @return bool */
        public static function UpdateRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateRange( $objects, self::$mapping, $options, $connectionName );
        }

        public static function Count( $searchArray, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Count( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return User[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return User */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return User */
        public static function GetOne( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetOne( $searchArray, self::$mapping, $options, $connectionName );
        }
        
        public static function GetCurrentId( $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetCurrentId( self::$mapping, $connectionName );
        }

        public static function Delete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Delete( $object, self::$mapping, $connectionName );
        }

        public static function DeleteByMask( $searchArray, $connectionName = self::DefaultConnection ) {
            return BaseFactory::DeleteByMask( $searchArray, self::$mapping, $connectionName );
        }

        public static function PhysicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::PhysicalDelete( $object, self::$mapping, $connectionName );
        }

        public static function LogicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::LogicalDelete( $object, self::$mapping, $connectionName );
        }

        /** @return User */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?>