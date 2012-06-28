<?php
    /**
     * Connection Factory
     * @package Eaze
     * @subpackage Database
     */
    class ConnectionFactory {

        /**
         * Default Connection Name
         */
        const DefaultConnection = 'default';

        /**
         * Connections
         * @var IConnection[]
         */
        private static $connections = array();

        /**
         * Connection params
         * @var array
         */
        private static $params = array(
            'driver'       => null
            , 'name'       => self::DefaultConnection
            , 'host'       => null
            , 'port'       => null
            , 'dbname'     => null
            , 'user'       => null
            , 'password'   => null
            , 'encoding'   => null
            , 'persistent' => false
        );

        
        /**
         * Add Connection config
         *
         * @param array $params
         * @return bool
         */
        public static function Add( $params ) {
            if ( empty( $params ) || empty( $params['driver'] ) ) {
                return false;
            }

            $params += self::$params;

            if ( !empty( $params['persistent'] ) ) {
                $params['persistent'] = Convert::ToBoolean( $params['persistent' ] );
            }

            if ( empty( $params['name'] ) ) {
                $params['name'] = self::$params['name'];
            }

            if ( isset( self::$connections[$params['name']] ) ){
                return false;
            }

            Package::Load( 'Eaze.Database/' . $params['driver'] );
            $className = $params['driver'] . 'Connection';
            
            ConnectionFactory::$connections[$params['name']] = new $className(
                $params['host']
                , $params['port']
                , $params['dbname']
                , $params['user']
                , $params['password']
                , $params['encoding']
                , $params['persistent']
            );
            
            return true;
        }
        
        
        /**
         * Get Opened Connection by Name
         * @param string $name [optional] connection name
         * @return IConnection
         */
        public static function Get( $name = self::DefaultConnection ) {
            if ( empty( $name ) ) {
                $name = self::DefaultConnection;
            }

            if ( !empty( self::$connections[$name] ) ) {
                return self::$connections[$name];
            }
            
            return null;
        }
        
        
        /**
         * Close connections and free resources
         */
        public static function Dispose() {
            foreach ( self::$connections as $connection ) {
                $connection->close();
            }
        }


        /**
         * Close and Remove connection from pool
         * @param string $name [optional] connection name
         * @return bool
         */
        public static function Remove( $name = self::DefaultConnection ) {
            if ( empty( $name ) ) {
                $name = self::DefaultConnection;
            }

            if ( !empty( self::$connections[$name] ) ) {
                self::$connections[$name]->Close();
                unset( self::$connections[$name] );
                return true;
            }

            return false;
        }


        /**
         * Begin Transaction
         * @static
         * @param string $name [optional] connection name
         * @return IConnection
         */
        public static function BeginTransaction( $name = self::DefaultConnection ) {
            $conn = ConnectionFactory::Get( $name );
            if ( !empty( $conn ) && !$conn->IsTransaction() ) {
                $conn->Begin();
            }

            return $conn;
        }

        /**
         * Commit or Rollback Transaction
         * @static
         * @param bool   $result  commit or rollback
         * @param string $name [optional] connection name
         * @return bool
         */
        public static function CommitTransaction( $result, $name = self::DefaultConnection ) {
            $conn = ConnectionFactory::Get( $name );
            if ( !empty( $conn ) && $conn->IsTransaction() ) {
                if ( $result ) {
                    $conn->Commit();
                } else {
                    $conn->Rollback();
                }

                return true;
            }

            return false;
        }
    }
