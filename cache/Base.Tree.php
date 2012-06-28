<?php
    define( "TREEMODE_LTREE", "LTree" );
    define( "TREEMODE_ADJ", "ADJLists" );
    define( "TREEMODE_NS", "NestedSets");
    
    define( "SELECTOR_PATH", "path" );
    define( "SELECTOR_ID", "id" );    
    
    /**
     * Base Tree Object Factory
     * 
     * @package Base
     * @subpackage Base.Tree
     * @author Rykin Maxim
     */
    class BaseTreeFactory {
        /**
         * Mapping For Base Tree Object.
         *
         * @var array
         * @access public
         * @static
         */
        public static $mapping = array(
            "fields"    => array(
                "objectId" => array(
                    "name"         => "objectId"
                    , "type"       => TYPE_INTEGER
                    , "key"        => true
                )
                ,"parentId" => array(
                    "name"         => "parentId"
                    , "type"       => TYPE_INTEGER
                    , "foreignKey" => "BaseTreeObject"
                )
                ,"path" => array(
                    "name"         => "path"
                    , "type"       => TYPE_STRING
                    , "nullable"   => "CheckEmpty"
                )
                ,"rKey" => array(
                    "name"         => "rKey"
                    , "type"       => TYPE_INTEGER
                )
                ,"lKey" => array(
                    "name"         => "lKey"
                    , "type"       => TYPE_INTEGER
                )
                , "level" => array(
                    "name"          => "level"
                    , "type"        => TYPE_INTEGER
                )
            )
            , "search"    => array(
                "_parentId" => array(
                    "name"         => "parentId"
                    , "type"       => TYPE_INTEGER
                    , "searchType" => SEARCHTYPE_ARRAY
                )
            )
        );

        /**
         * Current storage mode.
         *
         * @var string
         * @access private 
         * @static 
         */
        public static $CurrentMode = TREEMODE_LTREE;
        
        /**
         * Modes supported by add/update methods.
         *
         * @var array.
         * @access private
         * @static 
         */
        private static $supportedModes = array();
        
        /**
         * Sets current storage mode.
         *
         * @param string $mode  Mode to set.
         * @access public
         * @static
         */
        public static function SetCurrentMode( $mode ) {
            self::$CurrentMode = $mode;
        }
        
        /**
         * Sets supported modes for add/update actions.
         *
         * @param array $modes
         * @access public
         * @static
         */
        public static function SetSupportedModes( $modes ) {
            self::$supportedModes = $modes;
        }
        
        /**
         * Loads factory to manage tree.
         * 
         * @access public
         * @static
         */
        private static function loadFactory( $withSupports = false ) {
            if ( ! Package::Load( "Base.Tree/" . self::$CurrentMode )  ) {
                Logger::Error( "Storage mode " . self::$CurrentMode . " not supported!" );
            }
            
            if ( $withSupports ) {
                foreach ( self::$supportedModes as  $key => $mode ) {
                    Package::Load( "Base.Tree/" . self::$CurrentMode );
                    
                    if ( empty( Package::$Packages["Base.Tree/" . self::$CurrentMode] ) ) {
                        unset( self::$supportedModes[$key] );
                    }
                }
            }
        }
        
        

        /**
         * Selects all children of the specified tree node.
         *
         * @param BaseTreeObject $object  Root tree object.
         * @param array $mapping          Mapping of the object.
         * @param string $connectionName  Name of the database connection to use
         * @static 
         * @return array
         */
        public static function Get( $searchArray = array(), $options = array(), $object = null, $mapping, $connectionName = "" ) {
            self::loadFactory();

            $factoryName = self::$CurrentMode . "Factory";
            $factory = new $factoryName();
            $mapping["fields"] = array_merge( $mapping["fields"], self::$mapping["fields"] );
            $mapping["search"] = array_merge( $mapping["search"], self::$mapping["search"] );
            
            return ( $factory->Get( $searchArray, $options, $object, $mapping, $connectionName ) );
        }
        
        /**
         * Get node element by id.
         *
         * @param integer $id             Id of the object.
         * @param array $searchArray      Search array.
         * @param array $options          Array of the options to use.
         * @param BaseTreeObject $object  Root object to use.
         * @param array $mapping          Mapping for the object.
         * @param string $connectionName  Name of hte database connection to use.
         * @param string $mode            Mode of the tree storage.
         * @return BaseTreeObject
         */
        public static function GetById( $id, $searchArray, $options, $object, $mapping, $connectionName ) {
            self::loadFactory();
            
            $factoryName = self::$CurrentMode . "Factory";
            $factory = new $factoryName();
            $mapping["fields"] = array_merge( $mapping["fields"], self::$mapping["fields"] );
            $mapping["search"] = array_merge( $mapping["search"], self::$mapping["search"] );
            
            return ( $factory->GetById( $id, $searchArray, $options, $object, $mapping, $connectionName ) );
        }
        
        
        /**
         * Gets one of the tree elements. 
         *
         * @param array $searchArray      Search array.
         * @param array $options          Array of the options to use.
         * @param array $mapping          Mapping for the object.
         * @param string $connectionName  Name of hte database connection to use.
         * @param string $mode            Mode of the tree storage.
         * @return BaseTreeObject
         */
        public static function GetOne( $searchArray = array(), $options = array(), $mapping = array(), $connectionName = null ) {
            self::loadFactory();
            
            $factoryName = self::$CurrentMode . "Factory";
            $factory = new $factoryName();
            $mapping["fields"] = array_merge( $mapping["fields"], self::$mapping["fields"] );
            $mapping["search"] = array_merge( $mapping["search"], self::$mapping["search"] );
            
            return ( $factory->GetOne( $searchArray, $options, $mapping, $connectionName ) );
        }
        
        
        /**
         * Selects count of the element.
         *
         * @param BaseTreeObject $object  Root tree object.
         * @param array $mapping          Mapping of the object.  
         * @param string $connectionName  Name of the database connection to use.
         */
        public static function Count( $searchArray = null, $mapping, $options = null, $connectionName = "" ) {
            self::loadFactory();
            
            $factoryName = self::$CurrentMode . "Factory";
            $factory = new $factoryName();

            $mapping["fields"] = array_merge( $mapping["fields"], self::$mapping["fields"] );
            $mapping["search"] = array_merge( $mapping["search"], self::$mapping["search"] );
            
            return ( $factory->Count( $searchArray, $options, $mapping, $connectionName ) );
        }

        /**
         * Adds new object to the tree.
         *
         * @param BaseTreeObject $object  Tree object to add.
         * @param array $mapping          Mapping of the object.
         * @param string $connectionName  Connection name to use.
         */
        public static function Add( $object, array $mapping, $connectionName = "" ) {
            self::loadFactory( true );
            
            $factoryName = self::$CurrentMode . "Factory";
            $factory = new $factoryName();
            
            return ( $factory->Add( $object, $mapping, $connectionName ) );
        }
        

        /**
         * Deletes specified tree node.
         *
         * @param BaseTreeObject $object  Tree node to delete.
         * @param array $mapping          Mapping of the object
         * @param string $connectionName  Name of the database connection.
         * @param bool $withObjects       Determines whether deletes objects form the data table.
         */
        public static function Delete( $object, $mapping, $connectionName = "", $withObjects = true ) {
            self::loadFactory();
            
            $factoryName = self::$CurrentMode . "Factory";
            $factory = new $factoryName();
            $mapping["fields"] = array_merge( $mapping["fields"], self::$mapping["fields"] );
            $mapping["search"] = array_merge( $mapping["search"], self::$mapping["search"] );
            
            return ( $factory->Delete( $object, $mapping, $connectionName, $withObjects ) );
        }
        
        
        /**
         * Moves tree node to the other node.
         *
         * @param BaseTreeNode $object       Tree node to move.
         * @param BaseTreeNode $destination  Destination tree node to move.
         * @param array $mapping             Mapping of the object.   
         * @param string $connectionName     Name of the database connection to use.
         */
        public static function Move( $object, $destination, $mapping, $connectionName = null ) {
            self::loadFactory( true );
            
            $factoryName = self::$CurrentMode . "Factory";
            $factory = new $factoryName();
            $mapping["fields"] = array_merge( $mapping["fields"], self::$mapping["fields"] );
            $mapping["search"] = array_merge( $mapping["search"], self::$mapping["search"] );
            
            return ( $factory->Move( $object, $destination, $mapping, $connectionName ) );
        }
        

        /**
         * Copies tree node to the other node.
         *
         * @param BaseTreeNode $object       Tree node to copy.
         * @param BaseTreeNode $destination  Destination tree node to copy.
         * @param array $mapping             Mapping of the object.   
         * @param string $connectionName     Name of the database connection to use.
         */
        public static function Copy( $object, $destination, $mapping, $connectionName = null ) {
            self::loadFactory( true );
            
            $factoryName = self::$CurrentMode . "Factory";
            $factory = new $factoryName();
            $mapping["fields"] = array_merge( $mapping["fields"], self::$mapping["fields"] );
            $mapping["search"] = array_merge( $mapping["search"], self::$mapping["search"] );
            
            return ( $factory->Copy( $object, $destination, $mapping, $connectionName ) );
        }
        
        /**
         * Updates tree node data and/or tree structure
         *
         * @param mixed $object           node to update.
         * @param mixed $destination      Parent node for the target instance.
         * @param array $mapping          Object mapping.
         * @param string $connectionName  Name of the database connection to use.
         * @param mode $mode              Tree mode.
         */
        public static function Update( $object, $destination, $mapping, $connectionName = null ) {
            self::loadFactory();
            
            $factoryName = self::$CurrentMode . "Factory";
            $factory = new $factoryName();
            $mapping["fields"] = array_merge( $mapping["fields"], self::$mapping["fields"] );
            $mapping["search"] = array_merge( $mapping["search"], self::$mapping["search"] );
            
            return ( $factory->Update( $object, $destination, $mapping, $connectionName ) );
        }

        
        /**
         * Gets the branch of the specified node.
         *
         * @param BaseTreeNode $object          Object to get branch.
         * @param array        $mapping         Object mapping array.
         * @param string       $connectionName  Connection name to use in query.
         * @return array
         */
        public static function GetBranch( $object, $mapping, $connectionName = null ) {
            self::loadFactory();
            
            $factoryName = self::$CurrentMode . "Factory";
            $factory = new $factoryName();
            $mapping["fields"] = array_merge( $mapping["fields"], self::$mapping["fields"] );
            $mapping["search"] = array_merge( $mapping["search"], self::$mapping["search"] );
            
            return ( $factory->GetBranch( $object, $mapping, $connectionName ) );
        }
        
        /**
         * Gets children nodes for specified level.
         *
         * @param BaseTreeNode $object    Parent tree node.
         * @param array $searchArray      Array of the search parameters.
         * @param array $options          Array of the options to use.
         * @param integer $level          Max level to get the children.
         * @param string $connectionName  Name of the database connection to use.
         * @param string $mode            Mode to use.
         * @return array
         */
        public static function GetChildren( $object, $searchArray = array(), $options = array(), $level = 1, $mapping, $connectionName = null ) {
            self::loadFactory();
            
            $factoryName = self::$CurrentMode . "Factory";
            $factory = new $factoryName();
            $mapping["fields"] = array_merge( $mapping["fields"], self::$mapping["fields"] );
            $mapping["search"] = array_merge( $mapping["search"], self::$mapping["search"] );
            
            return ( $factory->GetChildren( $object, $searchArray, $options, $level, $mapping, $connectionName ) );
        }

        /**
         * Validates the tree.
         *
         * @param array $mapping          Object mapping.
         * @param string $connectionName  Name of the database connection to use.
         */
        public static function Check( $mapping, $connectionName = "" ) {
            return false;
        }


        /**
         * Restore tree from the base table.
         *
         * @param array $mapping
         * @param string $connectionName Name of the database connection to use.
         */
        public static function Restore( $mapping, $connectionName = "" ) {
            return false;
        }
    }
