<?php
    /**
     * Action
     *
     * @package Eaze
     * @subpackage Site
     * @author sergeyfast
     */
    class Action {

        /**
         * Package Name
         *
         * @var string
         */
        public $Package;

        /**
         * Action Name
         *
         * @var string
         */
        public $Name;

        /**
         * Full Name (Package.Name)
         *
         * @var string
         */
        public $FullName;

        /**
         * Action FilePath
         *
         * @var string
         */
        public $Path;

        /**
         * Redirects
         *
         * @var array
         */
        public $Redirects;

        /**
         * Request, Response parameters
         *
         * @var array
         */
        public $Parameters = array();


        /**
         * Ready State
         *
         * @var bool
         */
        private $ready = false;

        /**
         * Cached DOMDocuments
         *
         * @var DOMDocument[]
         */
        private static $docs = array();

        /**
         * Cached DOMXPaths
         *
         * @var DOMXPath[]
         */
        private static $xpath = array();


        /**
         * Constructor
         *
         * @param string $package  package name
         * @param string $name     action name
         */
        public function __construct( $package, $name ) {
            $this->Package  = $package;
            $this->Name     = $name;
            $this->FullName = sprintf( '%s.%s', $this->Package, $this->Name );

            if ( empty( self::$docs[$this->Package] ) ) {
                $filePath = sprintf( '%s/%s/%s.xml', __LIB__, $this->Package, $this->Package );

                if ( is_file( $filePath ) ) {
                    $doc = new DOMDocument();
                    $doc->preserveWhiteSpace = false;

                    if ( !$doc->load( $filePath ) ) {
                        Logger::Error( 'Error while loading %s', $filePath );
                        return;
                    }

                    self::$docs[$this->Package]  = $doc;
                    self::$xpath[$this->Package] = new DOMXPath( $doc );
                    Logger::Debug( 'Loaded %s', $this->FullName );
                } else {
                    Logger::Warning( "Couldn't open package file %s", $filePath );
                    return;
                }
            }

            $this->initializeAction();
        }


        /**
         * Process Action
         *
         * @return string  the redirect name
         */
        public function Process() {
            if ( $this->ready ) {
                $actionName     = basename( $this->Path, '.php' );
                $actionInstance = new $actionName();

                foreach ( $this->Parameters as $key => $paramGroup ) {
                    foreach ( $paramGroup as $paramKey => $paramValue ) {
                        switch ( $key ) {
                            case 'request':
                                Request::setParameter( $paramKey, $paramValue );
                                break;
                            case 'response':
                                Response::setParameter( $paramKey, $paramValue );
                                break;
                            case 'session':
                                Session::setParameter( $paramKey, $paramValue );
                                break;
                        }
                    }
                }

                return $actionInstance->execute();
            }

            Logger::Warning( 'Action %s is not ready', $this->FullName );
            return null;
        }


        /**
         * Initialize Action and require_once <action-name>.php
         */
        private function initializeAction() {
            if ( $this->ready ) {
                return;
            }

            Package::Load( $this->Package );

            if ( empty( self::$xpath[$this->Package] ) ) {
                Logger::Error( "Xpath doesn't exists %s", $this->Package );
                return;
            }

            $action = self::$xpath[$this->Package]->evaluate( sprintf( PageManagerConstants::defaultActionQuery, $this->Name ) )->item( 0 );
            if ( empty( $action ) ) {
                Logger::Error( "Action %s wasn't found", $this->FullName );
                return;
            }

            // GET Action Data
            foreach ( $action->childNodes as $node ) {
                if ( $node instanceof DOMComment ) {
                    continue;
                }

                $nodeName = $node->nodeName;
                switch ( $nodeName ) {
                    case 'path':
                        $this->Path = sprintf( '%s/%s/actions/%s.php', __LIB__, $this->Package, $node->nodeValue );
                        if ( is_file( $this->Path ) ) {
                            /** @noinspection PhpIncludeInspection */
                            require_once( $this->Path );
                        } else {
                            Logger::Error( "File %s doesn't exist", $this->Path );
                            return;
                        }

                        break;
                    case 'parameters':
                        foreach ( $node->childNodes as $pNode ) {
                            if ( $pNode instanceof DOMComment ) {
                                continue;
                            }

                            switch ( $pNode->nodeName ) {
                                case 'session':
                                case 'request':
                                case 'response':
                                    foreach ( $pNode->childNodes as $paramNode ) {
                                        if ( $paramNode instanceof DOMComment ) {
                                            continue;
                                        }

                                        $paramName = $paramNode->getAttribute( 'name' );
                                        $this->Parameters[$pNode->nodeName][$paramName]=   eval( 'return ' . $paramNode->nodeValue . ';' );
                                    }
                                    break;
                                default:
                                    Logger::Warning( 'Unknown  %s in %s xml|parameters', $pNode->nodeName, $this->FullName );
                                    break;
                            }
                        }
                        break;
                    case 'redirects':
                        foreach ( $node->childNodes as $rNode ) {
                            if ( $rNode instanceof DOMComment ) {
                                continue;
                            }

                            $this->Redirects[$rNode->getAttribute( 'name' )] = array(
                                'path'   => $rNode->getAttribute( 'path' )
                                , 'host' => $rNode->getAttribute( 'host' )
                            );
                        }
                        break;
                    default:
                        Logger::Warning( 'Unknown %s in %s .xml', $nodeName, $this->FullName );
                        break;
                }
            }

            // Load Default Path if needed
            if ( empty( $this->Path ) ) {
                $this->Path = sprintf( '%s/%s/actions/%s.php', __LIB__, $this->Package, $this->Name );
                if ( is_file( $this->Path ) ) {
                    Logger::Debug( 'Loading action %s', $this->FullName );
                    /** @noinspection PhpIncludeInspection */
                    require_once( $this->Path );
                } else {
                    Logger::Error( "File %s doesn't exist", $this->Path );
                    return;
                }
            }

            $this->ready = true;
        }
    }

