<?php
    /**
     * Database connection for MySQL
     *
     * @package Eaze
     * @subpackage MySql
     * @author max3.05, sergeyfast
     */
    class MySqlConnection implements IConnection {

        /**
         * Complex Type Mapping
         * @var array
         */
        public static $ComplexTypeMapping = array(
            'php'    => 'DbTypePhpArray'
            , 'json' => 'DbTypeJsonArray'
        );

        /**
         * Array of Complex Types
         * @var IComplexType[]
         */
        private static $complexTypes = array();

        /**
         * MySQL database server host
         *
         * @var string
         */
        private $host = 'localhost';

        /**
         * MySQL database server port
         *
         * @var string
         */
        private $port = '3306';

        /**
         * MySQL database user
         *
         * @var string
         */
        private $user = 'root';

        /**
         * MySQL database user password
         *
         * @var string
         */
        private $password = '';

        /**
         * MySQL database name
         *
         * @var string
         */
        private $dbname = 'mysql';

        /**
         * Connection charset
         *
         * @var string
         */
        private $charset = 'UTF8';

        /**
         * Use pconnect instead of connect
         *
         * @var boolean
         */
        private $isPersistent = false;

        /**
         * Connection instance resource
         *
         * @var resource
         */
        private $connection;

        /**
         * Converter for MySQL values
         *
         * @var MySqlConvert
         */
        private $converter;

        /**
         * Is in Transaction
         *
         * @var bool
         */
        private $isTransaction = false;

        /**
         * Eaze Connection Name
         * @var string
         */
        private $name;


        /**
         * Executes specified query and return result DataSet
         *
         * @param string $query  Sql query to execute.
         * @return MySqlDataSet Result DataSet.
         */
        public function ExecuteQuery( $query ) {
            if ( !is_resource( $this->connection ) ) {
                $this->open();
            }

            $resource = mysql_query( $query, $this->connection );
            if ( $resource === false ) {
                Logger::Error( $this->getLastError() );
            }

            return new MySqlDataset( $resource, $this );
        }


        /**
         * Execute Sql query and return result statement/
         *
         * @param string $query Sql query to execute.
         * @return boolean Return <code>true</code> if command executed successfully, otherwise return <code>false</code>.
         */
        public function ExecuteNonQuery( $query ) {
            if ( !is_resource( $this->connection ) ) {
                $this->open();
            }

            if ( ! is_resource( $this->connection ) ) {
                return false;
            }

            $resource = mysql_query( $query, $this->connection );
            return !empty( $resource );
        }


        /**
         * Starts transaction.
         */
        public function Begin() {
            $this->isTransaction = $this->executeNonQuery( 'BEGIN' );
        }


        /**
         * Commits current transaction.
         */
        public function Commit() {
            $this->ExecuteNonQuery( 'COMMIT' );
            $this->isTransaction = false;
        }


        /**
         * Rollbacks current transaction.
         */
        public function Rollback() {
            $this->ExecuteNonQuery( 'ROLLBACK' );
            $this->isTransaction = false;
        }


        /**
         * Determines if transaction started.
         *
         * @return bool Return <code>true</code> if current connection is in transaction, otherwise <code>false</code>
         */
        public function IsTransaction() {
            return $this->isTransaction;
        }


        /**
         * Quote String
         * like prepare
         *
         * @param string $str
         * @return string
         */
        public function Quote( $str ) {
            return $this->converter->Quote( $str );
        }

        /**
         * Checks if current connection instance is opened.
         *
         * @return boolean  <code>True</code> if connection is opened, otherwise <code>false</code>.
         */
        public function IsOpened() {
            return is_resource( $this->connection );
        }

        /**
         * Gets last error message string of the connection.
         *
         * @return string Last message error string if the connection.
         */
        public function GetLastError() {
            if ( is_resource( $this->connection ) ) {
                return mysql_error( $this->connection );
            }

            return 'Connection was not opened yet';
        }


        /**
         * Opens connection using specified parameters
         *
         * @return boolean <code>True</code> if the connection was opened successfully, otherwise <code>false</code>.
         */
        public function Open() {
            if (!empty( $this->isPersistent ) ) {
                $this->connection = mysql_pconnect( $this->host . ':' . $this->port, $this->user, $this->password );
            } else {
                $this->connection = mysql_connect( $this->host . ':' . $this->port, $this->user, $this->password );
            }

            if ( !($this->connection) ) {
                return false;
            }

            $result = true;
            if ( !empty( $this->dbname ) ) {
                $result = mysql_select_db( $this->dbname, $this->connection );
            }
            if ( false === $result ) {
                mysql_close( $this->connection );
                $this->connection = null;
                return false;
            }

            if ( ! empty( $this->charset ) ) {
                $result = $this->executeNonQuery( 'SET NAMES ' . $this->converter->ToString( $this->charset ) );
                if ( ! $result ) {
                    Logger::Warning( 'Charset %s was not found. Previous charset kept', $this->charset );
                }
            }

            return true;
        }


        /**
         * Close current connection
         */
        public function Close() {
            if ( is_resource( $this->connection ) ) {
                mysql_close( $this->connection );
                $this->connection = null;
            }
        }


        /**
         * Initializes MySqlConnection instance
         *
         * @param string $host      Database server host
         * @param string $port      Database server port
         * @param string $dbname    Database name
         * @param string $user      Database user
         * @param string $password  Database user password
         * @param null $charset
         * @param bool $isPersistent
         * @param string $name
         * @return MySqlConnection
         *
         */
        public function __construct( $host       = null
                                     , $port     = null
                                     , $dbname   = null
                                     , $user     = null
                                     , $password = null
                                     , $charset  = null
                                     , $isPersistent = false
                                     , $name         = null ) {
             $this->host         = $host;
             $this->port         = $port;
             $this->dbname       = $dbname;
             $this->user         = $user;
             $this->password     = $password;
             $this->isPersistent = $isPersistent;
             $this->name         = $name;

             if ( !empty( $charset ) ) {
                $this->charset  = $charset;
             }

             $this->converter = new MySqlConvert( $this );
        }


        /**
         * Get Complex Type
         * @param  string $alias  (e.g. php, json, int[], string[], hstore)
         * @return IComplexType
         */
        public function GetComplexType( $alias ) {
            if ( empty( self::$ComplexTypeMapping[$alias] ) ) {
                return null;
            }

            if ( empty( self::$complexTypes[$alias] ) ) {
                self::$complexTypes[$alias] = new self::$ComplexTypeMapping[$alias]( $this->converter );
            }

            return self::$complexTypes[$alias];
        }


        /**
         * Get SqlConverter
         * @return MySqlConvert
         */
        public function GetConverter() {
            return $this->converter;
        }


        /**
         * Get Connection Resource
         * @return resource
         */
        public function GetResource() {
            return $this->connection;
        }


        /**
         * Get Connection Name
         * @return string
         */
        public function GetName() {
            return $this->name;
        }


        /**
         * Returns ClassName
         * @return string
         */
        public function GetClassName() {
            return __CLASS__;
        }
    }