?><?php
    abstract class BaseTreeGetAction extends BaseGetAction {
    }
?><?php
    define( "INDEX_MODE", "index" );
    define( "SELECTOR_MODE", "selector" );

    Package::Load( "Eaze.Helpers" );
    
    /**
     * Base Tree Object Factory
     * 
     * @package Base
     * @subpackage Base.Tree
     * @author Rykin Maxim
     */
    class BaseTreeHelper {
        /**
         * Collapses objects to the tree.
         *
         * @param array $objects  List of the objects to collapse.
         */
        public static function Collapse( $objects ) {
            $tree = array();
            
            if ( empty( $objects ) ) {
                return array();
            }

            foreach ( $objects as $object ) {
                if ( empty( $objects[$object->parentId] ) ) {
                    $tree[$object->objectId] = $object;
                } else {
                    $objects[$object->parentId]->nodes[$object->objectId] = $object;
                    $object->parent = $objects[$object->parentId];
                }
            }
            
            return $tree;
        }

        /**
         * Renders Tree to the control
         *
         * @param array $objects  Array of root elements.
         * @param string $mode    Mode for render.
         */
        protected static function RenderToForm( $objects, $control, $field, $prefix, $mode = "index" ) {
            if ( !is_array( $objects ) ) {
                $objects = array( $objects );
            }
            
            echo '<ul id="' . $control . '" class="filetree">';

            foreach ( $objects as $object ) {
                self::drawElement( $object, $field, $prefix, $mode );
            }

            echo "</ul>";
            
            self::LoadScript( $control );
        }
        
        
        /**
         * Recursive Output For Tree Element
         *
         * @param array $objects
         * @param string $mode
         */
        private static function RecurciveTreeOuput( $objects, $field, $prefix, $mode = INDEX_MODE ) {
            if ( count( $objects ) !== 0 ) {
                echo "<ul>";
                    foreach ( $objects as $object ) {
                        self::drawElement( $object, $field, $prefix, $mode );
                    }
                echo "</ul>";
            }
        }
        
        /**
         * Draw A Tree Element.
         *
         * @param BaseTreeObject $object  Tree element to show.
         * @param string $field           Title field.
         * @param string $prefix          Prefix for the url.
         * @param string $mode            Mode for output.
         */
        private static function drawElement( $object, $field, $prefix, $mode = INDEX_MODE ) {
            switch ( $mode ) {
                case INDEX_MODE:
?>                        
                <li id="node_<?= $object->getFormattedPath() ?>"><span class="folder"><?= $object->$field ?></span>
                <span class="toolbox"><a href='<?= Site::GetWebPath( "vt://$prefix/add/{$object->path}" ) ?>'>add</a> <a href="<?= Site::GetWebPath( "vt://$prefix/edit/{$object->path}" ) ?>">edit</a> <a href="javascript:removeNode('<?= $object->getFormattedPath() ?>');">delete</a></span>
                <?php self::RecurciveTreeOuput( $object->nodes, $field, $prefix, $mode ) ?>
                </li>
<?php
                    break;
                case SELECTOR_MODE:
?>                        
                <li id="node_<?= $object->getFormattedPath() ?>">
                <span class="folder"><input type="radio" name="nodeId" value="<?= $object->path ?>" /><?= $object->$field ?></span>
                <?php self::RecurciveTreeOuput( $object->nodes, $field, $prefix, $mode ) ?>
                </li>
<?php                        
                    break;
            }
        }
        
        /**
         * Loads javascript for delete support.
         *
         * @param string $control  Control name.
         */
        private static function LoadScript( $control ) {
?>
    <script type="text/javascript">
		function removeNode( objectId ) {
            if ( confirm( 'delete?' ) ) {
                $( '#node_' + objectId ).hide();
                
                $.get( '<?= Site::GetWebPath( "vt://questions/rubrics/delete/" ) ?>' + objectId.replace( /_/g,"."), {}, function(data) {
                    $("#statusBar").html( "<b>{lang:vt.common.deleted}</b>").animate({opacity: 1}).animate({opacity: 0}, 300);
                } );
            }
        }
	</script>
<?php
        }
    }