?><?php
   /**
    * Http Host
    *
    * @package Eaze
    * @subpackage Site
    * @author sergeyfast
    */
    class Host {

        /**
         * Protocol scheme
         *
         * @var string
         */
        private $protocol = 'http';

        /**
         * Hostname
         *
         * @var string
         */
        private $hostname;

        /**
         * localname
         *
         * @var string
         */
        private $localname;

        /**
         * Port
         *
         * @var integer
         */
        private $port = 80;

        /**
         * Webroot
         *
         * @var string
         */
        private $webroot;

        /**
         * Default
         *
         * @var boolean
         */
        private $default = false;

        /**
         * Current host
         *
         * @var Host
         */
        private static $currentHost;

        /**
         * Current Host MD5 Key
         *
         * @var string
         */
        private static $currentHostKey;

        /**
         * Get Path String
         *
         * @var string
         */
        private $pathString;


        /**
         * Overrided Paths
         *
         * @var array
         */
        public $Paths;


        /**
         * Constructor
         *
         * @param string   $protocol
         * @param string   $hostname
         * @param integer  $port
         * @param string   $webroot
         * @param boolean  $default
         * @param string   $localname
         */
        public function __construct( $protocol = null
                                    , $hostname = null
                                    , $port = null
                                    , $webroot = null
                                    , $default = false
                                    , $localname = null ) {
            $this->hostname  = $hostname;
            $this->default   = $default;
            $this->localname = $localname;

            if ( !is_null( $protocol ) ) {
                $this->protocol = $protocol;
            }

            if ( !is_null( $webroot ) ) {
                $this->webroot  = $webroot;
            }

            if ( !is_null( $port ) ) {
                $this->port     = $port;
            }

            $this->setPathString();
        }


        /**
         * To String
         *
         * @return string
         */
        public function __toString() {
            return sprintf( '%s://%s:%s/%s', $this->protocol, $this->hostname, $this->port, $this->webroot );
        }


        /**
         * Update Path String
         */
        public function setPathString() {
            if (( $this->protocol == 'http' && $this->port == '80' )
                || ( $this->protocol == 'https' && $this->port == '80' )
                || ( $this->protocol == 'https' && $this->port == '443' )) {
                $this->pathString = sprintf( '%s://%s%s', $this->protocol, $this->hostname,  (true == empty( $this->webroot)) ? '' : '/' . $this->webroot );
            } else {
                $this->pathString = sprintf( '%s://%s:%s%s', $this->protocol, $this->hostname, $this->port, (true == empty( $this->webroot)) ? '' : '/' . $this->webroot  );
            }
        }


        /**
         * Sets current host to default
         * @param $bDefault
         */
        public function SetDefault( $bDefault ) {
            $this->default = $bDefault;
        }


        /**
         * Sets local name
         *
         * @param string $localname
         */
        public function SetLocalname( $localname ) {
            $this->localname = $localname;
        }


        /**
         * Set Paths
         *
         * @param DOMNodeList $paths
         */
        public function SetPaths( DOMNodeList $paths ) {
            foreach ( $paths as $path ) {
                /** @var DOMElement $path  */
                $this->Paths[$path->getAttribute('name'). '://'] = $path->getAttribute('value');
            }
        }


        /**
         * Get Current Host
         *
         * @static
         * @return Host
         */
        public static function GetCurrentHost() {
            if ( !empty( self::$currentHost ) ) {
                return self::$currentHost;
            }

            // Get protocol
            $protocol = 'http';
            $hostname = '';
            $port     = isset( $_SERVER['SERVER_PORT'] ) ? $_SERVER['SERVER_PORT'] : 80;
            $webroot  = Host::GetCurrentWebroot();

            if( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') )
            {
                $protocol = 'https';
            }

            if( isset( $_SERVER['REQUEST_URI'] ) ) {
                $script_name = $_SERVER['REQUEST_URI'];
            } else {
                $script_name = $_SERVER['PHP_SELF'];

                if( isset( $_SERVER['QUERY_STRING'] ) && $_SERVER['QUERY_STRING'] > ' ') {
                    $script_name .= '?' . $_SERVER['QUERY_STRING'];
                }
            }

            if ( isset( $_SERVER['HTTP_HOST'] ) ) {
                $hostname = $_SERVER['HTTP_HOST'];
                if ( strpos($hostname, ':') !== false ) {
                    $port = substr($hostname, strpos($hostname, ':')  + 1);
                }

            } else if ( isset( $_SERVER['SERVER_NAME'] ) ) {
                $hostname = $_SERVER['SERVER_NAME'];
            }


            if ( strpos($hostname, ':') !== false  ) {
                $hostname = substr($hostname, 0, strpos($hostname, ':') );
            }

            self::$currentHost    = new Host( $protocol, $hostname, $port, $webroot );
            self::$currentHostKey = md5( self::$currentHost->__toString() );

            return self::$currentHost;
        }



        /**
         * Get Current Host MD5 Key
         *
         * @return string
         */
        public static function GetCurrentKey() {
            if ( empty( self::$currentHostKey ) ) {
                self::GetCurrentHost();
            }

            return self::$currentHostKey;
        }

    	/**
    	 * Get Webroot
    	 *
    	 * @static
    	 * @return string
    	 */
    	public static function GetCurrentWebroot(){
	        $filename = basename($_SERVER['SCRIPT_FILENAME']);

	        if (basename($_SERVER['SCRIPT_NAME']) === $filename) {
                $baseUrl = $_SERVER['SCRIPT_NAME'];
            } elseif (basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) {
                $baseUrl = $_SERVER['ORIG_SCRIPT_NAME'];
            } elseif (basename($_SERVER['PHP_SELF']) === $filename) {
                $baseUrl = $_SERVER['PHP_SELF'];
            }

            if ( empty($baseUrl) ) {
                $basePath = '';
            } else {
                if (basename( $baseUrl ) === $filename) {
                    $basePath = dirname( $baseUrl );
                } else {
                    $basePath = $baseUrl;
                }

                $basePath = rtrim($basePath, '/');
                $basePath = ltrim($basePath, '/' );
                $basePath = ltrim($basePath, '\\' );
            }

    	    return $basePath;
    	}


    	/**
    	 * Get Hostname
    	 *
    	 * @return string
    	 */
    	public function GetHostname() {
    	    return $this->hostname;
    	}

    	/**
    	 * Get Port
    	 *
    	 * @return integer
    	 */
    	public function GetPort() {
    	    return $this->port;
    	}

    	/**
    	 * Get Protocol
    	 *
    	 * @return string
    	 */
    	public function GetProtocol() {
    	    return $this->protocol;
    	}


    	/**
    	 * Get Webroot
    	 *
    	 * @return string
    	 */
    	public function GetWebroot() {
    	    return $this->webroot;
    	}


     	/**
    	 * Get Default
    	 *
    	 * @return boolean
    	 */
    	public function GetDefault() {
    	    return $this->default;
    	}


 	    /**
    	 * Get Default
    	 *
    	 * @return boolean
    	 */
    	public function GetLocalname() {
    	    return $this->localname;
    	}


    	/**
    	 * Get PathString
    	 *
    	 * @return string
    	 */
    	public function GetPathString() {
    	    return $this->pathString;
    	}
    }
?><?php
    /**
     * IModule Interface
     */
    interface IModule {
        /**
         * Init Module
         *
         * @param DOMNodeList $params  the params node list
         * @static 
         */
        public static function Init( DOMNodeList $params );
    }
?><?php
    /**
     * Page
     * @package Eaze
     * @subpackage Eaze.Site
     */
    class Page {

        /**
         * Actions
         *
         * @var Action[]
         */
        public static $Actions = array();

        /**
         * Uri
         *
         * @var string
         */
        public static $Uri;

        /**
         * Template Path
         *
         * @var string
         */
        public static $TemplatePath;

        /**
         * RequestData from Regs
         *
         * @var array
         */
        public static $RequestData;


        /**
         * Constructor
         *
         * @param DOMElement  $page
         * @param array       $regs
         * @param DOMNodeList $virtualActions
         */
        public function __construct( DOMElement $page, array $regs, DOMNodeList $virtualActions ) {
            self::$RequestData = $regs;
            self::$Uri         = $page->getAttribute( 'uri' );

            self::setTemplate( $page );
            $actions = self::getActionsArray( $page, $virtualActions );

            // Form Packages
            $packages = array();
            foreach ( $actions as $action ) {
                if ( trim( $action ) == '' ) {
                    continue;
                }

                if ( preg_match( '#(.*\\..*)\\.(.*)#', $action, $regs ) ) {
                    $packages[$regs[1]][] = $regs[2];
                    self::$Actions[$action] = new Action( $regs[1], $regs[2] );

                    //Process Action
                    if ( !empty( self::$Actions[$action] ) ) {
                        $redirect = self::$Actions[$action]->Process();

                        // Check For Redirect
                        if ( !empty( $redirect )
                             && ( !empty( self::$Actions[$action]->Redirects[$redirect] ) ) )
                        {
                            Request::Commit();
                            Response::Redirect(
                                Site::GetWebPathEx(
                                    self::$Actions[$action]->Redirects[$redirect]['path']
                                    , self::$Actions[$action]->Redirects[$redirect]['host'] )
                            );
                        }
                    }
                } else {
                    Logger::Warning( 'Invalid action format: %s', $action );
                }
            }

            Request::Commit();

            // Process Template
            // simple include
            if ( ! empty( self::$TemplatePath ) ) {
                Template::Load( Site::GetRealPath( self::$TemplatePath ) );
            }
        }


        /**
         * Set Template Path
         *
         * @param DOMElement $page
         */
        private static function setTemplate( DOMElement $page ) {
            $template = '';
            $templateNode = $page->getElementsByTagName( 'template' )->item( 0 );
            if ( !empty( $templateNode ) ) {
                self::$TemplatePath = $templateNode->nodeValue;
            }
        }


        /**
         * Get Formatted Actions
         *
         * @param DOMElement $page
         * @param DOMNodeList $virtualActions
         * @return array
         */
        private static function getActionsArray( DOMElement $page, DOMNodeList $virtualActions ) {
            // Add virtual actions
            $vActionSearch = $vActionReplace = array();
            foreach ( $virtualActions as $vAction ) {
                $vActionSearch[]  = $vAction->getAttribute( 'name' );
                $vActionReplace[] = $vAction->nodeValue;
            }

            $boot        = $page->getAttribute( PageManagerConstants::xmlBoot );
            $shutdown    = $page->getAttribute( PageManagerConstants::xmlShutdown );
            $actionsNode = $page->getElementsByTagName( 'actions' )->item( 0 );
            $actions     = '';
            if ( !empty( $actionsNode ) ) {
                $actions = $actionsNode->nodeValue;
            }

            // Collect actions list
            $actionsList    = trim( sprintf( '%s,%s,%s', $boot, $actions, $shutdown ), ' , ' );
            if ( !empty( $vActionSearch ) ) {
                $actionsList = str_replace( $vActionSearch, $vActionReplace, $actionsList );
            }

            $actionsList    = str_replace( array( ' ', ',,' ), array( '', ',' ), $actionsList );
            $actionsArrList = explode( ',', $actionsList );

            return $actionsArrList;
        }
    }

?><?php
    define( 'CONFPATH_PAGES', 'etc/conf/pages.xml');

    class PageManagerConstants {
        const xmlPageGroupNode = 'pageGroup';
        const xmlPageNode = 'page';
        const xmlBoot = 'boot';
        const xmlShutdown = 'shutdown';
        const defaultPagesQuery = '//site[@name="%s" or contains(@names, "%s")]//page';
        const defaultVAQuery = '//site[@name="%s"]/hosts/host[@name="%s" or @name="*"]//action';
        const defaultPageCachePattern = 'pages_%s.xml';
        const defaultActionQuery = '//action[@name="%s"]';
    }


    class PageManager {

        /**
         * regexp from pages, matched by current uri
         * @var string
         */
        public static $CurrentPageUri;

        /**
         * Detect Page
         *
         * @param string $uri
         * @return void
         */
        public static function DetectPage( $uri = null ) {
            if ( empty($uri) ){
                $uri = Site::GetCurrentURI();
            }

            $doc = new DOMDocument();
            $doc->preserveWhiteSpace = false;

            if ( false == $doc->load( CacheManager::GetCachedXMLPath(
                    CONFPATH_PAGES, PageManagerConstants::defaultPageCachePattern, array( 'PageManager', 'CachePagesXML' )  ) )
                    ) {
                Logger::Error( 'Error while loading Pages.xml' );
                return null;
            }

            $xpath = new DOMXPath( $doc );
            $pages = $xpath->query( sprintf(PageManagerConstants::defaultPagesQuery, Site::$Name, Site::$Name ) );

            $initialized = false;

            $uri = urldecode( $uri );
            Logger::Checkpoint();
            foreach ( $pages as $page ) {
                $pageUri = Site::TranslatePathTemplate( $page->getAttribute('uri') );

                if ( preg_match( sprintf( '#^(%s)(\?(?:.*)|$)#i', $pageUri), $uri, $regs ) ) {
                    self::$CurrentPageUri = $pageUri;
                    $initialized = true;
                    array_shift( $regs ); array_pop( $regs );
                    break;
                }
            }

            Logger::Debug( 'page: %s', ( $initialized && !empty( $regs ) ? $regs[0] : 'not found' ) );

            if ( !$initialized ) {
                Response::HttpStatusCode( '404', 'Not Found' );
            } else {
                $virtualActions = $xpath->query( sprintf(PageManagerConstants::defaultVAQuery , Site::$Name, Host::GetCurrentHost()->GetLocalname()) );
                self::initializePage( $page, $regs, $virtualActions );
            }
        }


        /**
         * Initialize Page
         *
         * @param DOMElement $page
         * @param array $regs
         * @param DOMNodeList $virtualActions
         */
        private static function initializePage( DOMElement $page, array $regs, DOMNodeList $virtualActions ) {
            new Page( $page, $regs, $virtualActions );
        }


        /**
         * Cache Pages.xml
         *
         * @param DOMDocument $doc
         */
        public static function CachePagesXML( DOMDocument $doc ) {
            $sitesList = $doc->getElementsByTagName( 'site' );
            foreach ( $sitesList as $node )  {
                $pagesList = $node->getElementsByTagName( 'pages' )->item(0);

                foreach ( $pagesList->childNodes as $pageNode ){
                    if ( $pageNode instanceof DOMComment ) continue;

                    if ( $pageNode->nodeName == PageManagerConstants::xmlPageNode ) {
                        self::formatPage( $pageNode );
                    }

                    if ( $pageNode->nodeName == PageManagerConstants::xmlPageGroupNode ) {
                        self::formatPageGroup( $pageNode );
                    }
                    //XmlHelper::Dump( $pageNode );
                }
            }
        }


        /**
         * Format Page Group (boot and shutdown)
         *
         * @param DOMElement $pageGroup
         * @param string $boot
         * @param string $shutdown
         */
        private static function formatPageGroup( DOMElement $pageGroup, $boot = '', $shutdown = '' ) {
            if ( !$pageGroup->hasAttribute( PageManagerConstants::xmlBoot )  ) {
                $pageGroup->setAttribute( PageManagerConstants::xmlBoot, $boot );
            }

            if ( !$pageGroup->hasAttribute( PageManagerConstants::xmlShutdown  ) ) {
                $pageGroup->setAttribute( PageManagerConstants::xmlShutdown, $shutdown );
            }

            $nextBoot     = $pageGroup->getAttribute( PageManagerConstants::xmlBoot );
            $nextShutdown = $pageGroup->getAttribute( PageManagerConstants::xmlShutdown  );

            foreach ( $pageGroup->childNodes as $pageNode ) {
                if ( $pageNode instanceof DOMComment ) continue;

                if ( $pageNode->nodeName == PageManagerConstants::xmlPageNode ) {
                    self::formatPage( $pageNode, $nextBoot, $nextShutdown );
                }

                if ( $pageNode->nodeName == PageManagerConstants::xmlPageGroupNode ) {
                    self::formatPageGroup( $pageNode, $nextBoot, $nextShutdown );
                }
            }
        }


        /**
         * Format Page
         *
         * @param DOMElement $page
         * @param string     $boot
         * @param string     $shutdown
         */
        private static function formatPage( DOMElement $page, $boot = '', $shutdown = '' ) {
            if ( !$page->hasAttribute( PageManagerConstants::xmlBoot )  ) {
                $page->setAttribute( PageManagerConstants::xmlBoot, $boot );
            }

            if ( !$page->hasAttribute( PageManagerConstants::xmlShutdown  ) ) {
                $page->setAttribute( PageManagerConstants::xmlShutdown, $shutdown );
            }
        }
    }
?><?php

    /**
     * Site
     * @package Eaze
     * @subpackage Site
     * @author sergeyfast
     */
    class Site {

        /**
         * Current Host
         *
         * @var Host
         */
        public static $Host;

        /**
         * Hosts in site
         *
         * @var Host[]
         */
        public static $Hosts = array();

        /**
         * Default Host
         *
         * @var Host
         */
        public static $DefaultHost;

        /**
         * Paths array of name:// => path
         *
         * @var array
         */
        public static $Paths = array();

        /**
         * Current Site name
         *
         * @var string
         */
        public static $Name;

        /**
         * Modules
         *
         * @var array
         */
        public static $Modules = array();

        /**
         * Page
         *
         * @var Page
         */
        public static $Page;


        /**
         * Is Devel
         *
         * @var bool
         */
        private static $isDevel = false;


        /**
         * Get Is Devel <host devel="true">
         *
         * @return boolean
         */
        public static function IsDevel() {
            return self::$isDevel;
        }


        /**
         * Set Paths
         *
         * @param DOMNodeList $paths
         */
        private static function setPaths( DOMNodeList $paths ) {
            foreach ( $paths as $path ) {
                self::$Paths[$path->getAttribute( 'name' ) . '://'] = $path->getAttribute( 'value' );
            }
        }


        /**
         * Set Modules
         *
         * @param DOMNodeList $modules
         */
        private static function setModules( DOMNodeList $modules ) {
            if ( ! empty( $modules ) ) {
                Package::Load( 'Eaze.Modules' );

                foreach ( $modules as $module ) {
                    $class = $module->getAttribute( 'class' );
                    if ( !empty( $class ) ) {
                        call_user_func( array( $class, 'Init' ), $module->childNodes );
                    }
                }
            }
        }


        /**
         * Set Databases
         *
         * @param DOMNodeList $databases
         */
        private static function setDatabases( DOMNodeList $databases ) {
            foreach ( $databases as $database ) {
                if ( $database instanceof DOMComment ) {
                    continue;
                }

                $dbName = $database->getAttribute( 'name' );
                $param  = array(
                    'driver' => $database->getAttribute( 'driver' )
                    , 'name' => empty( $dbName ) ? 'default' : $dbName
                );

                /// Form array
                foreach ( $database->childNodes as $node ) {
                    if ( $node instanceof DOMComment ) {
                        continue;
                    }

                    $nodeName  = $node->nodeName;
                    $nodeValue = $node->nodeValue;

                    switch ( $nodeName ) {
                        case 'user':
                        case 'password':
                        case 'port':
                        case 'encoding':
                        case 'persistent':
                            $param[$nodeName] = $nodeValue;
                            break;
                        case 'hostname':
                            $param['host']    = $nodeValue;
                            break;
                        case 'name':
                            $param['dbname']  = $nodeValue;
                            break;
                        default:
                            Logger::Warning( 'Unknown key %s', $nodeName );
                            break;
                    }
                }

                ConnectionFactory::Add( $param );
            }
        }


        /**
         * Add Host from DOMNode
         *
         * @param DOMNode $node
         */
        private static function addHost( DOMNode $node ) {
            $host    = $node->getAttribute( 'name' );
            $isDevel = $node->getAttribute( "devel" );

            $protocol = 'http';
            $default  = false;
            $hostname = '';
            $port     = '';
            $webroot  = '';
            $paths    = array();

            foreach ( $node->childNodes as $child ) {
                $nodeName  = $child->nodeName;
                $nodeValue = $child->nodeValue;

                switch ( $nodeName ) {
                    case 'webroot':
                    case 'hostname':
                    case 'port':
                    case 'protocol':
                    case 'default':
                        $$nodeName = $nodeValue;
                        break;
                    case 'settings':
                        $pathLookup = XmlHelper::GetLookup( $child );
                        $paths = $pathLookup->Get( 'paths/*' );
                        break;
                    default:
                        Logger::Warning( 'Unknown key %s', $nodeName );
                        break;
                }
            }

            $hostObject = new Host( $protocol, $hostname, $port, $webroot, $default, $host );
            if ( $hostObject->__toString() == self::$Host->__toString() ) {
                Host::GetCurrentHost()->SetLocalname( $host );
                Host::GetCurrentHost()->SetDefault( $hostObject->GetDefault() );
            }

            if ( !empty( $paths ) ) {
                $hostObject->SetPaths( $paths );
            }

            if ( $default === 'true' ) {
                self::$DefaultHost = $hostObject;
                if ( $isDevel === 'true' ) {
                    self::$isDevel = true;
                }
            }

            self::$Hosts[$host] = $hostObject;
        }


        /**
         * Init site
         *
         * @param DOMElement $host  the current host
         */
        public static function Init( DOMElement $host ) {
            $siteLookup = XmlHelper::GetLookup( $host->parentNode->parentNode );
            $hostLookup = XmlHelper::GetLookup( $host );

            self::$Name = $host->parentNode->parentNode->getAttribute( 'name' );

            // Add Hosts
            self::$Host = Host::GetCurrentHost();

            // set is devel
            self::$isDevel = Convert::ToBoolean( $host->getAttribute( SiteManagerConstants::xmlDevel ) );
            if ( is_null( self::$isDevel ) ) {
                self::$isDevel = false;
            }

            foreach ( $host->parentNode->childNodes as $node ) {
                self::addHost( $node );
            }

            $localSettings = $hostLookup->Get( 'settings' )->item( 0 );
            if ( !empty( $localSettings ) ) {
                self::setPaths( $hostLookup->Get( 'settings/paths/*' ) );
                self::setDatabases( $hostLookup->Get( 'settings/databases/*' ) );
                self::setModules( $hostLookup->Get( 'settings/modules/*' ) );
            } else {
                self::setPaths( $siteLookup->Get( 'settings/paths/*' ) );
                self::setDatabases( $siteLookup->Get( 'settings/databases/*' ) );
                self::setModules( $siteLookup->Get( 'settings/modules/*' ) );
            }
        }


        /**
         * @static
         * @param  $path
         * @param null $hostname
         * @return mixed|string
         */
        public static function TranslateUrlWithPath( $path, $hostname = null ) {
            if ( !empty( $hostname )
                 && !empty( self::$Hosts[$hostname] )
            ) {
                $currentHost = self::$Hosts[$hostname];
                if ( !empty( $currentHost->Paths ) ) {
                    $hostPaths = $currentHost->Paths;
                }
            }

            // Detect path template
            if ( preg_match( '#^.+?://*#i', $path, $regs ) ) {
                $result = $regs[0];

                // Use Normal
                if ( empty( $hostPaths ) ) {
                    if ( !empty( self::$Paths[$result] ) ) {
                        $pathTemplate = self::$Paths[$result];
                    }
                } else { // User HostPaths
                    if ( !empty( $hostPaths[$result] ) ) {
                        $pathTemplate = $hostPaths[$result];
                    }
                }

                if ( !empty( $pathTemplate ) ) {
                    $result = str_replace( $result, $pathTemplate . "/", $path );
                } else {
                    $result = $path;
                }
            } else {
                $result = $path;
            }

            return $result;
        }


        /**
         * Translate Path Template
         *
         * @param string  $path
         * @return string
         */
        public static function TranslatePathTemplate( $path ) {
            // Detect path template
            if ( preg_match( '#^.+://*#i', $path, $regs ) ) {
                $result = $regs[0];

                // Use Normal
                if ( !empty( self::$Paths[$result] ) ) {
                    $pathTemplate = self::$Paths[$result];
                }

                if ( false == empty( $pathTemplate ) ) {
                    $result = str_replace( $result, $pathTemplate . "/", $path );
                } else {
                    $result = $path;
                }
            } else {
                $result = $path;
            }

            return $result;
        }


        /**
         * Get Web Path
         *
         * @param string $path
         * @param string $hostname
         * @return string
         */
        public static function GetWebPath( $path, $hostname = null ) {
            $session = "";
            $currentHost = self::$Host;

            /** Set Default Host Path resolution */
            if ( empty( $hostname ) && !empty( self::$DefaultHost ) ) {
                $hostname = self::$DefaultHost->GetLocalname( );
            }

            if ( !empty( $hostname )
                 && !empty( self::$Hosts[$hostname] )
            ) {
                $currentHost = self::$Hosts[$hostname];
                if ( self::$Host->GetProtocol( ) != $currentHost->getProtocol( ) ) {
                    $session = sprintf( '%sPHPSESSID=%s', ( strpos( $path, "?" ) === false ) ? "?" : "&", Session::getId( ) );

                    if ( ( strlen( $path ) > 3 )
                         && ( in_array( substr( $path, strlen( $path ) - 3, 3 ), array( "gif", "jpg", "css" ) ) )
                    ) {
                        $session = "";
                    }
                }
            }

            $result = self::translateUrlWithPath( $path, $hostname );
            $result = sprintf( "%s%s%s", $currentHost->GetPathString(), $result, $session );

            return $result;
        }


        /**
         * Get Web Path with {var} replacement
         * @static
         * @param  string $path
         * @param string $hostname
         * @return mixed
         */
        public static function GetWebPathEx( $path, $hostname = null ) {
            $path = self::GetWebPath( $path, $hostname );

            $parameters = Response::getParameters();
            $keys       = array();
            $values     = array();

            foreach ( $parameters as $key => $value ) {
                if ( is_string( $value ) || is_numeric( $value ) ) {
                    $keys[]   = sprintf( "{%s}", $key );
                    $values[] = $value;
                }
            }

            return str_replace( $keys, $values, $path );
        }


        /**
         * Get Real Path
         *
         * @param string $path
         * @return string
         */
        public static function GetRealPath( $path ) {
            $result = self::translateUrlWithPath( $path );

            $result = sprintf( "%s%s", __ROOT__, $result );
            return $result;
        }


        /**
         * Get Current URL
         *
         * @return string
         */
        public static function GetCurrentURI() {
            static $url;

            if ( empty( $url ) ) {
                if ( strlen( Host::GetCurrentWebroot() ) == 0 ) {
                    $url = Request::getRequestUri();
                } else {
                    $pos = strpos( Request::getRequestUri(), Host::GetCurrentWebroot() );
                    if ( $pos !== false ) {
                        $start = strlen( Host::GetCurrentWebroot() ) + $pos;
                        $end   = strlen( Request::getRequestUri() ) - $start;
                        $url   = substr( Request::getRequestUri(), $start, $end );
                    }
                }
            }

            return $url;
        }
    }

?><?php
    if ( !defined( 'CONFPATH_SITES' ) ){
        define( 'CONFPATH_SITES', 'etc/conf/sites.xml');
    }

    if ( !defined( 'CONFPATH_ERRORS' ) ) {
        define( 'CONFPATH_ERRORS', 'etc/errors');
    }

    Package::Load( 'Eaze.Site' );
    Package::Load( 'Eaze.Helpers' );
    Package::Load( 'Eaze.Database' );

    /**
     * SiteManager Constants
     *
     * @package Eaze
     * @subpackage Site
     */
    class SiteManagerConstants {
        const detectSiteQuery         = '//host[(hostname="%s" and webroot="%s" and port="%s" and protocol="%s") or hostname="*"]';
        const xmlExtends              = 'extends';
        const xmlDevel                = 'devel';
        const xmlPaths                = 'paths';
        const siteSettingsQuery       =  '//site[@name="%s"]/settings';
        const defaultSiteCachePattern = 'sites_%s.xml';

        public static $hostDefaults = array(
            'webroot'    => ''
            , 'port'     => 80
            , 'protocol' => 'http'
            , 'default'  => 'false'
        );
    }


    /**
     * SiteManager
     *
     * @package Eaze
     * @subpackage Site
     * @author sergeyfast
     */
    class SiteManager {

        /**
         * Detect Site
         * @param bool $autoDetectPage optional  start PageManager::DetectPage (default false)
         * @return
         */
        public static function DetectSite( $autoDetectPage = true ) {
            $doc = new DOMDocument();
            $doc->preserveWhiteSpace = false;

            if ( !$doc->load( CacheManager::GetCachedXMLPath(
                    CONFPATH_SITES, SiteManagerConstants::defaultSiteCachePattern , array( 'SiteManager', 'CacheSitesXML' ) ) )
                    ) {
                Logger::Error( 'Error while loading sites.xml' );
                return;
            }

            $currentHost = Host::GetCurrentHost();
            $query       = sprintf( SiteManagerConstants::detectSiteQuery, $currentHost->GetHostname(), $currentHost->GetWebroot(),$currentHost->GetPort(), $currentHost->GetProtocol() );

            Logger::Debug( 'Searching site: %s', $query );
            Logger::Checkpoint();
            $xpath = new DOMXPath( $doc );
            $host  = $xpath->query( $query )->item( 0 );

            if ( empty( $host ) ) {
                Response::HttpStatusCode( '501', 'Not Implemented' );
            }

            // initialize site settings
            Site::Init( $host );
            Logger::Debug( 'Site <b>%s</b> initialized', Site::$Name );

            if ( $autoDetectPage  ) {
                PageManager::DetectPage();
            }
        }


        /**
         * Cache Sites.xml
         *
         * @param DOMDocument $doc  the sites.xml
         * @return DOMDocument
         */
        public static function CacheSitesXML( DOMDocument $doc ) {
            // Merge Site Settings
            $sitesList = $doc->getElementsByTagName( 'site' );
            foreach ( $sitesList as $node )  {

                if ( $node->hasAttribute( SiteManagerConstants::xmlExtends  ) ) {
                    $xpath = new DOMXPath( $doc );

                    $exSiteName = $node->getAttribute( SiteManagerConstants::xmlExtends  );
                    $exSiteSettingsList = $xpath->evaluate( sprintf( SiteManagerConstants::siteSettingsQuery , $exSiteName ) );
                    $exSiteSettings = $exSiteSettingsList->item(0);

                    if (empty($exSiteSettings)) {
                        Logger::Warning( 'Unknown site name %s for merging %s ', $exSiteName, $node->getAttribute('name') );
                    }

                    // Get Current Settigns
                    $curSiteSettings = XmlHelper::GetChildNode( 'settings', $node );
                    if ( !empty( $curSiteSettings ) ) {
                        $mergedSiteSettings = XmlHelper::MergeNodes( $exSiteSettings, $curSiteSettings);
                        $node->replaceChild( $doc->importNode( $mergedSiteSettings, true ), $curSiteSettings );
                    } else {
                        $node->appendChild( $curSiteSettings );
                    }
                }
            }

            // Reformat hosts
            $hostsLits = $doc->getElementsByTagName( 'host' );
            foreach ( $hostsLits as $host ) {
                if ( !$host->hasAttribute('name')
                    || ( trim( $host->getAttribute('name') ) == '' )
                ) {
                    Logger::Warning( 'Host with empty name!' );
                    continue;
                }


                // check for devel attr and apply it for all hosts and site
                if ( !$host->hasAttribute(SiteManagerConstants::xmlDevel )
                     && $host->parentNode->parentNode->hasAttribute(SiteManagerConstants::xmlDevel) )
                {
                    $host->setAttribute( SiteManagerConstants::xmlDevel, $host->parentNode->parentNode->getAttribute(SiteManagerConstants::xmlDevel) );
                }

                // host defaults
                foreach ( SiteManagerConstants::$hostDefaults as $key => $value ) {
                    $tag = XmlHelper::GetChildNode( $key, $host );
                    if ( empty( $tag) ) {
                        $host->appendChild(  $doc->createElement( $key, $value ) );
                    }
                }

                // overrided settings for hosts
                $localSettings = XmlHelper::GetChildNode( 'settings', $host );
                if ( !empty($localSettings) ) {
                    $curSiteSettings = XmlHelper::GetChildNode( 'settings', $localSettings->parentNode->parentNode->parentNode  );

                    $mergedSiteSettings = XmlHelper::MergeNodes( $curSiteSettings, $localSettings );
                    $host->replaceChild( $doc->importNode( $mergedSiteSettings, true ), $localSettings );
                }
            }

            return $doc;
        }
    }
?><?php
    /**
     * Template
     *
     * @package Eaze
     * @subpackage Eaze.Site
     */
    class Template {
        /**
         * Registered Functions
         *
         * @var array
         */
        public static $Functions = array(
            "web"       => 'Site::GetWebPath("\\1") '
            , "webs"	=> 'Site::GetWebPath("\\1", "secure") '
            , "real"    => 'Site::GetRealPath("\\1") '
            , "lang"    => 'LocaleLoader::Translate("\\1") '
            , "upper"   => 'strtoupper("\\1") '
            , "lower"   => 'strtolower("\\1") '
            , "ucfirst" => 'ucfirst("\\1") '
            , "tobr"    => 'nl2br("\\1")'
            , "num"     => 'number_format( "\\1", 0, "", " " )'
            , "numf"    => 'number_format( "\\1", 2 )'
            , "numfr"   => 'number_format( "\\1", 2, ",", " " )'
            , "form"    => 'FormHelper::RenderToForm( "\\1" )'
            , "link"    => 'DBPageUtility::GetLink( "\\1" )'
        );

        private static $actFunctions = array(
            "increal" => 'include( Template::GetCachedRealPath("\\1") )'
        );


        /**
         * Parse Tempalte
         *
         * @param CacheManagerData $data
         */
        public static function Parse( CacheManagerData $data ) {
            $t = $data->data;

            self::parseVariables( $t );
            self::parseFuncWithVariables( $t );
            self::parseFunctions( $t );

            $data->data = $t;
        }


        /**
         * Render Template
         *
         * @param string $filename
         * @return string
         */
        public static function Render( $filename ) {
            Logger::Debug( 'Loading Template %s', $filename  );
            // Start buffering
            foreach ( Response::getParameters() as $_key => $_value ) {
                $$_key = $_value;
            }

            ob_start();
            /** @noinspection PhpIncludeInspection */
            require $filename ;
            $data = ob_get_contents();
            ob_clean();

            if ( AssetHelper::$PostProcess ) {
                $data = AssetHelper::PostProcess( $data );
            }

            echo $data;
        }



        /**
         * Parse Variables
         *
         * @param string $tempalteContents
         */
        private static function parseVariables(&$tempalteContents) {
            $m = array();
            if (preg_match_all('/{\\$([^{}]+)}/', $tempalteContents, $m )) {
	           foreach ( $m[1] as $variable ) {
	               $searchVar = $variable;

                   $replaceVar = str_replace( ".", "->", $variable );
	               $replaceVar = str_replace( "]", "']", $replaceVar );
	               $replaceVar = str_replace( "[", "['", $replaceVar );

	               $tempalteContents = str_replace(
                        sprintf( '{$%s}',  $searchVar ),
                        sprintf( '<?= $%s; ?>', $replaceVar),
                        $tempalteContents
                    );
	           }
            }
        }


        /**
         * Parse Functions with Variables
         *
         * @param string $tempalteContents
         */
        private static function parseFuncWithVariables(&$tempalteContents) {
            $m = array();
            if (preg_match_all('/{([^\s:]+):\\$([^{}]+)}/', $tempalteContents, $m)) {
               for( $i = 0; $i < count( $m[0] ); $i ++ ) {
                   $func = $m[1][$i];
                   $v    = $m[2][$i];

                   if ( !empty( self::$Functions[$func] ) ) {
                       $funcBody = str_replace( '"\\1"', '%s', self::$Functions[$func] );
                       $fullVar  = str_replace( '.', '->', $v );
                       $fullVar  = str_replace( "]", "']", $fullVar );
	                   $fullVar  = str_replace( "[", "['", $fullVar );
                       $fullFunc = sprintf( "<?= %s; ?>", sprintf( $funcBody, "$" . $fullVar ) );
                       $tempalteContents = str_replace( $m[0][$i], $fullFunc, $tempalteContents );
                   }

                   // dummy check
                   if ( !empty( self::$actFunctions[$func] ) ) {
                       $funcBody = str_replace( '"\\1"', '%s', self::$actFunctions[$func] );
                       $fullVar  = str_replace( '.', '->', $v );
                       $fullVar  = str_replace( "]", "']", $fullVar );
	                   $fullVar  = str_replace( "[", "['", $fullVar );
                       $fullFunc = sprintf( "<? %s; ?>", sprintf( $funcBody, "$" . $fullVar ) );

                       $tempalteContents = str_replace( $m[0][$i], $fullFunc, $tempalteContents );
                   }
               }
            }
        }


        /**
         * Parse Functions
         *
         * @param string $tempalteContents
         */
        private static function parseFunctions(&$tempalteContents) {
            foreach ( self::$Functions as $func => $phpFunc ) {
                $tempalteContents = preg_replace(
                     sprintf( "/{%s:([^{}]+)}/", $func ),
                     sprintf( "<?= %s;?>", $phpFunc ),
                     $tempalteContents );
            }

            // dummy check
            foreach ( self::$actFunctions as $func => $phpFunc ) {
                $tempalteContents = preg_replace(
                     sprintf( "/{%s:([^{}]+)}/", $func ),
                     sprintf( "<? %s;?>", $phpFunc ),
                     $tempalteContents );
            }
        }


        public static function GetCachedRealPath($path) {
            $filepath = CacheManager::GetCachedFilePath( Site::GetRealPath($path), "%s_%s.inc", array( "Template","Parse"));
            return $filepath;
        }

        public static function GetCachedPath($path) {
            $filepath = CacheManager::GetCachedFilePath( $path, "%s_%s.inc", array( "Template","Parse"));
            return $filepath;
        }


        /**
         * Load File and Render it
         *
         * @param string $path
         */
        public static function Load( $path ) {
            if ( !file_exists( $path ) ) {
                Logger::Fatal( 'No such template %s', $path );
            } else {
                Logger::Debug( 'Opening template: %s', $path );
                self::Render( Template::GetCachedPath( $path ) );
            }
        }
    }
?>