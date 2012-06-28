<?php
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
?>