?><?php
    /**
     * Base Tree Object
     * 
     * @package Base
     * @subpackage Base.Tree
     * @author Rykin Maxim
     */
    class BaseTreeObject {
        /**
         * Object id.
         *
         * @var int
         */
        public $objectId = null;
        
        /**
         * Parent object id.
         *
         * @var int
         */
        public $parentId = null;
        
        /**
         * Parent tree node.
         * 
         * @var BaseTreeObject.
         */
        public $parent = null;
        
        /**
         * Material path to the node.
         *
         * @var string
         */
        public $path = "";
        
        /**
         * Right key for nested sets.
         *
         * @var int
         */
        public $rKey = 0;
        
        /**
         * Left key for nested sets.
         *
         * @var int
         */
        public $lKey = 0;
        
        /**
         * Node level in the tree.
         *
         * @var int
         */
        public $level = 0;
        
        /**
         * Children nodes list.
         *
         * @var array
         */
        public $nodes = array();
        
        
        /**
         * Represents tree branch as array of the tree node.
         *
         * @return array
         */
        public function GetBranch() {
            $result = array();
            
            while ( !empty( $obj->parent ) ) {
                $result[] = clone $obj->parent;
                $obj = $obj->parent;
            }
            
            $result = array_reverse( $result );
            
            return $result;
        }
        
        public function GetParentPath() {
            $return = "";
            
            $path = $this->path;

            if ( !empty( $path ) ) {
                $pathes = explode( '.', $this->path );
                $return = "";
                
                for ( $i = 0; $i < count( $pathes ) - 1; $i++ ) {
                    $return .= $pathes[$i] . ".";
                }
                
                return ( rtrim( $return, '.' ) );
            } else if ( !empty( $this->parent ) ) {
                return ( $this->parent->path );
            }
            
            return $return;
        }
        
        
        public function GetFormattedPath( $format = "_" ) {
            return str_replace( ".", $format, $this->path );
        }
    }