?><?php
    /**
     * Helps to manage sets of database data resources.
     *
     * @package Eaze
     * @subpackage Database
     * @author  max.05
     */
    abstract class DataSet {

        /**
         * DataSet Columns
         *
         * @var array
         */
        public $Columns = array();        
        
        /**
         * The cursor.
         * 
         * @var integer
         */
        protected $cursor  = -1;
        
        /**
         * Database data resource.
         *
         * @var resource
         */
        protected $resource = null;
        
        /**
         * Represents database data as array of the rows.
         * 
         * @var array
         */
        protected $data = array();
        
        /**
         * The number of the rows in the result resource.
         * 
         * @var integer
         */
        protected $size = 0;

        /**
         * Sql Connection
         *
         * @var IConnection
         */
        protected $connection;
        

        /**
         * Sets the cursor to a first element.
         */
        public function First() {
            $this->cursor = 0;
        }
        
        
        /**
         * Sets the cursor to a last element.
         */
        public function Last() {
            $this->cursor = $this->size - 1;
        }


        /**
         * @param IConnection $connection
         */
        public function __construct( IConnection $connection ) {
            $this->connection = $connection;
        }


        /**
         * Sets the cursor to a next element.
         *
         * @return boolean  <code>true</code> if cursor moved to the next element, otherwise <code>false</code>
         */
        public function Next() {
            if ( isset( $this->data[$this->cursor] ) ) {
                unset( $this->data[$this->cursor] );
            }

            $this->cursor ++;
            
            if ( $this->cursor >= $this->size ) {
                $this->cursor --;
                return false;
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
                return true;
            }
            
            $this->cursor++;
            return false;
        }
        
        
        /**
         * Sets the cursor to the initial value.
         */
        public function Reset() {
            $this->cursor = -1;   
        }
        
        
        /**
         * Returns the size of the set.
         *
         * @return integer  The number of elements in the data source
         */
        public function GetSize() {
            return ( $this->size );
        }    
        
        
        /**
         * Sets the cursor position.
         *
         * @param integer $position  the cursor position
         */
        public function SetCursor( /*integer*/ $position ) {
            if ( ($position > -1) && ($position < $this->size) ) {
                $this->cursor = $position;
            }
        }
        
        
        /**
         * Returns the current position of the cursor.
         *
         * @return integer The cursor value
         */
        public function GetCursor() {
            return ( $this->cursor );
        }
        
       
        /**
         * Clears the DataSet data.
         */
        public function Clear() {
            $this->resource = null;
            $this->data     = null;    
            $this->size     = 0;
            $this->cursor   = -1;
        }

        /**
         * Get Integer
         * @abstract
         * @param  string $name column name
         * @return integer
         */
        abstract function GetInteger( $name );

        /**
         * Get String
         * @abstract
         * @param  string $name column name
         * @return string
         */
        abstract function GetString( $name );

        /**
         * Get Float
         * @abstract
         * @param  string $name column name
         * @return float
         */
        abstract function GetFloat( $name );

        /**
         * Get Double
         * @abstract
         * @param  string $name column name
         * @return float
         */
        abstract function GetDouble( $name );

        /**
         * Get Boolean
         * @abstract
         * @param  string $name column name
         * @return bool
         */
        abstract function GetBoolean( $name );

        /**
         * Get Date Time
         * @abstract
         * @param  string $name column name
         * @return DateTimeWrapper
         */
        abstract function GetDateTime( $name );


        /**
         * Get Value
         * @abstract
         * @param string $name  column name
         * @param string $type
         * @return mixed
         */
        abstract function GetValue( $name, $type = TYPE_STRING );


        /**
         * Get Unprocessed Value
         * @abstract
         * @param  string $name
         * @return mixed
         */
        abstract function GetParameter( $name );


        /**
         * Get ComplexType Value
         * @abstract
         * @param  string $name
         * @param  string $alias complexType Alias
         * @return array|mixed
         */
        public function GetComplexType( $name, $alias ) {
            $result = null;
            $type = $this->connection->GetComplexType( $alias );
            if ( $type !== null ) {
                $result = $type->FromDatabase( $this->GetParameter( $name ) );
            }

            return $result;
        }
    }
?>
<?php
    /**
     * Db Type Json Array
     * @package Eaze
     * @subpackage Database
     */
    class DbTypeJsonArray implements IComplexType {

        /**
         * @var ISqlConvert
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
         * @param mixed|null $value
         * @return string
         */
        public function ToDatabase( $value = null ) {
            return $this->converter->ToString( json_encode( $value ) );
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
            if ( empty( $value ) || is_array( $value ) ) {
                return $errors;
            }

            $errors['format'] = 'format';

            return $errors;
        }


        /**
         * Get PHP value from Database
         * @param  string $parameter
         * @return array|mixed
         */
        public static function FromDatabase( $parameter ) {
            return json_decode( $parameter, true );
        }


        /**
         * Get PHP value from Request
         * @param array|mixed $value
         * @return array|mixed
         */
        public static function FromRequest( $value ) {
            return Convert::ToArray( $value );
        }


        /**
         * Get Complex Type Name
         * @return string
         */
        public static function GetName() {
            return 'json';
        }
    }