?><?php
    /**
     * MySQL Type Converter
     * 
     * @package Eaze
     * @subpackage MySql
     * @author max3.05, sergeyfast
     */
    class MySqlConvert implements ISqlConvert {

        /**
         * Connection
         * @var MySqlConnection
         */
        private static $connection;

        /**
         * Create MySqlConvert
         * @param IConnection $connection
         */
        public function __construct( IConnection $connection ) {
            if ( empty( self::$connection ) || self::$connection != $connection ) {
                self::$connection = $connection;
            }
        }


        /**
         * Convert Null Value to String
         * @static
         * @param  $value
         * @return string
         */
        public static function NullToString( $value ) {
            if ( $value === null ) {
                return 'null';
            }
            
            return $value;   
        }
        
        
        /**
         * Converts given argument to sql string.
         * @static
         * @param mixed $value  Value to convert
         * @return string
         */
        public static function ToString( $value ) {
            static $firstTry;

            $value = Convert::ToString( $value );
            if ( $value === null ) {
                return 'null';
            }

            if ( $firstTry === null && !empty( self::$connection ) && !self::$connection->IsOpened() ) {
                $firstTry = !self::$connection->Open();
            }

            if ( empty( self::$connection ) || self::$connection->GetResource() === null ) {
                Logger::Error( 'Connection was not found ' );
                return null;
            }

            $sqlString = "'" . mysql_real_escape_string( $value, self::$connection->GetResource() )  . "'";
           
            return $sqlString;
        }
        
        
        /**
         * Converts given argument to sql integer.
         *
         * @param mixed $value  Value to convert
         * @return string
         */
        public static function ToInt( $value ) {
            return self::NullToString( Convert::ToInteger( $value ) );
        }
        
        
        /**
         * Converts given argument to sql integer.
         *
         * @param mixed $value  Value to convert
         * @return string
         */
        public static function ToInteger( $value ) {
            return self::NullToString( Convert::ToInteger( $value ) );
        }
        
        
        /**
         * Converts given argument to sql double.
         *
         * @param mixed $value  Value to convert
         * @return string
         */
        public static function ToDouble( $value ) {
            return self::NullToString( Convert::ToDouble( $value ) );
        }
        
        
        /**
         * Converts given argument to sql float.
         *
         * @param float $value
         * @return string
         */
        public static function ToFloat( $value ) {
            return self::NullToString( Convert::ToFloat( $value ) );
        }
        
        
        /**
         * Converts given argument to sql boolean.
         *
         * @param mixed $value  Value to convert
         * @return string
         */
        public static function ToBoolean( $value ) {
            if ( $value === true || $value == 'true' ) {
                return '1';
            } else if ( $value === false || $value == 'false' ) {
                return '0';
            }

            return 'null';
        }


        /**
         * Converts given argument to sql datetime.
         *
         * @param mixed $value  Value to convert
         * @param string $format
         * @return string
         */
        public static function ToDateTime( $value, $format = 'Y-m-d H:i:s' ) {
            $value = Convert::ToDateTime( $value );
            if ( $value == null ) {
                return 'null';
            }

            return self::ToString( $value->format( $format ) );
        }


        /**
         * Converts given argument to sql date.
         *
         * @param mixed $value  Value to convert
         * @param string $format
         * @return string
         */
        public static function ToDate( $value, $format = 'Y-m-d' ) {
            return self::ToDateTime( $value, $format);
        }


        /**
         * Converts given argument to sql time.
         *
         * @param mixed $value  Value to convert
         * @param string $format
         * @return string
         */
        public static function ToTime( $value, $format = 'G:i:s' ){
            return self::ToDateTime( $value, $format);
        }        
        

        /**
         * Converts given argument from sql string.
         *
         * @param mixed $value  Value to convert
         * @return string
         */
        public static function FromString( $value ) {
            return Convert::ToString( $value );
        }
        
        
        /**
         * Converts given argument from sql integer.
         *
         * @param mixed $value  Value to convert
         * @return integer
         */
        public static function FromInt( $value ) {
            return Convert::ToInteger( $value );
        }
        
        
        /**
         * Converts given argument from sql integer.
         *
         * @param mixed $value  Value to convert
         * @return integer
         */
        public static function FromInteger( $value ) {
            return Convert::ToInteger( $value );
        }
        
        
        /**
         * Converts given argument from sql double.
         *
         * @param mixed $value  Value to convert
         * @return double
         */
        public static function FromDouble( $value ) {
            return Convert::ToDouble( $value );
        }
        
        
        /**
         * Converts given argument from sql float.
         *
         * @param mixed $value  Value to convert
         * @return float
         */
        public static function FromFloat( $value ) {
            return Convert::ToFloat( $value );
        }
        
        
        /**
         * Converts given argument from sql boolean.
         *
         * @param mixed $value  Value to convert
         * @return boolean
         */
        public static function FromBoolean( $value ) {
            $value = Convert::ToInteger( $value );
            
            switch ( $value ) {
                case 1:
                case '1':
                    return true;
                case 0:
                case '0':
                    return false;
                default:
                    return null;
            }
        }

        
        /**
         * Converts given argument from sql Datetime.
         *
         * @param mixed $value  Value to convert
         * @return Datetime
         */
        public static function FromDateTime( $value ) {
            return Convert::ToDateTime( $value );
        }
        
		/**
         * Converts given argument from sql Datetime.
         *
         * @param mixed $value  Value to convert
         * @return Datetime
         */
        public static function FromDate( $value ) {
            return Convert::ToDateTime( $value );
        }


        /**
         * Converts given argument from sql parameters.
         *
         * @param mixed $value  Value to convert
         * @param $type
         * @return mixed
         */
        public static function FromParameter( $value, $type ) {
            switch ( $type ) {
                case TYPE_INTEGER:
                    return self::FromInteger( $value  );
                case TYPE_FLOAT:
                    return self::FromFloat( $value );
                case TYPE_BOOLEAN:
                    return self::FromBoolean( $value );
                case TYPE_STRING:
                    return self::FromString( $value );
                case TYPE_DATETIME:
                case TYPE_DATE:
                case TYPE_TIME:
                    return self::FromDateTime( $value );
                case TYPE_LTREE:
                    return self::FromString( $value );
                default:
                    Logger::Error( 'Cannot call converter for %s of class MySqlConvert', $type);
                    return null;
            }
        }


        /**
         * Converts given argument to sql in expression.
         *
         * @param mixed $value  value to convert
         * @param string $type  type of the values in the array.
         * @return string
         */
        public static function ToList( $value, $type = TYPE_STRING ) {
            $method = 'To' . $type;

            if ( !is_callable( array( __CLASS__ , $method ) ) ) {
                Logger::Error( 'Call to undefined method %s', $method );
            }

            $items = Convert::ToArray( $value );
            $items = array_map( array( __CLASS__ , $method ), $items) ;

            return '(' . implode( ',', $items ) . ')';
        }


        /**
         * Quote Database Object
         *
         * @param string $field
         * @return string
         */
        public static function Quote( $field ) {
            return '`' . $field . '`';
        }
    }