?><?php
    /**
     * Base Abstract Save Action
     *
     * @author Sergey Bykov
     * @package Eaze
     * @subpackage Eaze.Model
     */
    abstract class BaseTreeSaveAction {
        /**
         * Current Factory
         *
         * @var IFactory
         */
        public static $factory; 
        
        /**
         * Current Object;
         *
         * @var object
         */
        protected $currentObject;
        
        /**
         * Original Object
         *
         * @var unknown_type
         */
        protected $originalObject;
         
        /**
         * Options for Get Object
         *
         * @var array
         */
        protected $options = array(
            "hideDisabled" => false
            , "withLists"  => true
        );
        
        
        /**
         * Abstract Add
         *
         * @param object $object
         */
        abstract protected function add( $object );
        
        
        /**
         * Abstract Update
         *
         * @param object $object
         */
        abstract protected function update( $object, $path = null );
        
        /**
         * Abstract Delete.
         *
         * @param object $object
         */
        abstract protected function delete( $object );
        
        
        /**
         * Abstract Validate
         *
         * @param object $object
         */
        abstract protected function validate( $object, $parentPath = null );
        
        
         /**
         * Abstract Get Search
         *
         * @return array
         */
        abstract protected function getSearch();
        
        
        /**
         * Abstract Get Object From Request
         *
         * @param object $originalObject
         */
        abstract protected function getFromRequest( $originalObject = null );
        
        
        /**
         * Abstract Set Foreign Lists
         *
         */
        abstract protected function setForeignLists();
        
        /**
         * Abstract Get Original Object.
         *
         */
        abstract protected function getOriginalObject();
        
        
        /**
         * Execute Action
         *
         * @return string
         */
        public function Execute() {
        	
            $addForm     = Request::getInteger( "addForm" );
            $editForm    = Request::getInteger( "editForm" );
            $deleteForm  = Request::getInteger( "deleteForm" );
            
            $mode = Request::getString( "mode" );
            $searchArray = $this->getSearch();
            $this->getOriginalObject();
            
            switch ( $mode ) {
                case "add" :
                    $object = $this->getFromRequest( $this->originalObject );
                    break;
                case "update" :
                    $object = $this->originalObject;
                    break;
                case "delete" :
                    $objectPath = Page::$RequestData[1];
                    $ids = explode( ".", $objectPath );

                    $object = self::$factory->GetById( $ids[count($ids) - 1] ); 
            }
            
            /** delete mode */
            if ( $deleteForm == 1 ) {
                $errors = $this->validate( $object );
                
                if ( !empty( $object ) && empty( $errors["root"] ) ) {
                    $this->delete( $object );
                }
                return null;
            }
            
            $this->setForeignLists();
            
            /** edit mode */
            if ( true == is_null( $object ) ) {
                $object = $this->getFromRequest();
            }
            
    
            //Add
            if ( (1 == $addForm) || (1 == $editForm) ) {
                $object = $this->getFromRequest( $this->originalObject );
                
                /// Proccess Validate
                $vars = get_class_vars( get_class( self::$factory ) );
                $class    = $vars["mapping"]["class"];
                $class[0] = strtolower( $class[0] );
                
                $array          = Request::getArray( $class );
                $path           = $array["parent.path"];
                
                $errors = $this->validate( $object, $path );
                
                if ( empty( $errors ) ) {
                    if ( $addForm == 1 ) {
                        $result = $this->add( $object );
                    } elseif( 1 == $editForm ) {
                        $result = $this->update( $object, $path );
                    }
                    
                    if ( $result === false ) {
                        $errors["fatal"] = "database";
                        Response::setParameter( "errors", $errors );
                    } else {
                        return "success";
                    }
                } else {
                    Response::setArray(  "errors", $errors );
                }
            }
            
            Response::setParameter( "object", $object );
        }
    }