?><?php
    /**
     * Db Type Php Array
     * @package Eaze
     * @subpackage Database
     */
    class DbTypePhpArray implements IComplexType {

        /**
         * @var ISqlConvert
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
         * @param mixed|null $value
         * @return string
         */
        public function ToDatabase( $value = null ) {
            return $this->converter->ToString( serialize( $value ) );
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
            if ( empty( $value ) || is_array( $value ) ) {
                return $errors;
            }

            $errors['format'] = 'format';

            return $errors;
        }


        /**
         * Get PHP value from Database
         * @param  string $parameter
         * @return array|mixed
         */
        public static function FromDatabase( $parameter ) {
            return unserialize( $parameter );
        }


        /**
         * Get PHP value from Request
         * @param array|mixed $value
         * @return array|mixed
         */
        public static function FromRequest( $value ) {
            return Convert::ToArray( $value );
        }


        /**
         * Get Complex Type Name
         * @return string
         */
        public static function GetName() {
            return 'php';
        }
    }
?><?php
    /**
     * IComplexType Interface for Database Support
     * @package Eaze
     * @subpackage Database
     * @author sergeyfast
     */
    interface IComplexType {

        /**
         * @abstract
         * @param ISqlConvert $converter
         */
        function __construct( ISqlConvert $converter );


        /**
         * Get Complex Type Name
         * @return string
         */
        static function GetName();


        /**
         * Get PHP value from Database
         * @param  string $parameter
         * @return array|mixed
         */
        static function FromDatabase( $parameter );


        /**
         * Get PHP value from Request
         * @param array|mixed $value
         * @return array|mixed
         */
        static function FromRequest( $value );


        /**
         * Save PHP value to Database
         * @param mixed|null $value
         * @return string
         */
        function ToDatabase( $value = null );


        /**
         * Validate PHP Value before Save to Database
         * @param array|mixed $value
         * @param array $structure     field structure from mapping
         * @param array|null $options  additional options
         * @return array errors array
         */
        function Validate( $value, array $structure, $options = null );


        /**
         * Returns expression
         * @abstract
         * @param  string $operator  search type operator (SEARCHTYPE_*)
         * @param  string $field     search field
         * @param  string $value     field value
         * @return string  '"title" = 1 ' or '`field` IN (1,2,3)'
         */
        function GetSearchOperatorString( $operator, $field, $value );
    }

?><?php
    /**
     * Database connection interface
     * 
     * @package Eaze
     * @subpackage Database
     * @author max3.05
     */
    interface IConnection {
        
        /**
         * Executes specified query and return result dataset
         *
         * @param string $query  Sql query to execute.
         * @return IDataSet Result dataset
         */
        function ExecuteQuery( $query );
        
        /**
         * Execute Sql query and return result statement/
         *
         * @param string $query Sql query to execute.
         * @return boolean Return <code>true</code> if command executed successfully, otherwise return <code>false</code>.
         */
        function ExecuteNonQuery( $query );
        
        /**
         * Starts transaction
         * @return bool
         */
        function Begin();
        
        /**
         * Commits current transaction.
         * @return bool
         */
        function Commit();
        
        /**
         * Rollbacks current transaction.
         * @return bool
         */
        function Rollback();
        
        /**
         * Determines if transaction started.
         *
         * @return bool Return <code>true</code> if current connection is in transaction, otherwise <code>false</code>
         */
        function IsTransaction();

        /**
         * Gets last error message string of the connection.
         *
         * @return string Last message error string if the connection.
         */
        function GetLastError();
        
        /**
         * Opens connection using specified parameters
         */
        function Open();
        
        /**
         * Close current connection
         * @return bool
         */
        function Close();
        
        /**
         * Checks if current connection instance is opened.
         *
         * @return boolean  <code>True</code> if connection is opened, otherwise <code>false</code>.
         */
        function IsOpened();

        /**
         * Quote String
         *
         * @param string $str
         */
        function Quote( $str );

        /**
         * Get SqlConverter
         * @abstract
         * @return ISqlConvert
         */
        function GetConverter();


        /**
         * Get Complex Type
         * @abstract
         * @param  string $alias  (e.g. php, json, int[], string[], hstore)
         * @return IComplexType
         */
        function GetComplexType( $alias );


        /**
         * Get Connection Resource
         * @abstract
         * @return resource
         */
        function GetResource();

        /**
         * Get Connection Name
         * @abstract
         * @return string
         */
        function GetName();

        /**
         * Returns ClassName
         * @abstract
         * @return string
         */
        function GetClassName();
    }
