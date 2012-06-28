<?php
    /**
     * Locale Loader
     *
     * @desc Module Parameters
     * path:        path to language files, default is lang://
     * default:     default language, required
     * allowChange: allow change, default is false
     * LC_*:        set locale
     * htmlEncoding: set encoding for html pages (default is utf-8);
     * @package Eaze
     * @subpackage Modules
     * @static
     * @author sergeyfast
     */
    class LocaleLoader implements IModule {

        /**
         * Russian
         */
        const Ru = 'ru';

        /**
         * Module Parameters
         *
         * @var array
         */
        private static $params = array();


        /**
         * setlocale Module Parameters
         * @var array
         */
        private static $locales = array();

        /**
         * Initialized Flag
         *
         * @var boolean
         */
        public static $Initialized = false;

        /**
         * Default Path constant
         */
        const defaultPath = 'lang://';

        /**
         * Session Language Key
         *
         */
        const defaultSessionKey = '__language';

        /**
         * Current Language
         *
         * @var string
         */
        public static $CurrentLanguage = '';


        /**
         * Current HTML Encoding
         *
         * @var string
         */
        public static $HtmlEncoding = 'utf-8';


        /**
         * Language Messages
         *
         * @var array
         */
        private static $messages = array();


        /**
         * Init Module
         *
         * @param DOMNodeList $params
         */
        public static function Init( DOMNodeList $params ) {
            foreach ( $params as $param ) {
                $nodeName = $param->getAttribute( 'name' );
                if ( strpos( $nodeName, 'LC_' ) === 0 ) {
                    self::$locales[$nodeName] = $param->nodeValue;
                } else {
                    self::$params[$nodeName]  = $param->nodeValue;
                }
            }

            if ( empty( self::$params['path'] ) ) {
                self::$params['path'] = self::defaultPath;
            }

            if ( !empty( self::$params['htmlEncoding'] ) ) {
                self::$HtmlEncoding = self::$params['htmlEncoding'];
            } else {
                self::$params['html-encoding'] = self::$HtmlEncoding;
            }

            self::$Initialized = true;
            self::Process();
        }


        /**
         * Process module
         *
         */
        public static function Process() {
            if ( !self::$Initialized ) {
                Logger::Error( "Module isn't in Initialized state" );
                return;
            }

            // setlocale
            foreach( self::$locales as $categoryName => $value ) {
                setlocale( constant( $categoryName ), $value );
            }

            // detect current lang
            self::$CurrentLanguage = self::detectLanguage();
            self::LoadLanguage( self::$CurrentLanguage );
        }


        /**
         * Detect Language
         *
         * @return string
         */
        private static function detectLanguage() {
            if ( empty( self::$params['allowChange'] ) ) {
                self::$params['allowChange'] = 'false';
            }

            if ( 'false' == self::$params['allowChange'] ) {
                return self::$params['default'];
            }

            // change lang from post
            $lang = Request::getString( 'lang' );
            if ( false == empty( $lang ) ) {
                if ( file_exists( Site::GetRealPath( self::$params['path'] . $lang . '.xml' ) ) ) {
                    Session::setString( self::defaultSessionKey, $lang );
                    return $lang;
                }
            }

            //set lang from session
            $lang = Session::getString( self::defaultSessionKey );
            if ( false == empty( $lang ) ) {
                Session::setString( self::defaultSessionKey, $lang );
                return $lang;
            }

            // return default
            Session::setString( self::defaultSessionKey, self::$params['default'] );
            return self::$params['default'];
        }


        /**
         * Load Language from php file
         *
         * @param string $lang
         */
        public static function LoadLanguage( $lang ) {
            $filepath = CacheManager::GetCachedFilePath( Site::GetRealPath( self::$params['path'] . $lang . '.xml' ), '%s_%s.lng', array( 'LocaleLoader', 'Cache' ), $lang );

            $l = array();

            /** @noinspection PhpIncludeInspection */
            include_once( $filepath );
            if( !array_key_exists( $lang, self::$messages ) ) {
                self::$messages[$lang] = $l;
            }
        }


        /**
         * Cache Language File
         *
         * @param CacheManagerData $data
         */
        public static function Cache( CacheManagerData $data ) {
            $t = $data->data;

            $doc = new DOMDocument();
            $doc->preserveWhiteSpace = false;
            $doc->loadXML( $t );

            $parsedPHP = self::parse( $doc );

            if ( $doc->xmlEncoding !== 'utf-8' ) {
                $parsedPHP = iconv( 'utf-8', $doc->xmlEncoding, $parsedPHP );
            }

            $data->data = $parsedPHP;
        }


        /**
         * Parse XML File To PHP file
         *
         * @param DOMDocument $doc
         * @return unknown
         */
        private static function parse( DOMDocument $doc ) {
            $data = new CacheManagerData( "<?php \n" );

            foreach ( $doc->childNodes as $node ) {
                self::parseGroup( $node, $data, null );
            }

            $data->data .= '?>';
            return $data->data;
        }


        /**
         * Parse XML Group to PHP array def
         *
         * @param DOMElement $node
         * @param CacheManagerData $data
         * @param string $path
         */
        private static function parseGroup( DOMElement $node, CacheManagerData $data, $path ) {
            foreach ( $node->childNodes as $childNode ) {

                if ( !empty( $childNode->firstChild ) && $childNode->firstChild instanceof DOMText ) {
                    $data->data .= '    $l' . "['" . $path . $childNode->nodeName . "'] = '" . addcslashes( $childNode->nodeValue, "'" ) . "';\n";
                } else {
                    if ( $childNode->hasChildNodes() && count( $childNode->childNodes ) > 0 ) {
                        $k = $path . $childNode->nodeName . ".";
                        self::parseGroup( $childNode, $data, $k );
                    }
                }
            }
        }


        /**
         * Translate Message
         *
         * @param string $message  the message string
         * @return string
         */
        public static function Translate( $message ) {
            if ( !self::$Initialized ) {
                return $message;
            }

            if ( !empty( self::$messages[self::$CurrentLanguage][$message] ) ) {
                return str_replace( "\'", "'", self::$messages[self::$CurrentLanguage][$message] );
            }

            return $message;
        }


        /**
         * Convert From Win1251 To UTF8 if current language != utf8
         * @param string $value
         * @return string
         */
        public static function TryToUTF8( $value ) {
            if ( mb_detect_encoding( $value, 'CP1251,UTF-8' ) != 'UTF-8' ) {
                $value = TextHelper::ToUTF8( $value );
            }

            return $value;
        }


        /**
         * Convert From UTF8 to Win1251 if current language != utf8
         * @param string $value
         * @return string
         */
        public static function TryFromUTF8( $value ) {
            if ( mb_detect_encoding( $value, 'CP1251,UTF-8' ) != 'UTF-8' ) {
                $value = TextHelper::FromUTF8( $value );
            }

            return $value;
        }
    }