?><?php
    /**
     * Tree Factory Abstract Class.
     *
     * @package Base
     * @subpackage Tree
     * @author Rykin Maxim
     */
    interface ITreeFactory {

        /**
         * Selects all children of the specified tree node.
         *
         * @param BaseTreeObject $object  Root tree object.
         * @param array $mapping          Mapping of the object.
         * @param string $connectionName  Name of the database connection to use
         * @static
         * @return array
         */
        public static function Get( $searchArray = array(), $options = array(), $object = null, $mapping, $connectionName = null );


        /**
         * Get node element by id.
         *
         * @param integer $id             Id of the object.
         * @param array $searchArray      Search array.
         * @param array $options          Array of the options to use.
         * @param BaseTreeObject $object  Root object to use.
         * @param array $mapping          Mapping for the object.
         * @param string $connectionName  Name of hte database connection to use.
         * @param string $mode            Mode of the tree storage.
         * @return BaseTreeObject
         */
        public static function GetById( $id, $searchArray, $options, $object, $mapping, $connectionName );


        /**
         * Gets one of the tree elements.
         *
         * @param array $searchArray      Search array.
         * @param array $options          Array of the options to use.
         * @param array $mapping          Mapping for the object.
         * @param string $connectionName  Name of hte database connection to use.
         * @param string $mode            Mode of the tree storage.
         * @return BaseTreeObject
         */
        public static function GetOne( $searchArray = array(), $options = array(), $mapping = array(), $connectionName = null );


        /**
         * Selects count of the element.
         *
         * @param BaseTreeObject $object  Root tree object.
         * @param array $mapping          Mapping of the object.
         * @param string $connectionName  Name of the database connection to use.
         */
        public static function Count( $searchArray = null, $object = null, $mapping, $connectionName = "" );


        /**
         * Adds new object to the tree.
         *
         * @param BaseTreeObject $object  Tree object to add.
         * @param array $mapping          Mapping of the object.
         * @param string $connectionName  Connection name to use.
         */
        public static function Add( $object, array $mapping, $connectionName = "" );


        /**
         * Deletes specified tree node.
         *
         * @param BaseTreeObject $object  Tree node to delete.
         * @param array $mapping          Mapping of the object
         * @param string $connectionName  Name of the database connection.
         * @param bool $withObjects       Determines whether deletes objects form the data table.
         */
        public static function Delete( $object, $mapping, $connectionName = "", $withObjects = true );


        /**
         * Moves tree node to the other node.
         *
         * @param BaseTreeNode $object       Tree node to move.
         * @param BaseTreeNode $destination  Destination tree node to move.
         * @param array $mapping             Mapping of the object.
         * @param string $connectionName     Name of the database connection to use.
         */
        public static function Move( $object, $destination, $mapping, $connectionName = null );


        /**
         * Copies tree node to the other node.
         *
         * @param BaseTreeNode $object       Tree node to copy.
         * @param BaseTreeNode $destination  Destination tree node to copy.
         * @param array $mapping             Mapping of the object.
         * @param string $connectionName     Name of the database connection to use.
         */
        public static function Copy( $object, $destination, $mapping, $connectionName = null );


        /**
         * Updates tree node data and/or tree structure
         *
         * @param mixed $object           node to update.
         * @param mixed $destination      Parent node for the target instance.
         * @param array $mapping          Object mapping.
         * @param string $connectionName  Name of the database connection to use.
         * @param mode $mode              Tree mode.
         */
        public static function Update( $object, $destination, $mapping, $connectionName = null );


        /**
         * Validates the tree.
         *
         * @param array $mapping          Object mapping.
         * @param string $connectionName  Name of the database connection to use.
         */
        public static function Check( $mapping, $connectionName = "" );


        /**
         * Restore tree from the base table.
         *
         * @param array $mapping
         * @param string $connectionName Name of the database connection to use.
         */
        public static function Restore( $mapping, $connectionName = "" );

        /**
         * Gets the node branch.
         *
         * @param BaseTreeNode $object          Start node to get branch.
         * @param array        $mapping         Object mapping to use.
         * @param string       $connectionName  Name of the connection to use.
         */
        public static function GetBranch( $object, $mapping, $connectionName = null );


        /**
         * Gets children nodes for specified level.
         *
         * @param BaseTreeNode $object    Parent tree node.
         * @param array $searchArray      Array of the search parameters.
         * @param array $options          Array of the options to use.
         * @param integer $level          Max level to get the children.
         * @param string $connectionName  Name of the database connection to use.
         * @param string $mode            Mode to use.
         * @return array
         */
        public static function GetChildren( $object, $searchArray = array(), $options = array(), $level = 1, $mapping, $connectionName = null );
    }
