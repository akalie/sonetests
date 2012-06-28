<?php
    /**
     * Database connection for PostgreSQL
     *
     * @package Eaze
     * @subpackage PgSql
     * @author max3.05, sergeyfast
     */
    class PgSqlConnection implements IConnection {

        /**
         * Complex Type Mapping
         * @var array
         */
        public static $ComplexTypeMapping = array(
            'php'        => 'DbTypePhpArray'
            , 'json'     => 'DbTypeJsonArray'
            , 'int[]'    => 'PgSqlTypeIntArray'
            , 'float[]'  => 'PgSqlTypeFloatArray'
            , 'string[]' => 'PgSqlTypeStringArray'
            , 'hstore'   => 'PgSqlTypeHStoreArray'
            , 'point'    => 'PgSqlTypePoint'
        );


        /**
         * Array of Complex Types
         * @var IComplexType[]
         */
        private static $complexTypes = array();


        /**
         * PostgreSQL database server host
         *
         * @var string
         */
        private $host = 'localhost';

        /**
         * PostgreSQL database server port
         *
         * @var int
         */
        private $port = 5432;

        /**
         * PostgreSQL database user
         *
         * @var string
         */
        private $user = 'postrges';

        /**
         * PostgreSQL database user password
         *
         * @var string
         */
        private $password = '';

        /**
         * PostgreSQL database name
         *
         * @var string
         */
        private $dbname = 'postrges';

        /**
         * Connection charset
         *
         * @var string
         */
        private $charset = null;

        /**
         * Connection instance resource
         *
         * @var resource
         */
        private $connection;

        /**
         * Use pconnect instead of connect
         * 
         * @var boolean
         */        
        private $isPersistent = false;        
        
        /**
         * Converter for PostgreSQL values
         *
         * @var PgSqlConverter
         */
        private $converter;

        /**
         * Eaze Connection Name
         * @var string
         */
        private $name;


        /**
         * Form connection string from the connection parameters.
         *
         * @return string Connection string for the connection.
         */
        public function GetConnectionString() {
            $connectionString = sprintf( 'host=%s port=%s dbname=%s user=%s password=%s', $this->host, $this->port, $this->dbname, $this->user, $this->password );

            return $connectionString;
        }


        /**
         * Executes specified query and return result DataSet.
         *
         * @param string $query  Sql query to execute.
         * @return PgSqlDataSet Result DataSet.
         */
        public function ExecuteQuery( $query ) {
            if ( !is_resource( $this->connection ) ) {
                $this->open();
            }

            /** @var resource $resource  */
            $resource = pg_exec( $this->connection, $query );

            return new PgSqlDataset( $resource, $this );
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

            $resource = pg_query( $this->connection, $query );

            return !empty( $resource );
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
         * Starts transaction.
         * @return bool
         */
        public function Begin() {
            return $this->executeNonQuery( 'BEGIN TRANSACTION' );
        }


        /**
         * Commits current transaction.
         * @return bool
         */
        public function Commit() {
            return $this->executeNonQuery( 'COMMIT TRANSACTION' );
        }


        /**
         * Rollbacks current transaction.
         * @return bool
         */
        public function Rollback() {
            return $this->executeNonQuery( 'ROLLBACK TRANSACTION' );
        }


        /**
         * Determines if transaction started.
         *
         * @return bool Return <code>true</code> if current connection is in transaction, otherwise <code>false</code>
         */
        public function IsTransaction() {
            return ( PGSQL_TRANSACTION_IDLE != pg_transaction_status( $this->connection ) );
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
         * Gets last error message string of the connection.
         *
         * @return string Last message error string if the connection.
         */
        public function GetLastError() {
            if ( is_resource( $this->connection ) ) {
                return pg_last_error( $this->connection );
            }

            return 'Connection was not opened yet';
        }

        /**
         * Opens connection using specified parameters
         * @return bool
         */
        public function Open() {
            if ( is_resource( $this->connection ) ) {
                return true;
            }

            $connectionString = $this->getConnectionString();

            if ( !empty( $this->isPersistent ) ) {
                $this->connection = pg_pconnect( $connectionString );
            } else {
                $this->connection = pg_connect( $connectionString );
            }

            if ( !empty( $this->charset ) ) {
                $result = $this->executeNonQuery( 'SET CLIENT_ENCODING TO ' . $this->charset );

                if ( !$result ) {
                    Logger::Warning(  'Charset %s was not found. Previous charset kept', $this->charset );
                }
            }

            return is_resource( $this->connection );
        }


        /**
         * Close current connection
         * @return bool
         */
        public function Close() {
            if ( is_resource( $this->connection ) ) {
                return pg_close( $this->connection );
            }

            return false;
        }


        /**
         * Get SqlConverter
         * @return PgSqlConvert
         */
        public function GetConverter() {
            return $this->converter;
        }


        /**
         * Initializes PgSqlConnection instance
         *
         * @param string $host      Database server host
         * @param string $port      Database server port
         * @param string $dbname    Database name
         * @param string $user      Database user
         * @param string $password  Database user password
         * @param string $charset
         * @param bool   $isPersistent Use pconnect instead of connect
         * @param string $name eaze connection name
         * @return PgSqlConnection
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
            $this->host         = !empty( $host )     ? $host     : 'localhost';
            $this->port         = !empty( $port )     ? $port     : 5432;
            $this->dbname       = !empty( $dbname )   ? $dbname   : 'postgres';
            $this->user         = !empty( $user )     ? $user     : 'postgres';
            $this->password     = !empty( $password ) ? $password : '';
            $this->charset      = !empty( $charset )  ? $charset  : '';
            $this->isPersistent = $isPersistent;
            $this->name         = $name;
            $this->converter    = new PgSqlConvert( $this );
        }


        /**
         * Get Complex Type
         * @param  string $alias  (e.g. php, json, int[], string[], hstore)
         * @return IComplexType
         */
        public function GetComplexType( $alias  ) {
            if ( empty( self::$ComplexTypeMapping[$alias] ) ) {
                return null;
            }

            if ( empty( self::$complexTypes[$alias] ) ) {
                self::$complexTypes[$alias] = new self::$ComplexTypeMapping[$alias]( $this->converter );
            }

            return self::$complexTypes[$alias];
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
     * PostgreSQL Type Converter
     * 
     * @package Eaze
     * @subpackage PgSql
     * @author sergeyfast, max3.05
     */
    class PgSqlConvert implements ISqlConvert  {

        /**
         * Connection
         * @var PgSqlConnection
         */
        private static $connection;

        /**
         * Create PgSqlConvert
         * @param IConnection $connection
         */
        public function __construct( IConnection $connection ) {
            if ( empty( self::$connection ) ) {
                self::$connection = $connection;
            }
        }


        /**
         * Null To String
         * @static
         * @param mixed $value
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
         *
         * @param mixed $value  Value to convert
         * @return string
         */
        public static function ToString( $value ) {
            $value = Convert::ToString( $value );
            
            if ( $value === null ) {
                return 'null';
            }

            $sqlString = "'" . pg_escape_string( $value ) . "'";
           
            return $sqlString;
        }
        
        
        /**
         * Converts given argument to sql integer.
         *
         * @param mixed $value  Value to convert
         * @return string
         */
        public static function ToInt( $value ){
            return self::NullToString( Convert::ToInteger( $value ) );
        }
        
        
        /**
         * Converts given argument to sql integer.
         *
         * @param mixed $value  Value to convert
         * @return string
         */
        public static function ToInteger( $value ){
            return self::NullToString( Convert::ToInteger( $value ) );
        }
        
        
        /**
         * Converts given argument to sql double.
         *
         * @param mixed $value  Value to convert
         * @return string
         */
        public static function ToDouble( $value ){
            return self::NullToString( Convert::ToDouble( $value ) );
        }
        
        
        /**
         * Converts given argument to sql float.
         *
         * @param float $value
         * @return string
         */
        public static function ToFloat( $value ){
            return self::NullToString( Convert::ToFloat( $value ) );
        }
        
        
        /**
         * Converts given argument to sql boolean.
         *
         * @param mixed $value  Value to convert
         * @return string
         */
        public static function ToBoolean( $value ){
            if ( $value === true || $value == 'true' ) {
                return 'true';
            } else if ( $value === false || $value == 'false' ) {
                return 'false';
            }

            return 'null';
        }


        /**
         * Converts given argument to sql array.
         *
         * @param mixed $value  Value to convert
         * @param string $type   Type of the values in the array (TYPE_*)
         *
         * @return string
         */
        public static function ToList( $value, $type = TYPE_STRING ){
            $method = 'To' . $type;
            
            if ( !is_callable( array( __CLASS__, $method ) ) ) {
                Logger::Error( 'Call to undefined method %s', $method  );
            }
            
            $items = Convert::ToArray( $value );
            $items = array_map( array( __CLASS__, $method ), $items) ;

            return '(' . implode( ',', $items ) . ')';
        }


        /**
         * Converts given argument to sql datetime.
         *
         * @param mixed $value  Value to convert
         * @param string $format
         * @return string
         */
        public static function ToDateTime( $value, $format = 'Y-m-d H:i:s' ){
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
        public static function ToDate( $value, $format = 'Y-m-d' ){
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
        public static function FromString( $value ){
            return Convert::ToString( $value );
        }
        
        
        /**
         * Converts given argument from sql integer.
         *
         * @param mixed $value  Value to convert
         * @return integer
         */
        public static function FromInt( $value ){
            return Convert::ToInteger( $value );
        }
        
        
        /**
         * Converts given argument from sql integer.
         *
         * @param mixed $value  Value to convert
         * @return integer
         */
        public static function FromInteger( $value ){
            return Convert::ToInteger( $value );
        }
        
        
        /**
         * Converts given argument from sql double.
         *
         * @param mixed $value  Value to convert
         * @return double
         */
        public static function FromDouble( $value ){
            return Convert::ToDouble( $value );
        }
        
        
        /**
         * Converts given argument from sql float.
         *
         * @param mixed $value  Value to convert
         * @return float
         */
        public static function FromFloat( $value ){
            return Convert::ToFloat( $value );
        }
        
        
        /**
         * Converts given argument from sql boolean.
         *
         * @param mixed $value  Value to convert
         * @return boolean
         */
        public static function FromBoolean( $value ) {
            switch ( $value ) {
                case 't':
                    return true;
                case 'f':
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
        public static function FromParameter( $value, $type ){
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
                    Logger::Error( 'Cannot call converter for %s of class PgSqlConvert', $type );
                    return null;
            }
        }


        /**
         * Quote Database Object
         *
         * @param string $field
         * @return string
         */
        public static function Quote( $field ) {
            return '"' . $field . '"';
        }

    }
?><?php
    /**
     * Helps to manage sets of PostgreSQL database data resources.
     *
     * @package Eaze
     * @subpackage PgSql
     * @author max3.05, sergeyfast
     */
    class PgSqlDataSet extends DataSet {

        /**
         * Initializing instance.
         *
         * @param resource $resource  PostgreSQL result resource.
         * @param IConnection $connection
         * @return PgSqlDataSet
         */
        public function __construct( $resource, IConnection $connection ) {
            if ( is_resource( $resource ) ) {
                $this->size     = pg_num_rows( $resource );
                $this->resource = $resource;
                
                $i = pg_num_fields( $resource );
                for ( $j = 0; $j < $i; $j++ ) {
                    $name = pg_field_name( $resource, $j );
                    $this->Columns[$name] = $name;
                }

                parent::__construct( $connection );
            }
        }

        
        /**
         * Sets the cursor to a next element.
         *
         * @return boolean  <code>true</code> if cursor moved to the next element, otherwise <code>false</code>
         */
        public function Next() {
            if ( !parent::Next() ) {
                return false;
            }
            
            if ( empty( $this->data[$this->cursor] ) ) {
                $this->data[$this->cursor] = pg_fetch_array( $this->resource, $this->cursor, PGSQL_BOTH );
            }

            return true;
        }
        
        
        /**
         * Sets the cursor to a previous element.
         *
         * @return boolean  <code>true</code> if cursor moved to the previous element, otherwise <code>false</code>
         */
        public function Previous() {
            $this->cursor --;
            
            if ( $this->cursor > -1 ) {
                if ( empty( $this->data[$this->cursor] ) ) {
                    $this->data[$this->cursor] = pg_fetch_array( $this->resource, $this->cursor, PGSQL_BOTH );
                }

                return true;
            }
            
            $this->cursor ++;
            return false;
        }


        /**
         * Gets parameters of the current row and specified field as it.
         *
         * @param string $name  Field name.
         * @return string Field value of the current row.
         */
        public function GetParameter( $name ) {
            if ( isset( $this->data[$this->cursor][$name] ) ) {
                return $this->data[$this->cursor][$name];
            } 
            
            return null;
        }
        

        /**
         * @param string $name
         * @param string $type
         * @return mixed
         */
        public function GetValue( $name, $type = TYPE_STRING ) {
            return PgSqlConvert::FromParameter( $this->data[$this->cursor][$name], $type );
        }


        /**
         * Returns the string from the hash.
         *
         * @param  string $name  the parameter name
         * @return string the parameter value
         */
        public function GetString( $name ) {
            $param = $this->GetParameter( $name );
            
            return PgSqlConvert::FromString( $param );
        }
    
        
        /**
         * Returns the integer from the hash.
         *
         * @param  $name  the parameter name
         * @return the parameter value
         */
        public function GetInteger( $name ) {
            $param = $this->GetParameter( $name );
            
            return PgSqlConvert::FromInteger( $param );
        }
        
        
        /**
         * Returns the float from the hash.
         *
         * @param  $name  the parameter name
         * @return the parameter value
         */
        public function GetFloat( $name ) {
            $param = $this->GetParameter( $name );
                        
            return PgSqlConvert::FromFloat( $param );
        }
        
        
        /**
         * Returns the double from the hash.
         *
         * @param  $name  the parameter name
         * @return the parameter value
         */
        public function GetDouble( $name ) {
            $param = $this->GetParameter( $name );
                        
            return PgSqlConvert::FromDouble( $param );
        }
    
        
        /**
         * Returns the boolean from the hash.
         *
         * @param  $name  the parameter name
         * @return the parameter value
         */
        public function GetBoolean( $name ) {
            $param = $this->getParameter( $name );
            
            return PgSqlConvert::FromBoolean( $param );
        }
    
    
        /**
         * Returns the datetime parameter from hash.
         *
         * @param string $name  Field name.
         * @return Datetime
         */
        public function GetDateTime( $name ) {
            $param = $this->getParameter( $name );
                        
            return PgSqlConvert::FromDateTime( $param );
        }
    }
?><?php

    /**
     * PgSql Type Float[]
     * @package Eaze
     * @subpackage Database
     * @subpackage PgSql
     * @author sergeyfast, shuler
     */
    class PgSqlTypeFloatArray implements IComplexType {

        /**
         * @var PgSqlConvert
         */
        private $converter;


        /**
         * @param ISqlConvert $converter
         */
        public function __construct( ISqlConvert $converter ) {
            $this->converter = $converter;
        }


        /**
         * @param  string $operator
         * @param string $field
         * @param  string $value
         * @return string
         */
        public function GetSearchOperatorString( $operator, $field, $value ) {
            switch( $operator ) {
                case SEARCHTYPE_EQUALS:
                    $result = sprintf( '%s = %s', $this->converter->Quote( $operator ), $this->ToDatabase( $value ) );
                    break;
                default:
                    Logger::Error( 'Invalid search type %s', $operator );
                    $result = 'false';
            }

            return $result;
        }


        /**
         * Save PHP value to Database
         * @param array $value
         * @return string
         */
        public function ToDatabase( $value = null ) {
            if ( $value === null ) {
                return 'null';
            }

            $result = '{' . implode( ',', array_map( array( 'PgSqlConvert', 'ToFloat' ), $value ) ) . '}';

            return $this->converter->ToString( $result );
        }


        /**
         * Validate PHP Value before Save to Database
         * @param array|mixed $value
         * @param array $structure
         * @param array|null $options
         * @return array errors array
         */
        public function Validate( $value, array $structure, $options = null ) {
            $errors = array();

            //format check
            if( !is_array( $value ) && $value !== null ) {
                return array( 'format' => 'format' );
            }

            //nullable check
            if( isset( $structure['nullable'] ) ) {
                switch ( $structure['nullable'] ) {
                    case 'CheckEmpty':
                        if( !is_array( $value ) || empty( $value ) ) {
                            $errors['null'] = 'null';
                        }
                        break;
                    case 'No':
                        if( is_null( $value ) ) {
                            $errors['null'] = 'null';
                        }
                        break;
                }
            }

            return $errors;
        }


        /**
         * Get PHP value from Database
         * @param  string $parameter
         * @return array
         */
        public static function FromDatabase( $parameter ) {
            if ( empty( $parameter ) ) {
                return array();
            }

            $value  = trim( $parameter, '{} ' );
            $result = ( strlen( $value ) == 0 ) ? array() : explode( ',', $value );
            $result = array_map( array( 'Convert', 'ToFloat' ), $result );

            return $result;
        }


        /**
         * Get PHP value from Request
         * @param array|mixed $value
         * @return array
         */
        public static function FromRequest( $value ) {
            $value = array_map( array( 'Convert', 'ToFloat' ), Convert::ToArray( $value ) );

            return $value;
        }


        /**
         * Get Complex Type Name
         * @return string
         */
        public static function GetName() {
            return 'float[]';
        }
    }
?>
<?php
    /**
     * PgSqlTypeHStoreArray
     *
     * @package Eaze
     * @subpackage Database
     * @subpackage PgSql
     * @author m.kabilov
     */
    class PgSqlTypeHStoreArray  implements IComplexType {

        const HstoreFormat = '"%s"=>"%s"';

        //const HstoreRegexp = '#(^|, )"(.*)"=>"(.*)"#siU';
        const HstoreRegexp = '#(^|, )"(.*?)"=>(?>"{(.*?)}"|"(.*?)")#si';

        private static $strSearch  = array( '"', '\'' );
        private static $strReplace = array( '\\\"', '\'\'' );

        /**
         * @var PgSqlConvert
         */
        private $converter;


        /**
         * @param ISqlConvert $converter
         */
        public function __construct( ISqlConvert $converter ) {
            $this->converter = $converter;
        }


        /**
         * @param  string $operator
         * @param string $field
         * @param  string $value
         * @return string
         */
        public function GetSearchOperatorString( $operator, $field, $value ) {
            switch( $operator ) {
                case SEARCHTYPE_EQUALS:
                    $result = sprintf( '%s = %s', $this->converter->Quote( $operator ), $this->ToDatabase( $value ) );
                    break;
                default:
                    Logger::Error( 'Invalid search type %s', $operator );
                    $result = 'false';
            }

            return $result;
        }


        /**
         * Save PHP value to Database
         * @param array $value
         * @return string
         */
        public function ToDatabase( $value = null ) {
            if ( $value === null ) {
                return 'null';
            }

            $hstoreArray = array();
            foreach ( $value as $k => $v ) {
                $k = str_replace( self::$strSearch, self::$strReplace, $k );
                $v = $v === null ? 'null' : str_replace( self::$strSearch, self::$strReplace, $v );
                
                $hstoreArray[] = sprintf( self::HstoreFormat, $k, $v );
            }

            $result = "'" . implode( ',', $hstoreArray ) . "'";

            return $result;
        }


        /**
         * Validate PHP Value before Save to Database
         * @param array|mixed $value
         * @param array $structure
         * @param array|null $options
         * @return array errors array
         */
        public function Validate( $value, array $structure, $options = null ) {
            // TODO: validate array values (must be either one-dimensional or scalar)
            $errors = array();

            //format check
            if( !is_array( $value ) && $value !== null ) {
                return array( 'format' => 'format' );
            }

            //nullable check
            if( isset( $structure['nullable'] ) ) {
                switch ( $structure['nullable'] ) {
                    case 'CheckEmpty':
                        if( !is_array( $value ) || empty( $value ) ) {
                            $errors['null'] = 'null';
                        }
                        break;
                    case 'No':
                        if( is_null( $value ) ) {
                            $errors['null'] = 'null';
                        }
                        break;
                }
            }

            return $errors;
        }


        /**
         * Get PHP value from Database
         * @param  string $parameter
         * @return array
         */
        public static function FromDatabase( $parameter ) {
            $result = array();
            if ( empty( $parameter ) ) {
                return $result;
            }

            if ( !preg_match_all( self::HstoreRegexp , $parameter, $params ) ) {
                return $result;
            }
            
            $params[2] = str_replace( '\"', '"', $params[2] );
            $params[3] = str_replace( '\"', '"', $params[3] );
            $params[4] = str_replace( '\"', '"', $params[4] );

            //merge with JSON parameters:
            foreach( array_filter($params[3]) as $key => $jsonParam ){
                if( isset( $params[4][$key] ) && empty( $params[4][$key] ) ){
                    $params[4][$key] = sprintf( '{%s}', $jsonParam );
                }
            }

            $result = array_combine( $params[2], $params[4] );

            return $result;
        }


        /**
         * Get PHP value from Request
         * @param array|mixed $value
         * @return array
         */
        public static function FromRequest( $value ) {
            $value = Convert::ToArray( $value );

            return $value;
        }


        /**
         * Get Complex Type Name
         * @return string
         */
        public static function GetName() {
            return 'hstore';
        }

    }
?>
<?php

    /**
     * PgSql Type Int[]
     * @package Eaze
     * @subpackage Database
     * @subpackage PgSql
     * @author sergeyfast
     */
    class PgSqlTypeIntArray implements IComplexType {

        /**
         * @var PgSqlConvert
         */
        private $converter;


        /**
         * @param ISqlConvert $converter
         */
        public function __construct( ISqlConvert $converter ) {
            $this->converter = $converter;
        }


        /**
         * @param  string $operator
         * @param string $field
         * @param  string $value
         * @return string
         */
        public function GetSearchOperatorString( $operator, $field, $value ) {
            switch( $operator ) {
                case SEARCHTYPE_EQUALS:
                    $result = sprintf( '%s = %s', $this->converter->Quote( $operator ), $this->ToDatabase( $value ) );
                    break;
                default:
                    Logger::Error( 'Invalid search type %s', $operator );
                    $result = 'false';
            }

            return $result;
        }


        /**
         * Save PHP value to Database
         * @param array $value
         * @return string
         */
        public function ToDatabase( $value = null ) {
            if ( $value === null ) {
                return 'null';
            }

            $result = '{' . implode( ',', array_map( array( 'PgSqlConvert', 'ToInteger' ), $value ) ) . '}';

            return $this->converter->ToString( $result );
        }


        /**
         * Validate PHP Value before Save to Database
         * @param array|mixed $value
         * @param array $structure
         * @param array|null $options
         * @return array errors array
         */
        public function Validate( $value, array $structure, $options = null ) {
            $errors = array();

            //format check
            if( !is_array( $value ) && $value !== null ) {
                return array( 'format' => 'format' );
            }

            //nullable check
            if( isset( $structure['nullable'] ) ) {
                switch ( $structure['nullable'] ) {
                    case 'CheckEmpty':
                        if( !is_array( $value ) || empty( $value ) ) {
                            $errors['null'] = 'null';
                        }
                        break;
                    case 'No':
                        if( is_null( $value ) ) {
                            $errors['null'] = 'null';
                        }
                        break;
                }
            }

            return $errors;
        }


        /**
         * Get PHP value from Database
         * @param  string $parameter
         * @return array
         */
        public static function FromDatabase( $parameter ) {
            if ( empty( $parameter ) ) {
                return array();
            }

            $value  = trim( $parameter, '{} ' );
            $result = ( strlen( $value ) == 0 ) ? array() : explode( ',', $value );
            $result = array_map( array( 'Convert', 'ToInteger' ), $result );

            return $result;
        }


        /**
         * Get PHP value from Request
         * @param array|mixed $value
         * @return array
         */
        public static function FromRequest( $value ) {
            $value = array_map( array( 'Convert', 'ToInteger' ), Convert::ToArray( $value ) );

            return $value;
        }


        /**
         * Get Complex Type Name
         * @return string
         */
        public static function GetName() {
            return 'int[]';
        }
    }
?>
<?php

    /**
     * PgSql Type Point
     * @package Eaze
     * @subpackage Database
     * @subpackage PgSql
     * @author sergeyfast
     */
    class PgSqlTypePoint implements IComplexType {

        /**
         * @var PgSqlConvert
         */
        private $converter;


        /**
         * @param ISqlConvert $converter
         */
        public function __construct( ISqlConvert $converter ) {
            $this->converter = $converter;
        }


        /**
         * @param  string $operator
         * @param string $field
         * @param  string $value
         * @return string
         */
        public function GetSearchOperatorString( $operator, $field, $value ) {
            switch( $operator ) {
                case SEARCHTYPE_EQUALS:
                    $result = sprintf( '%s = %s', $this->converter->Quote( $operator ), $this->ToDatabase( $value ) );
                    break;
                default:
                    Logger::Error( 'Invalid search type %s', $operator );
                    $result = 'false';
            }

            return $result;
        }


        /**
         * Save PHP value to Database
         * @param array $value
         * @return string
         */
        public function ToDatabase( $value = null ) {
            if ( $value === null || empty( $value ) ) {
                return 'null';
            }

            $result = '(' . implode( ',', array_map( array( 'PgSqlConvert', 'ToFloat' ), $value ) ) . ')';

            return $this->converter->ToString( $result );
        }


        /**
         * Validate PHP Value before Save to Database
         * @param array|mixed $value
         * @param array $structure
         * @param array|null $options
         * @return array errors array
         */
        public function Validate( $value, array $structure, $options = null ) {
            $errors = array();

            //format check
            if( $value !== null
                && ( !is_array( $value ) || !in_array( count( $value ), array( 0, 2 ) ) ) )
            {
                return array( 'format' => 'format' );
            }

            // if has value check for null
            if ( !empty( $value ) ) {
                $value = array_values( $value );
                if ( is_null( $value[0] ) || is_null( $value[1] ) ) {
                    $errors['null'] = 'null';
                }
            }

            //nullable check from mapping
            if( isset( $structure['nullable'] ) ) {
                switch ( $structure['nullable'] ) {
                    case 'CheckEmpty':
                    case 'No':
                        if( empty( $value ) ) {
                            $errors['null'] = 'null';
                        }
                        break;
                }
            }

            return $errors;
        }


        /**
         * Get PHP value from Database
         * @param  string $parameter
         * @return array
         */
        public static function FromDatabase( $parameter ) {
            if ( empty( $parameter ) ) {
                return array();
            }

            $value  = trim( $parameter, '() ' );
            $result = ( strlen( $value ) == 0 ) ? array() : explode( ',', $value );
            $result = array_map( array( 'Convert', 'ToFloat' ), $result );

            return $result;
        }


        /**
         * Get PHP value from Request
         * @param array|mixed $value
         * @return array
         */
        public static function FromRequest( $value ) {
            $value = array_map( array( 'Convert', 'ToFloat' ), Convert::ToArray( $value ) );
            if ( $value !== null ) {
                if ( count( $value ) == 2 ) {
                    $value = array_values( $value );
                } else if ( count( $value ) >= 2 ) {
                    $value = array_slice( array_values( $value ), 0, 2 );
                } else if ( count( $value ) < 2 ) {
                    $value = null;
                }
            }

            // check for null values
            if  ( !is_null( $value ) && is_null( $value[0] ) && is_null( $value[1] ) ) {
                $value = null;
            }

            return $value;
        }


        /**
         * Get Complex Type Name
         * @return string
         */
        public static function GetName() {
            return 'point';
        }
    }
?><?php

    /**
     * PgSql Type String[]
     * @package Eaze
     * @subpackage Database
     * @subpackage PgSql
     * @author sergeyfast, shuler
     */
    class PgSqlTypeStringArray implements IComplexType {

        /**
         * @var PgSqlConvert
         */
        private $converter;


        /**
         * @param ISqlConvert $converter
         */
        public function __construct( ISqlConvert $converter ) {
            $this->converter = $converter;
        }


        /**
         * @param  string $operator
         * @param string $field
         * @param  string $value
         * @return string
         */
        public function GetSearchOperatorString( $operator, $field, $value ) {
            switch( $operator ) {
                case SEARCHTYPE_EQUALS:
                    $result = sprintf( '%s = %s', $this->converter->Quote( $operator ), $this->ToDatabase( $value ) );
                    break;
                default:
                    Logger::Error( 'Invalid search type %s', $operator );
                    $result = 'false';
            }

            return $result;
        }


        /**
         * Save PHP value to Database
         * @param array $value
         * @return string
         */
        public function ToDatabase( $value = null ) {
            if ( $value === null ) {
                return 'null';
            }

            $result = '{' . implode( ',', array_map( array( 'PgSqlConvert', 'ToString' ), $value ) ) . '}';

            return $this->converter->ToString( $result );
        }


        /**
         * Validate PHP Value before Save to Database
         * @param array|mixed $value
         * @param array $structure
         * @param array|null $options
         * @return array errors array
         */
        public function Validate( $value, array $structure, $options = null ) {
            $errors = array();

            //format check
            if( !is_array( $value ) && $value !== null ) {
                return array( 'format' => 'format' );
            }

            //nullable check
            if( isset( $structure['nullable'] ) ) {
                switch ( $structure['nullable'] ) {
                    case 'CheckEmpty':
                        if( !is_array( $value ) || empty( $value ) ) {
                            $errors['null'] = 'null';
                        }
                        break;
                    case 'No':
                        if( is_null( $value ) ) {
                            $errors['null'] = 'null';
                        }
                        break;
                }
            }

            return $errors;
        }


        /**
         * Get PHP value from Database
         * @param  string $parameter
         * @return array
         */
        public static function FromDatabase( $parameter ) {
            if ( empty( $parameter ) ) {
                return array();
            }

            $value  = trim( $parameter, '{} ' );
            $result = ( strlen( $value ) == 0 ) ? array() : explode( ',', $value );
            $result = array_map( array( 'PgSqlTypeStringArray', 'convertFromDatabase' ), $result );

            return $result;
        }


        /**
         * Get PHP value from Request
         * @param array|mixed $value
         * @return array
         */
        public static function FromRequest( $value ) {
            $value = array_map( array( 'Convert', 'ToString' ), Convert::ToArray( $value ) );

            return $value;
        }


        /**
         * Get Complex Type Name
         * @return string
         */
        public static function GetName() {
            return 'string[]';
        }

        /**
         * converts string value from database array
         *
         * @static
         * @param $value
         * @return null|string
         */
        private static function convertFromDatabase( $value ) {
            if( $value === 'null' || $value === 'NULL' ) {
                return null;
            } else {
                //cut first and last (') symbol, cut double (') symbol
                $result = mb_substr( $value, 1, mb_strlen( $value ) - 2 );
                $result = str_replace( "''", "'", $result );
                return (string) $result;
            }
        }
    }
?>