?>
<?php
    /**
     * Locale Loader
     *
     * @desc Module Parameters
     * senderEmail: sender email
     * senderName:  sender name
     * charset:     encoding, default is iso-8859-1
     * xMailer:     xmailer, default is Eaze
     * @package Eaze
     * @subpackage Eaze.Modules
     * @static
     * @author Sergey Bykov
     */
    class MailFactory implements IModule {
        /**
         * Initialized Flag
         *
         * @var boolean
         */
        public static $Initialized = false;

        /**
         * Module Parameters
         *
         * @var array
         */
        private static $params = array();

        /** Charset of message */
        private $charset           = "iso-8859-1";

        /** Content Type */
        private $contentType       = "text/html";

        /** Encoding */
        private $encoding          = "8bit";

        /** Mime Version */
        private $mimeVersion       = "1.0";

        /** Content Type */
        private $msgContentType    = "multipart/mixed; boundary=\"Message-Boundary\"";

        /** X-mailer */
        private $xMailer           = "Eaze";

        /** Sender email */
        private $senderEmail       = "";

        /** Sender Name */
        private $senderName        = "";

        /** Subject */
        private $subject           = "";

        /** Headers */
        private $headers           = "";

        /** Body */
        private $body              = "";

        /** Message Body */
        private $messageBody       = "";

        /** Sets word wrapping on the body of the message to a given number of characters */
        private $wordWrap          = 0;

        /** To */
        private $to                = array();

        /** CC */
        private $cc                = array();

        /** BCC */
        private $bcc               = array();

        /** Reply To */
        private $replyTo           = array();

        /** Attachments */
        private $attachment        = array();

        /** LE */
        private $EOL               = "\n";

        /** Error Strings </p> */
        public static $Errors = array(
            1   => "Sender email is null."
            , 2 => "Sender email is not valid."
            , 3 => "There are no valid recepients."
            , 4 => "Function \"mail\" does not exists."
        );


        /**
         * Switches between html and plain text modes
         *
         * @param boolena $isHTML  the html mode if<code>true</code>.
         */
        public function SetHTML( /*boolean*/ $isHTML ) {
            if ( true == $isHTML ) {
                $this->contentType = "text/html";
            } else {
                $this->contentType = "text/plain";
            }
        }


        /**
         * Add Recipient ( "to" ) address
         *
         * @param string $email  the user email
         * @param string $name   the user name
         */
        public function AddRecipient( /*string*/ $email, /*string*/ $name = null ) {
            $this->to[] = array(
                "email"  => $email
                , "name" => $name
            );
        }


        /**
         * Add "CC" adress
         *
         * @param string $email  the user email
         * @param string $name   the user name
         */
        public function AddCC( /*string*/ $email, /*string*/ $name = null ) {
            $this->cc[] = array(
                "email"  => $email
                , "name" => $name
            );
        }


        /**
         * Add "to" adress
         *
         * @param string $email  the user email
         * @param string $name   the user name
         */
        public function AddBCC( /*string*/ $email, /*string*/ $name = null ) {
            $this->bcc[] = array(
                "email"  => $email
                , "name" => $name
            );
        }


        /**
         * Set "ReplyTo" adress
         *
         * @param string $email  the user email
         * @param string $name   the user name
         */
        public function AddReplyTo( /*string*/ $email, /*string*/ $name = null ) {
            $this->replyTo[] = array(
                "email"  => $email
                , "name" => $name
            );
        }


        /**
         * Add Attachments
         *
         * @param string $filename     the file
         * @param string $name         the filename
         * @param string $content      the file content
         * @param string $contentType  the content type
         */
        public function AddAttachment( /*string*/ $filename
                                      , /*string*/ $name = null
                                      , /*string*/ $content = null
                                      , /*string*/ $contentType = null ) {
            $this->attachment[] = array(
                "file"          => $filename
                , "filename"    => $name
                , "content"     => $content
                , "contentType" => $contentType
            );
        }


        /**
         * Add Hedaer
         *
         * @param string $header  the header
         * @param string $value   the hedaer value
         */
        public function AddHeader( $header, $value ) {
            $this->headers .= $header . ": " . $value . $this->EOL;
        }


        /**
         * Set xMailer
         *
         * @param string $xMailer  the xMailer
         */
        public function SetXMailer( /*string*/ $xMailer ) {
            $this->xMailer = $xMailer;
        }


        /**
         * Set Message Charset
         *
         * @param string $charset  the message charset
         */
        public function SetCharset( /*string*/ $charset ) {
            $this->charset = $charset;
        }


        /**
         * Add Body
         *
         * @param string $header  the header
         * @param string $value   the hedaer value
         */
        public function AddBody( /*string*/ $header, /*string*/ $value = null ) {
            if ( true == empty( $value ) ) {
                $this->body .= $header . $this->EOL;
            } else {
                $this->body .= $header . ": " . $value . $this->EOL;
            }
        }


        /** Clear Recipients ("to") */
        public function ClearRecipients() {
            $this->to = array();
        }


        /** Clear CCs */
        public function ClearCCs() {
            $this->cc = array();
        }


        /** Clear BCCs */
        public function ClearBCCs() {
            $this->to = array();
        }


        /** Clear ReplyTos */
        public function ClearReplyTos() {
            $this->replyTo = array();
        }


        /** Clear All Recipients */
        public function ClearAllRecipients() {
            $this->clearRecipients();
            $this->clearCCs();
            $this->clearBCCs();
        }


        /** Clear Attachments */
        public function ClearAttachments() {
            $this->attachment = array();
        }


        /** Set sender name and email */
        public function SetSender( /*string*/ $email, /*string*/ $name = null ) {
            $this->senderEmail = $email;
            $this->senderName  = $name;
        }


        /** Set subject */
        public function SetSubject( /*string*/ $subject ) {
            $this->subject = $subject;
        }

        /** Set wordwrap */
        public function SetWordwrap( /*integer*/ $wordwrap ) {
            $this->wordWrap = $wordwrap;
        }


        /** Get subject */
        public function GetSubject() {
            return ( stripslashes( $this->subject ) );
        }


        /** Set message body */
        public function SetMessageBody( /*string*/ $messageBody ) {
            $this->messageBody = $messageBody;
        }


        /** Get Body */
        public function GetBody() {
            return $this->body;
        }


        /** Get Sender String */
        public function GetSenderString() {
            $senderString = $this->senderEmail;

            if ( false == empty( $this->senderName ) ) {
                $senderString = "\"" . $this->senderName . "\" " . "<" . $this->senderEmail . ">" ;
            }

            return ( $senderString );
        }

        /**
         * Set email content type
         * @param $contentType
         * @return void
         */
        public function SetMsgContentType( $contentType ) {
           $this->msgContentType = ! empty( $contentType ) ? $contentType : $this->msgContentType;
        }


        /**
         *  Check email format
         *
         * @param string $email
         * @return boolean
         */
        public static function CheckEmailFormat( /*string*/ $email ) {
            if ( true == preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,5})$/', $email ) ) {
                return ( true );
            } else {
                return ( false );
            }
        }


        /**
         * Check MX Host
         *
         * @param string $email
         * @return bool
         */
        public static function CheckMXHost( /*string*/ $email ) {
            if (!self::CheckEmailFormat( $email )) {
                return false;
            }

        	$userDomain =  explode("@", $email);
        	if ( true == isset( $userDomain[1] )) {
        	    if ( function_exists("getmxrr")) {
        	        $hostArray = null;
                    getmxrr( $userDomain[1], $hostArray );
        	    } else {
        	        $hostArray  = gethostbynamel( $userDomain[1] );
        	    }
            } else {
                return false;
            }

            if ( (true == is_array( $hostArray )) && (count( $hostArray ) > 0) ) {
                return true;
            }

            return false;
        }


        /**
         * Get Adresses String
         *
         * @param array $adresses  the array of emails and names
         * @return string
         */
        public function GetAdressesString( /*array*/ $adresses ) {
            $i           = 1;
            $newAdresses = array();
            $adressesStr = "";

            // check for incorrect email or empty emails
            foreach ( $adresses as $adress ) {
                if ( false == empty( $adress["email"] ) ) {

                    if ( true == $this->checkEmailFormat( $adress["email"] ) ) {

                        if ( true == empty( $adress["name"] ) ) {
                            $newAdresses[] = $adress["email"];
                        } else {
                            $newAdresses[] = "\"" . $adress["name"] . "\" <" . $adress["email"] . ">";
                        }
                    }
                }
            }

            //set adresses string
            foreach ( $newAdresses as $adress ) {
                $adressesStr .= $adress;

                if ( $i != count( $newAdresses ) ) {
                    $adressesStr .= ", ";

                    $i++;
                }
            }

            return ( $adressesStr );
        }


        /** Proccess plain text word wrap */
        private function proccessWordWrap( $wordwrap = 0 ) {
            $this->wordWrap = $wordwrap;

            if (( $this->wordWrap >= 20 )
                    && ( "text/plain" == $this->contentType )) {
                $this->messageBody = wordwrap( $this->messageBody, $this->wordWrap, "\n" );
            }
        }


        /**
         * Send Mail Function
         *
         * @param bool $showMail  show mail message
         */
        public function SendMail( $showMail = false ) {
            $errorCodes = $this->validate();

            //check for right data
            if ( false == empty( $errorCodes ) ) {
                return ( $errorCodes );
            }

            //formig headers
            $this->addHeader( "From",         $this->getSenderString() );
            $this->addHeader( "Reply-To",     $this->getAdressesString( $this->replyTo ));

            if ( "" != trim( $this->getAdressesString( $this->cc ) ) ) {
                $this->addHeader( "CC", $this->getAdressesString( $this->cc ));
            }

            if ( "" != trim( $this->getAdressesString( $this->bcc ) ) ) {
                $this->addHeader( "BCC", $this->getAdressesString( $this->bcc ));
            }

            $this->addHeader( "X-Mailer",     $this->xMailer );
            $this->addHeader( "MIME-version", $this->mimeVersion );
            $this->addHeader( "Content-type", $this->msgContentType );

            //form body headers
            $this->addBody( "--Message-Boundary" );
            $this->addBody( "Content-type",              $this->contentType . "; charset=\"" . $this->charset . "\"" );
            $this->addBody( "Content-transfer-encoding", $this->encoding . "\n" );
            //$this->addBody( "\n" );


            $this->proccessWordWrap( $this->wordWrap );

            $this->addBody( $this->messageBody );
            $this->proccessAttachments();

            //return true;

            if (( true == function_exists( "mail" ) )
                    && ( $showMail == false )) {
                $result = mail(
                    $this->getAdressesString( $this->to )
                    , $this->getSubject()
                    , $this->getBody()
                    , $this->headers
                    , "-f" . $this->senderEmail
                );

                return ( $result );
            } elseif ( $showMail == true ) {
                $message = "To: " . $this->getAdressesString( $this->to ) . $this->EOL
                        . "Subject: " . $this->getSubject() . $this->EOL
                        . $this->headers . $this->EOL
                        . $this->getBody();

                return ( $message );
            }

            return false;
        }


        /**  Proccess Attachments */
        private function proccessAttachments() {
            if  ( false == empty( $this->attachment ) ) {

                foreach ( $this->attachment as $file ) {

                    if ( true == file_exists( $file["file"] ) ) {
                        $content = file_get_contents( $file["file"] );
                    } elseif ( false == is_null( $file["content"] ) ) {
                        $content = $file["content"];
                    } else {
                        continue;
                    }

                    $encodedAttach = chunk_split( base64_encode( $content ) );

                    $this->addBody( "\n\n--Message-Boundary" );

                    if ( true == empty( $file["filename"] ) ) {
                        $file["filename"] = basename( $file["file"] );
                    }

                    $this->addBody( "Content-Disposition",       "attachment; filename=\"" . $file["filename"] . "\"" );
                    if ( true == empty( $file["contentType"] ) ) {
                        $this->addBody( "Content-Type", "application/octet-stream; name=\"" . $file["filename"] . "\"" );
                    } else {
                        $this->addBody( "Content-Type", $file["contentType"] . "; name=\"" . $file["filename"] . "\"" );
                    }
                    $this->addBody( "Content-ID", "<" . $file["filename"] . ">" );
                    $this->addBody( "Content-Transfer-Encoding", "base64\n" );
                    $this->addBody( $encodedAttach );
                }
                $this->addBody( "--Message-Boundary--" );
            }
        }


        /**
         *  Constructor
         *
         * @param string $senderEmail  the sender email
         * @param string $senderName   the sender name
         * @param string $charset      the charset
         * @param string $xMailer      the xMailer
         * @param string $subject      the subject
         * @param string $messageBody  the message body
         */
        public function __construct( /*string*/$senderEmail = null
                                    , /*string*/ $senderName = null
                                    , /*string*/ $charset = null
                                    , /*string*/ $xMailer = null
                                    , /*string*/ $subject = null
                                    , /*string*/ $messageBody = null ) {
            if ( false == is_null( $xMailer ) ) {
                $this->setXMailer( $xMailer );
            }

            if ( false == is_null( $charset ) ) {
                $this->setCharset( $charset );
            }

            $this->setSender( $senderEmail, $senderName );
            $this->setSubject( $subject );
            $this->setMessageBody( $messageBody );
        }


        /** Validate data */
        public function Validate() {
            $errorCodes = array();

            if ( true == empty( $this->senderEmail ) ) {
                $errorCodes[] = 1;
            }

            if ( false == $this->checkEmailFormat( $this->senderEmail ) ) {
                $errorCodes[] = 2;
            }

            if ( "" == trim( $this->getAdressesString( $this->to )) ) {
                $errorCodes[] = 3;
            }

            if ( true == empty( $this->replyTo ) ) {
                $this->addReplyTo( $this->senderEmail, $this->senderName );
            }

            return ( $errorCodes );
        }


        /**
         * Init Module
         *
         * @param DOMNodeList $params  the params node list
         * @static
         */
        public static function Init(DOMNodeList $params) {
            foreach ( $params as $param ) {
                /** @var DOMElement $param */
                self::$params[$param->getAttribute("name")] = $param->nodeValue;
            }

            if ( !isset( self::$params["senderEmail"] ) ) {
                self::$params["senderEmail"] = "";
            }

            if ( !isset( self::$params["senderName"] ) ) {
                self::$params["senderName"] = "";
            }

            if ( !isset( self::$params["charset"] ) ) {
                self::$params["charset"] = "iso-8859-1";
            }

            if ( !isset( self::$params["bcc"] ) ) {
                self::$params["bcc"] = "";
            }

            if ( !isset( self::$params["xMailer"] ) ) {
                self::$params["xMailer"] = "Eaze v1.0";
            }

            self::$Initialized = true;
        }


        /**
         * Constructs an object
         *
         * @return MailFactory
         */
        public static function Get() {
            if ( !self::$Initialized ) {
                return null;
            }

            $mf = new MailFactory( self::$params["senderEmail"]
                    , self::$params["senderName"]
                    , self::$params["charset"]
                    , self::$params["xMailer"]
            );

            if ( !empty( self::$params["bcc"] ) ) {
                $mf->AddBCC( self::$params["bcc"] );
            }

            return $mf;
        }
    }