?><?php
    /**
     * Interface for SQL conversation
     *
     * @package Eaze
     * @subpackage Database
     * @author sergeyfast, max3.05
     */
    interface ISqlConvert {

        /**
         * Create Sql Converter with Connection (static)
         * @abstract
         * @param IConnection $connection
         */
        function __construct( IConnection $connection );

        /**
         * Returns 'null' if value === null or value.
         * @static
         * @param  mixed $value
         * @return string
         */
        static function NullToString( $value );

        /**
         * Converts given argument to sql string.
         *
         * @param mixed $value  Value to convert
         */
        static function ToString( $value );


        /**
         * Converts given argument to sql integer.
         *
         * @param mixed $value  Value to convert
         */
        static function ToInt( $value );


        /**
         * Converts given argument to sql integer.
         *
         * @param mixed $value  Value to convert
         */
        static function ToInteger( $value );


        /**
         * Converts given argument to sql double.
         *
         * @param mixed $value  Value to convert
         */
        static function ToDouble( $value );


        /**
         * Converts given argument to sql float.
         *
         * @param float $value
         */
        static function ToFloat( $value );


        /**
         * Converts given argument to sql boolean.
         *
         * @param mixed $value  Value to convert
         */
        static function ToBoolean( $value );


        /**
         * Converts given argument to sql datetime.
         *
         * @param mixed $value  Value to convert
         * @param string $format
         *
         */
        static function ToDateTime( $value, $format = 'Y-m-d H:i:s' );


        /**
         * Converts given argument to sql date.
         *
         * @param mixed $value  Value to convert
         * @param string $format
         *
         */
        static function ToDate( $value, $format = 'Y-m-d' );


        /**
         * Converts given argument to sql time.
         *
         * @param mixed $value  Value to convert
         * @param string $format
         *
         */
        static function ToTime( $value, $format = 'H:i:s' );


        /**
         * Converts given argument to sql in expression.
         *
         * @param mixed $value  value to convert
         * @param string $type  type of the values in the array.
         */
        static function ToList( $value, $type = TYPE_STRING );


        /**
         * Converts given argument from sql string.
         *
         * @param mixed $value  Value to convert
         */
        static function FromString( $value );


        /**
         * Converts given argument from sql integer.
         *
         * @param mixed $value  Value to convert
         */
        static function FromInt( $value );


        /**
         * Converts given argument from sql integer.
         *
         * @param mixed $value  Value to convert
         */
        static function FromInteger( $value );


        /**
         * Converts given argument from sql double.
         *
         * @param mixed $value  Value to convert
         */
        static function FromDouble( $value );


        /**
         * Converts given argument from sql float.
         *
         * @param mixed $value  Value to convert
         */
        static function FromFloat( $value );


        /**
         * Converts given argument from sql boolean.
         *
         * @param mixed $value  Value to convert
         */
        static function FromBoolean( $value );


        /**
         * Converts given argument from sql datetime
         * @static
         * @param  $value
         * @return DateTimeWrapper
         */
        static function FromDate( $value );


        /**
         * Converts given argument from sql datetime.
         *
         * @param mixed $value  Value to convert
         */
        static function FromDateTime( $value );


        /**
         * Converts given argument from sql parameter.
         *
         * @param mixed $value  Value to convert
         * @param string $type  simple type of the value TYPE_*
         *
         */
        static function FromParameter( $value, $type );

        /**
         * Quote Database Object
         *
         * @param string $field
         */
        static function Quote( $field );
    }