?><?php
    /**
     * Tree Factory Abstract Class.
     *
     * @package Base
     * @subpackage Base.Tree
     * @author Rykin Maxim
     */
    abstract class TreeFactory implements ITreeFactory {
        /**
         * Gets results form the data set.
         *
         * @param IDataSet $ds    Result Data Set.
         * @param array $options  Array of options.
         * @static
         * @access private
         * @return array
         */
        public static function GetResults( DataSet $ds, $options = array(), $mapping, $connectionName = null ) {
            $structure = BaseFactory::GetObjectTree( $ds->Columns );
            $result    = array();
            $keys      = BaseFactoryPrepare::GetPrimaryKeys( $mapping );
            $key       = ( empty( $keys[0] ) ) ? null : $keys[0];

            while ( $ds->next() ) {
                if ( !empty($structure[$key])) {
                    $result[$ds->getParameter( $key )] = BaseFactory::getObject( $ds, $mapping, $structure );
                } else {
                    $result[] = self::getObject( $ds, $mapping, $structure );
                }
            }

            // With Lists Mode
            if ( !empty( $options[BaseFactory::WithLists] )
                    && !empty( $mapping["lists"] )
                    && !empty( $result ) ) {
                foreach ( $mapping["lists"] as $name => $value ) {
                    $ids         = array_keys( $result );
                    $factoryName = $value["foreignKey"] . "Factory";
                    $factory     = new $factoryName();
                    $listArray = $factory->Get( array( "_" . $value["name"] => $ids ), null, $connectionName );

                    BaseFactoryPrepare::Glue( $result, $listArray, $value["name"], $name );
                }
            }

            return $result;
        }
    }
?>