?><?php
    /**
     * Helps to manage sets of MySQL database data resources.
     *
     * @package Eaze
     * @subpackage MySql
     * @author  max3.05
     */
    class MySqlDataSet extends DataSet {

        /**
         * Initializing instance.
         *
         * @param resource $resource  mysql resource.
         * @param IConnection $connection
         * @return MySqlDataSet
         */
        public function __construct(  $resource, IConnection $connection ){
            if ( is_resource( $resource ) ) {
                $this->size     = mysql_num_rows( $resource );
                $this->resource = $resource;
                
                $i = mysql_num_fields( $resource );
                for ( $j = 0; $j < $i; $j++ ) {
                    $name = mysql_field_name( $resource, $j);
                    $this->Columns[$name] = $name;
                }

                parent::__construct( $connection );
            }
        }
        
        
        /**
         * Sets the cursor to a next element.
         *
         * @return boolean  <code>ture</code> if cursor moved to the next element, otherwise <code>false</code>
         */
        public function Next() {
            if ( !parent::Next() ) {
                return false;
            }
            
            mysql_data_seek( $this->resource, $this->cursor );
            if ( empty( $this->data[$this->cursor] ) ) {
                $this->data[$this->cursor] = mysql_fetch_array( $this->resource, MYSQL_BOTH );
            }

            return true;
        }
        
        
        /**
         * Sets the cursor to a previous element.
         *
         * @return boolean  <code>true</code> if cursor moved to the previous element, otherwise <code>false</code>
         */
        public function Previous() {
            $this->cursor--;
            
            if ( $this->cursor > -1 ) {
                mysql_data_seek( $this->resource, $this->cursor );
                
                if ( true == empty( $this->data[$this->cursor] ) ) {
                    $this->data[$this->cursor] = mysql_fetch_array( $this->resource, MYSQL_BOTH );
                }

                return true;
            }
            
            $this->cursor++;
            return false;
        }


        /**
         * Gets parameters of the current row and specified field as it.
         *
         * @param string $name  Field name.
         * @return string  Field value of the current row.
         */
        public function GetParameter( $name ) {
            if ( isset( $this->data[$this->cursor][$name] ) ) {
                return $this->data[$this->cursor][$name];
            }

            return null;
        }
        

        /**
         * Returns the string from the hash.
         *
         * @param  string $name  the parameter name
         * @return the parameter value
         */
        public function GetString( $name ) {
            $param = $this->GetParameter( $name );
            
            return MySqlConvert::FromString( $param );
        }
    
        
        /**
         * Returns the integer from the hash.
         *
         * @param  string $name  the parameter name
         * @return the parameter value
         */
        public function GetInteger( $name ) {
            $param = $this->GetParameter( $name );
            
            return MySqlConvert::FromInteger( $param );
        }
        
        
        /**
         * Returns the float from the hash.
         *
         * @param  string $name  the parameter name
         * @return the parameter value
         */
        public function GetFloat( $name ) {
            $param = $this->GetParameter( $name );
                        
            return MySqlConvert::FromFloat( $param );
        }
        
        
        /**
         * Returns the double from the hash.
         *
         * @param  string $name  the parameter name
         * @return the parameter value
         */
        public function GetDouble( $name ) {
            $param = $this->GetParameter( $name );
                        
            return MySqlConvert::FromDouble( $param );
        }
    
        
        /**
         * Returns the boolean from the hash.
         *
         * @param  string $name  the parameter name
         * @return the parameter value
         */
        public function GetBoolean( $name ) {
            $param = $this->GetParameter( $name );
            
            return MySqlConvert::FromBoolean( $param );
        }
    
    
        /**
         * Returns the datetime parameter from hash.
         *
         * @param string $name  Field name.
         * @return Datetime
         */
        function GetDateTime( $name ) {
            $param = $this->getParameter( $name );
                        
            return MySqlConvert::FromDateTime( $param );
        }

        /**
         * @param string $name
         * @param string $type
         * @return mixed
         */
        public function GetValue( $name, $type = TYPE_STRING ) {
            return MySqlConvert::FromParameter( $this->data[$this->cursor][$name], $type );
        }
    }
?>