?><?php
    /**
     * SqlCommand
     *
     * @package Eaze
     * @subpackage Database
     * @author  sergeyfast
     */
    class SqlCommand {

        /**
         * The sql command text.
         * 
         * @var string
         */
    	private $command;

    	/**
    	 * The connection descriptor.
    	 * 
    	 * @var IConnection
    	 */
        private $connection;
        
        /**
         * Sql command parameters.
         *
         * @var array
         */
        private $params = array();


        /**
         * Sets the command and a connection.
         *
         * @param string      $command     the sql query
         * @param IConnection $connection  the object implements {@link IConnection}
         * @see IConnection
         */
        public function SqlCommand( $command, IConnection $connection ) {
            if ( !empty( $command ) ) {
                $this->SetCommand( $command );    
            }
            
            $this->connection = $connection;
        }


        /**
         * Sets the command text.
         *
         * @param string $command  the command text
         */
        public function SetCommand( /*string*/ $command ) {
            $this->command = trim( $command );
            
            if ( empty( $this->command ) ) {
                Logger::Error( 'Empty sql command specified' );
            }
        }


        /**
         * Returns replaced sql statement with parameters
         * @return string
         */
        private function getPreparedQuery() {
            $params = $this->params;
            krsort( $params, SORT_STRING );
        	$query = str_replace( array_keys( $params ), array_values( $params ), $this->command );

            return $query;
        }


        /**
         * Executes the command.
         *
         * @return DataSet
         */
        public function Execute() {
            if ( !is_callable( array( $this->connection, 'executeQuery' ) ) ) {
                Logger::Error( 'Wrong database connection specified' );

                return null;
            }

            $query = $this->getPreparedQuery();
            
            // Execute query and create DataSet
            Logger::Checkpoint();
            $data = $this->connection->executeQuery( $query );

            /** Log sql query */
            if ( Logger::GetOutputMode() ==  Logger::HtmlMode ) {
                Logger::Debug( nl2br( str_replace( ' ', '&nbsp;', $query ) ) );
            } else {
                Logger::Debug( $query );
            }
            
            return $data;
        }


        /**
         * Executes the non query command.
         *
         * @return the result DataSet
         */
        public function ExecuteNonQuery() {
            if ( !is_callable( array( $this->connection, "executeNonQuery" ) ) ) {
                Logger::Error( 'Wrong database connection specified' );

                return null;
            }

            $query = $this->getPreparedQuery();

            // Execute query and create DataSet
            Logger::Debug( $query );
            $result = $this->connection->executeNonQuery( $query );

            return $result;
        }

        
        /**
         * Gets query with replaced parameters.
         *
         * @return string  Sql query 
         */
        public function GetQuery() {
            return $this->getPreparedQuery();
        }
        
        
        /**
         * Removes value from parameters with specified name
         *
         * @param string $name  Name of the parameter.
         */
        public function ClearParameter( $name ) {
            if ( isset( $this->params[$name] ) ) {
                unset( $this->params[$name] );
            }
        }
        
        
        /**
         * Clears parameters array.
         *
         */
        public function ClearParameters() {
            $this->params = array();
        }
        
        
        /**
         * Sets parametrized string.
         *
         * @param string $name   Name of the parameter.
         * @param string $value  Value to set.
         */
        public function SetString( $name, $value ){
            $this->params[$name] = $this->connection->GetConverter()->ToString( $value );
        }
        
        
        /**
         * Sets parametrized integer.
         *
         * @param string $name   Name of the parameter.
         * @param string $value  Value to set.
         */
        public function SetInt( $name, $value ){
            $this->params[$name] = $this->connection->GetConverter()->ToInt( $value );
        }
        
        
        /**
         * Sets parametrized integer.
         *
         * @param string $name   Name of the parameter.
         * @param string $value  Value to set.
         */
        public function SetInteger( $name, $value ){
            $this->params[$name] = $this->connection->GetConverter()->ToInt( $value );
        }
        
        
        /**
         * Sets parametrized double.
         *
         * @param string $name   Name of the parameter.
         * @param string $value  Value to set.
         */
        public function SetDouble( $name, $value ){
            $this->params[$name] = $this->connection->GetConverter()->ToDouble( $value );
        }
        
        
        /**
         * Sets parametrized float.
         *
         * @param string $name   Name of the parameter.
         * @param string $value  Value to set.
         */
        public function SetFloat( $name, $value ){
            $this->params[$name] = $this->connection->GetConverter()->ToFloat( $value );
        }
        
        
        /**
         * Sets parametrized boolean.
         *
         * @param string $name   Name of the parameter.
         * @param string $value  Value to set.
         */
        public function SetBoolean( $name, $value ){
            $this->params[$name] = $this->connection->GetConverter()->ToBoolean( $value );
        }


        /**
         * Sets parametrized array.
         *
         * @param string $name   Name of the parameter.
         * @param string $value  Value to set.
         * @param string $type   Type of instances in the array.
         */
        public function SetList( $name, $value, $type ){
            $this->params[$name] = $this->connection->GetConverter()->ToList( $value, $type );
        }


        /**
         * Sets parametrized datetime.
         *
         * @param string $name   Name of the parameter.
         * @param string $value  Value to set.
         */
        public function SetDateTime( $name, $value ){
            $this->params[$name] = $this->connection->GetConverter()->ToDateTime( $value );
        }
        
        
        /**
         * Sets parametrized date.
         *
         * @param string $name   Name of the parameter.
         * @param string $value  Value to set.
         */
        public function SetDate( $name, $value ){
            $this->params[$name] = $this->connection->GetConverter()->ToDate( $value );
        }

        /**
         * Sets parametrized time.
         *
         * @param string $name   Name of the parameter.
         * @param string $value  Value to set.
         */
        public function SetTime( $name, $value ){
            $this->params[$name] = $this->connection->GetConverter()->ToTime( $value );
        }


        /**
         * Set Complex Type
         * @param  string $name   name of the parameter
         * @param  mixed  $value  value to set
         * @param  string $type   complex type alias
         * @return void
         */
        public function SetComplexType( $name, $value, $type ) {
            $ct = $this->connection->GetComplexType( $type );
            $this->params[$name] = $ct !== null ? $ct->ToDatabase( $value ) : $this->connection->GetConverter()->NullToString( null );
        }


        /**
         * Set parameter.
         *
         * @param string $name          Name of the parameter.
         * @param string $value         Value to set.
         * @param string $type          Value type.
         * @param string $complexType   optional complex type alias
         *
         */
        public function SetParameter( $name, $value, $type = TYPE_STRING, $complexType = null ){
            $method = 'Set' . $type;

            if ( !empty( $complexType ) ) {
                $this->SetComplexType( $name, $value, $complexType );
            } else if ( is_callable( array( $this, $method ) ) ) {
                $this->$method( $name, $value );
            } else {
                Logger::Error( 'Cannot call %s of class SqlCommand', $method );
            }
        }
        
        
        /**
         * Sets parameter as range filter.
         *
         * @param string $name      Name of the parameter.
         * @param mixed $lowBound   Low bound of the range.
         * @param mixed $highBound  High bound of the range.
         * @param string $type      Type of the range bounds.
         */
        public function SetRange( $name
                                  , $lowBound  = null
                                  , $highBound = null
                                  , $type      = TYPE_STRING ) {
            $method = 'To' . $type;
            
            if ( !is_callable( array( $this->connection->GetConverter(), $method ) ) ) {
                Logger::Error( 'Could not call method %s', $method );
            }
            
            if ( $lowBound === null && $highBound === null  ) {
                Logger::Error( 'Both bounds could not be null!' );
            } elseif ( $lowBound === null && ( $highBound !== null ) ) {
                $this->params[$name] = '<= ' . $this->connection->GetConverter()->$method( $highBound );
            } elseif ( $lowBound !== null && $highBound === null ) {
                $this->params[$name] = '>= ' . $this->connection->GetConverter()->$method( $lowBound );
            } else {
                $this->params[$name] = 'BETWEEN ' . $this->connection->GetConverter()->$method( $lowBound ) . ' AND ' . $this->connection->GetConverter()->$method( $highBound );
            }
        }
    }
?>