?><?php
    /**
     * Memcache Helper
     * @link        http://ru2.php.net/memcache
     *
     * See $defaultParams for module params
     * Example:
     *     <memcache class="MemcacheHelper">
     *        <servers autocompress="true" active="true">
     *           <server host="127.0.0.1" active="true" />
     *           <server host="127.0.0.1" active="true" />
     *           <server host="127.0.0.1" active="false" />
     *       </servers>
     *     </memcache>
     *
     * @desc        Module Parameters
     * @package     Eaze
     * @subpackage  Modules
     * @static
     * @author      s.bykov
     * @author      m.grigoriev
     */

    class MemcacheHelper implements IModule {

        /**
         * Controls the minimum value length before attempting to compress automatically
         */
        const AutoCompressThreshold = 20000;

        /**
         * Specifies the minimum amount of savings to actually store the value compressed.
         * The supplied value must be between 0 and 1.
         * Default value is 0.2 giving a minimum 20% compression savings.
         */
        const AutoCompressMinSaving = 0.2;

        /**
         * Expiration time of the item. If it's equal to zero, the item will never expire.
         * You can also use Unix timestamp or a number of seconds starting from current time,
         * but in the latter case the number of seconds may not exceed 2592000 (30 days).
         */
        const CacheDefaultExpire = 3600;

        /**
         * Same expiration time for block keys.
         */
        const CacheKeyDefaultExpire = 30;

        /**
         * Counter for get requests.
         *
         * @var int
         */
        public static $TotalGetRequests = 0;

        /**
         * Counter for set request including add/set/replace/delete/increment/decrement methods too.
         *
         * @var int
         */
        public static $TotalSetRequests = 0;

        /**
         * Default params for servers and client. See memcache::addServer() for details
         *
         * @var array
         */
        private static $defaultParams = array(
            'server' => array(
                'host'              => '127.0.0.1'
                , 'port'            => 11211
                , 'active'          => false
                , 'persistent'      => false
                , 'weight'          => 1
                , 'timeout'         => 1
                , 'retryInterval'   => 15
                , 'status'          => true
                , 'failureCallback' => 'MemcacheHelper::FailureCallback'
            )
            , 'client' => array(
                'autocompress' => false
                , 'compress'   => false
                , 'active'     => false
                , 'hostKey'    => null
            )
        );

        /**
         * Servers params
         *
         * @var array
         */
        private static $serversParams = array();

        /**
         * Client params
         * @var array
         */
        private static $clientParams = array();

        /**
         * Memcache connection state
         *
         * @var bool
         */
        private static $isActive = false;

        /**
         * Initialized flag
         *
         * @var bool
         */
        private static $initialized = false;

        /**
         * Memcache instance
         *
         * @var Memcache
         */
        private static $memcache;

        /**
         * Key prefix
         *
         * @var null|string
         */
        private static $keyPrefix;


        /**
         * Module initialization.
         *
         * @static
         * @param DOMNodeList $params
         * @return null
         */
        public static function Init( DOMNodeList $params ) {
            $serversNode = $params->item( 0 );

            foreach ( $serversNode->attributes as $attribute ) {
                self::$clientParams [$attribute->name] = $attribute->value;
            }

            /** @var $serverDOMElement DOMElement */
            /** @var $serverAttribute DOMAttr */
            foreach ( $serversNode->childNodes as $serverDOMElement ) {
                $server = array();
                if ( !empty( $serverDOMElement->attributes ) ) {
                    foreach ( $serverDOMElement->attributes as $serverAttribute ) {
                        $server[$serverAttribute->name] = $serverAttribute->value;
                    }
                }

                self::$serversParams[] = $server + self::$defaultParams['server'];
            }

            if ( empty( self::$clientParams['active'] ) || self::$clientParams['active'] == 'false' ) {
                self::$isActive = false;
                Logger::Info( 'Memcache support is disabled by the sites.xml' );
            } else {
                self::$isActive = true;

                if ( !class_exists( 'Memcache' ) ) {
                    self::$isActive = false;
                    Logger::Warning( 'Memcache module is not installed' );
                }
            }

            self::$initialized = true;
            self::$keyPrefix   = !empty( self::$clientParams['hostKey'] ) ? self::$clientParams['hostKey'] : sprintf( '%s_', substr( Host::GetCurrentKey(), 0, 5 ) );

            self::connect();

            return null;
        }


        /**
         * Getter for memcache connection state.
         *
         * @static
         * @return bool
         */
        public static function IsActive() {
            return self::$isActive;
        }


        /**
         * Getter for key prefix.
         *
         * @static
         * @return string
         */
        public static function GetKeyPrefix() {
            return self::$keyPrefix;
        }


        /**
         * Callback error handler for memcache.
         *
         * @static
         * @param string $host
         * @param int    $tcpPort
         * @param int    $udpPort
         * @param int    $errorNumber
         * @param string $errorMessage
         */
        public static function FailureCallback( $host = null, $tcpPort = 0, $udpPort = 0, $errorNumber = 0, $errorMessage = null ) {
            Logger::Warning( 'Memcache %s:%d (%d) failed with %d:%s', $host, $tcpPort, $udpPort, $errorNumber, $errorMessage );
        }


        /**
         * Prepare key for memcache.
         *
         *
         * @static
         * @param string $key
         * @return string
         */
        public static function PrepareKey( $key ) {
            return sprintf( '%s%s', self::GetKeyPrefix(), md5( $key ) );
        }


        /**
         * Get value from the server.
         *
         * @param  string|array  $key  key parameter
         * @param bool           $prepareKey
         * @return mixed
         */
        public static function Get( $key, $prepareKey = true ) {
            if ( !self::IsActive() || empty( $key ) ) {
                return false;
            }
            self::$TotalGetRequests++;
            Logger::Debug( 'Get value with key %s', is_array( $key ) ? implode( '; ', $key ) : $key );

            if ( $prepareKey ) {
                if ( is_array( $key ) ) {
                    foreach ( $key as &$k ) {
                        $k = self::PrepareKey( $k );
                    }
                } else {
                    $key = self::PrepareKey( $key );
                }
            }

            return self::$memcache->get( $key );
        }


        /**
         *  Set item to the server (add|set|replace).
         *
         * @param string      $operation   add, set or replace operation
         * @param string      $key         key parameter
         * @param string      $value       value
         * @param int         $flag        Use MEMCACHE_COMPRESSED to store the item compressed (uses zlib). Default 0
         * @param int         $expire      expiration date, default is 3600*
         * @param bool        $prepareKey
         * @return bool
         */
        private static function setValue( $operation, $key, $value, $flag = 0, $expire = self::CacheDefaultExpire, $prepareKey = true ) {
            if ( !in_array( $operation, array( 'add', 'set', 'replace') ) || !self::IsActive() || empty( $key ) ) {
                return false;
            }

            self::$TotalSetRequests++;
            $flag = self::checkCompressCompatibility( $value ) && $flag == MEMCACHE_COMPRESSED ? MEMCACHE_COMPRESSED : 0;

            if ( $prepareKey ) {
                $key = self::PrepareKey( $key );
            }

            Logger::Debug( '%s value with key %s, flag %d, expire %d', $operation, $key, $flag, $expire );
            return self::$memcache->$operation( $key, $value, $flag, $expire );
        }


        /**
         *  Add an item to the server.
         *
         * @param string      $key         key parameter
         * @param string      $value       value
         * @param int         $flag        Use MEMCACHE_COMPRESSED to store the item compressed (uses zlib). Default 0
         * @param int         $expire      expiration date, default is 3600*
         * @param bool        $prepareKey
         * @return bool
         */
        public static function AddValue( $key, $value, $flag = 0, $expire = self::CacheDefaultExpire, $prepareKey = true ) {
            return self::setValue( 'add', $key, $value, $flag, $expire, $prepareKey );
        }


        /**
         * Set value
         *
         * @param string   $key     key parameter
         * @param string   $value   value
         * @param int      $flag    Use MEMCACHE_COMPRESSED to store the item compressed (uses zlib). Default 0
         * @param int      $expire  expiration date, default is 3600
         * @param bool     $prepareKey
         * @return mixed
         */
        public static function Set( $key, $value, $flag = 0, $expire = self::CacheDefaultExpire, $prepareKey = true ) {
            return self::setValue( 'set', $key, $value, $flag, $expire, $prepareKey );
        }


        /**
         * Replace value
         *
         * @param  string  $key     key parameter
         * @param  string  $value   value
         * @param  int     $flag    Use MEMCACHE_COMPRESSED to store the item compressed (uses zlib). Default 0
         * @param  int     $expire  expiration date, default is 3600
         * @param bool     $prepareKey
         * @return bool
         */
        public static function Replace( $key, $value, $flag = 0, $expire = self::CacheDefaultExpire, $prepareKey = true ) {
            return self::setValue( 'replace', $key, $value, $flag, $expire, $prepareKey );
        }


        /**
         * Increment value by key
         *
         * @static
         * @param string $key key parameter
         * @param bool   $prepareKey
         * @return int|false
         */
        public static function Increment( $key, $prepareKey = true ) {
            if ( !self::IsActive() || empty( $key ) ) {
                return false;
            }
            self::$TotalSetRequests++;

            if ( $prepareKey ) {
                $key = self::PrepareKey( $key );
            }

            Logger::Debug( 'Increment with key %s', $key );
            return self::$memcache->increment( $key );
        }


        /**
         * Decrement value by key.
         *
         * @static
         * @param string $key       key parameter
         * @param bool   $prepareKey
         * @return int|false
         */
        public static function Decrement( $key, $prepareKey = true ) {
            if ( !self::IsActive() || empty( $key ) ) {
                return false;
            }
            self::$TotalSetRequests++;

            if ( $prepareKey ) {
                $key = self::PrepareKey( $key );
            }

            Logger::Debug( 'Decrement with key %s', $key );
            return self::$memcache->decrement( $key );
        }


        /**
         * Try to add block key, if block key already exists return false.
         *
         * @static
         * @param  string     $key     key parameter
         * @param  int        $value   value
         * @param  int        $expire  expiration date, default is 3600
         * @return bool|void
         */
        public static function AddBlock( $key, $value = 1, $expire = self::CacheKeyDefaultExpire ) {
            if ( !self::IsActive() || empty( $key ) ) {
                return false;
            }

            $blockKey = sprintf( '%s_block', self::PrepareKey( $key ) );
            return self::$memcache->add( $blockKey, $value, 0, $expire  );
        }


        /**
         * Getting blocking key status.
         *
         * @static
         * @param  string  $key  key parameter
         * @return bool
         */
        public static function IsBlocked( $key ) {
            if ( !self::IsActive() || empty( $key ) ) {
                return false;
            }

            $blockKey = sprintf( '%s_block', self::PrepareKey( $key ) );
            $result   = self::$memcache->get( $blockKey );

            return ( $result !== false ) ? true : false;
        }


        /**
         * Delete blocking key.
         *
         * @static
         * @param  string      $key  key parameter
         * @return bool
         */
        public static function DeleteBlock( $key ) {
            if ( !self::IsActive() || empty( $key ) ) {
                return false;
            }

            $blockKey = sprintf( '%s_block', self::PrepareKey( $key ) );
            return self::$memcache->delete( $blockKey );
        }


        /**
         * Delete Value By Key
         *
         * @param  string     $key
         * @param  int|string $timeout Execution time of the item. If it's equal to zero, the item will be deleted right away whereas if you set it to 30, the item will be deleted in 30 seconds.
         * @return bool
         */
        public static function Delete( $key, $timeout = 0 ) {
            if ( !self::IsActive() || empty( $key ) ) {
                return false;
            }

            self::$TotalSetRequests++;

            Logger::Debug( 'Delete with key %s, timeout: %d', $key, $timeout );
            return self::$memcache->delete( self::PrepareKey( $key ), $timeout );
        }


        /**
         * Flush All Keys.
         *
         * @return bool
         */
        public static function Flush() {
            if ( !self::IsActive() ) {
                return false;
            }

            Logger::Debug( 'Flushing' );
            return self::$memcache->flush();
        }


        /**
         * Get memcache version.
         *
         * @static
         * @return string
         */
        public static function GetVersion() {
            if ( !self::IsActive() ) {
                return false;
            }

            return self::$memcache->getVersion();
        }


        /**
         * Get server stats.
         *
         * @return array
         */
        public static function GetStats() {
            if ( !self::IsActive() ) {
                return false;
            }

            return self::$memcache->getStats();
        }


        /**
         * Close memcache connection.
         *
         * @static
         * @return bool
         */
        public static function Close() {
            if ( !self::IsActive() ) {
                return false;
            }

            return self::$memcache->close();
        }


        /**
         * Compare Tag Versions.
         *
         * @param  array $tags1
         * @param  array $tags2
         * @return bool  true if equals
         */
        public static function CompareTags( $tags1, $tags2 ) {
            if ( count( $tags1 ) != count( $tags2 ) ) {
                return false;
            }

            $tags1 = empty( $tags1 ) ? array() : $tags1;
            $tags2 = empty( $tags2 ) ? array() : $tags2;

            foreach ( $tags1 as $tag1Key => $tag1Value ) {
                if ( empty( $tags2[$tag1Key] ) || $tags2[$tag1Key] != $tag1Value ) {
                    return false;
                }
            }

            return true;
        }


        /**
         * Add memcache server to the connection pool.
         *
         * @static
         * @return bool
         */
        private static function connect() {
            if ( !self::IsActive() ) {
                return false;
            }

            self::$memcache = new Memcache();
            self::$isActive = false;

            foreach ( self::$serversParams as $server ) {
                if ( $server['active'] == 'true' || $server['active'] === true ) {
                    $isAdded = self::$memcache->addServer(
                        $server['host']
                        , $server['port']
                        , $server['persistent']
                        , $server['weight']
                        , $server['timeout']
                        , $server['retryInterval']
                        , $server['status']
                        , $server['failureCallback']
                    );

                    self::$isActive = $isAdded === true ? true : self::$isActive;
                }
            }

            if ( self::IsActive() ) {
                if ( !empty( self::$clientParams['autocompress'] ) || self::$clientParams['active'] != 'false' ) {
                    self::$memcache->setCompressThreshold( self::AutoCompressThreshold, self::AutoCompressMinSaving );
                }
            } else {
                Logger::Warning( 'All memcache servers were marked as an inactive' );
            }

            return true;
        }


        /**
         * Check data type. If data has type boolean, integer or float
         * compressing is not supported.
         *
         * @static
         * @param  mixed $value  value
         * @return bool
         */
        private static function checkCompressCompatibility( $value ) {
            return !( !self::$clientParams['compress'] || is_bool( $value ) || is_int( $value ) || is_float( $value ) );
        }

    }
?>