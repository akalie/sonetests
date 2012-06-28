<?php
    class ADJListsFactory extends TreeFactory {
        
        /**
         * 
         * @param BaseTreeObject $object  Tree object to add. 
         * @param array $mapping          Mapping of the object. 
         * @param string $connectionName  Connection name to use. 
         * @see TreeFactory::Add()
         */
        public static function Add( $object, array $mapping, $connectionName = "", $withSupport = array( "TREEMODE_ADJ" ) ) {
            if ( empty( $object->parent ) ) {
                $object->parentId = null;
            }

            $connection = ConnectionFactory::Get( $connectionName ) ;

            $connection->begin();

            $result = true;

            if ( empty( $object->objectId ) ) {
                $result           = BaseFactory::Add( $object, $mapping, $connectionName );
                $object->objectId = BaseFactory::GetCurrentId( $mapping, $connectionName );
            }

            if ( $result ) {
                $command = ADJListsPrepare::PrepareAddCommand( $mapping["table"] . "Tree", $connection );
                $cmd = new SqlCommand( $command, $connection );

                $cmd->SetInt( "@objectId", $object->objectId );
                $cmd->SetInt( "@parentId", $object->parentId );
                $cmd->SetInt( "@level", $object->parent->level + 1);

                if ( $cmd->ExecuteNonQuery() ) {
                    $connection->commit();
                    return true;
                }
            }

            $connection->rollback();
            return false;
        }
        
        /**
         * 
         * @param array $mapping          Object mapping. 
         * @param string $connectionName  Name of the database connection to use. 
         * @see TreeFactory::Check()
         */
        public static function Check( $mapping, $connectionName = "" ) {
            //TODO - Insert your code here
        }
        
        /**
         * 
         * @param BaseTreeNode $object       Tree node to copy. 
         * @param BaseTreeNode $destination  Destination tree node to copy. 
         * @param array $mapping             Mapping of the object. 
         * @param string $connectionName     Name of the database connection to use. 
         * @see TreeFactory::Copy()
         */
        public static function Copy( $object, $destination, $mapping, $connectionName = null ) {
            //TODO - Insert your code here
        }
        
        /**
         * 
         * @param BaseTreeObject $object  Root tree object. 
         * @param array $mapping          Mapping of the object. 
         * @param string $connectionName  Name of the database connection to use. 
         * @see TreeFactory::Count()
         */
        public static function Count( $searchArray = array(), $options = array(), $mapping, $connectionName = "" ) {
            $connection = ConnectionFactory::Get( $connectionName );

            if ( !empty( $object ) ) {
                if ( $object instanceof BaseTreeObject ) {
                    $object = BaseFactory::GetById( $object->objectId, NULL, $mapping, null, $connectionName );
                } else {
                    $object = BaseFactory::GetById( $object, NULL, $mapping, null, $connectionName );
                }
            }

            $result     = ( !empty( $object ) && !empty( $options[OPTION_WITH_PARENT] ) ) ? array( $object->objectId => $object ) : array();
            $levelArray = ( empty( $object ) ? array() : array( $object->objectId => $object ) );
            $level      = 1;

            while ( true ) {
                Logger::Debug( 'Getting tree level %s', $level );

                if ( !empty( $levelArray ) ) {
                    $searchArray["_parentId"] = array_keys( $levelArray );
                    unset( $levelArray );
                }

                $command = BaseFactoryPrepare::PrepareGetString( $searchArray, $mapping, $options, $connection );
                $cmd     = new SqlCommand( $command, $connection );

                BaseFactory::ProcessSearchParameters( $searchArray, $mapping, $options, $cmd );

                if ( BaseFactory::CanPages( $mapping ) ) {
                    $cmd->SetInteger( "@pageOffset", $searchArray[BaseFactoryPrepare::Page] * $searchArray[BaseFactoryPrepare::PageSize] );
                    $cmd->SetInteger( "@pageSize",   $searchArray[BaseFactoryPrepare::PageSize] );
                }

                // memcache
                if ( !empty( $mapping["flags"]["CanCache"] ) && MemcacheHelper::IsActive() ) {
                    $cacheKey    = $mapping["class"] . "_query_" . md5($cmd->GetQuery());
                    $cacheResult = MemcacheHelper::Get( $cacheKey );

                    if ( !$cacheResult === false ) {
                        $levelArray = $cacheResult;
                    }
                }


                if ( !isset( $levelArray ) || $level == 1 ) {
                    $ds     = $cmd->execute();
                    $levelArray = self::GetResults( $ds, $options, $mapping, $connectionName );
                }

                // memcached hack
                if ( !empty( $mapping["flags"]["CanCache"] ) && MemcacheHelper::IsActive() ) {
                    MemcacheHelper::Replace( $mapping["class"], $cacheKey, $levelArray );
                }

                if ( !empty( $options[OPTION_LEVEL_MAX] ) && $options[OPTION_LEVEL_MAX] < $level ) {
                    break;
                }

                if ( !empty( $options[OPTION_LEVEL_MIN] ) && $options[OPTION_LEVEL_MIN] > $level ) {
                    $level++;
                    continue;
                }

                if ( empty( $levelArray ) ) break;
                $level++;

                foreach ( $levelArray as $key => $value ) {
                    $result[$value->objectId] = $value;
                }
            }

            // calculate pagecount
            if ( !BaseFactory::CanPages( $mapping, $options ) ) {
                $searchArray[BaseFactoryPrepare::PageSize] = 1;
            }

            if ( 0 == $searchArray[BaseFactoryPrepare::PageSize] ) {
                $searchArray[BaseFactoryPrepare::PageSize] = BaseFactoryPrepare::PageSizeCount;
            }

            return ( count( $result ) / $searchArray[BaseFactoryPrepare::PageSize] );
        }
        
        /**
         * 
         * @param BaseTreeObject $object  Tree node to delete. 
         * @param array $mapping          Mapping of the object 
         * @param string $connectionName  Name of the database connection. 
         * @param bool $withObjects       Determines whether deletes objects form the data table. 
         * @see TreeFactory::Delete()
         */
        public static function Delete( $object, $mapping, $connectionName = "", $withObjects = true ) {
            if ( empty( $object ) ) {
                return false;
            }

            if ( empty( $object->objectId ) ) {
                return false;
            }

            $connection = ConnectionFactory::Get( $connectionName );

            $command = ADJListsPrepare::PrepareDeleteCommand( $mapping, $connection );
            $cmd = new SqlCommand(
                $command
                , $connection
            );


            $childrenIds = array();
            $ids         = array( $object->objectId );

            $connection->begin();

            while ( !empty( $ids ) ) {
                $childrenIds = array_merge($childrenIds, $ids);
                $cmd = new SqlCommand( $command, $connection );

                $cmd->SetList("@_objectIds", $ids, TYPE_INTEGER);
                //$cmd->SetInteger( "@level", $level ); // WTF???
                $result = $cmd->ExecuteNonQuery();

                if ( !$result ) {
                    $connection->rollback();
                    return false;
                }

                $objects = self::Get( array( "_parentId" => $ids ), array(), null, $mapping, $connectionName );
                $ids     = array_keys( $objects );
                //$level   = $level + 1; //WTF???
            }

            if ( $withObjects ) {
                $object->statusId = 3;
                $result = BaseFactory::UpdateByMask( $object, array( "statusId" ), array( "_id" => $childrenIds ), $mapping, $connectionName );
            }

            if ( !$result ) {
                $connection->rollback();
                return false;
            }

            $connection->commit();
            return true;
        }
        
        /**
         * 
         * @param BaseTreeObject/integer $object  Root tree object. 
         * @param array $mapping          Mapping of the object. 
         * @param string $connectionName  Name of the database connection to use 
         * @static  
         * @return array 
         * @see TreeFactory::Get()
         */
        public static function Get( $searchArray = array(), $options = array(), $object = null, $mapping, $connectionName = "") {
            $connection = ConnectionFactory::Get( $connectionName );

            if ( !empty( $object ) ) {
                if ( $object instanceof BaseTreeObject ) {
                    $object = BaseFactory::GetById( $object->objectId, NULL, $mapping, null, $connectionName );
                } else {
                    $object = BaseFactory::GetById( $object, NULL, $mapping, null, $connectionName );
                }
            }

            $result     = ( !empty( $object ) && !empty( $options[OPTION_WITH_PARENT] ) ) ? array( $object->objectId => $object ) : array();
            $levelArray = ( empty( $object ) ? array() : array( $object->objectId => $object ) );
            $level      = 1;
            
            while ( true ) {
                Logger::Debug( 'Getting tree level %s', $level );
                
                if ( !empty( $levelArray ) ) {
                    $searchArray["_parentId"] = array_keys( $levelArray );
                    unset( $levelArray );
                }
                
                $command = BaseFactoryPrepare::PrepareGetString( $searchArray, $mapping, $options, $connection );
                $cmd     = new SqlCommand( $command, $connection );
                
                BaseFactory::ProcessSearchParameters( $searchArray, $mapping, $options, $cmd );
                
                if ( BaseFactory::CanPages( $mapping ) ) {
                    $cmd->SetInteger( "@pageOffset", $searchArray[BaseFactoryPrepare::Page] * $searchArray[BaseFactoryPrepare::PageSize] );
                    $cmd->SetInteger( "@pageSize",   $searchArray[BaseFactoryPrepare::PageSize] );
                }

                // memcache
                if ( !empty( $mapping["flags"]["CanCache"] ) && MemcacheHelper::IsActive() ) {
                    $cacheKey    = $mapping["class"] . "_query_" . md5($cmd->GetQuery());
                    $cacheResult = MemcacheHelper::Get( $cacheKey );
                    
                    if ( !$cacheResult === false ) {
                        $levelArray = $cacheResult;
                    }
                }    
                
                
                if ( !isset( $levelArray ) || $level == 1 ) {
                    $ds     = $cmd->execute();
                    $levelArray = self::GetResults( $ds, $options, $mapping, $connectionName );
                }
                
                // memcached hack
                if ( !empty( $mapping["flags"]["CanCache"] ) && MemcacheHelper::IsActive() ) {
                    MemcacheHelper::Replace( $mapping["class"], $cacheKey, $levelArray );
                }
                
                if ( !empty( $options[OPTION_LEVEL_MAX] ) && $options[OPTION_LEVEL_MAX] < $level ) {
                    break;
                }

                if ( !empty( $options[OPTION_LEVEL_MIN] ) && $options[OPTION_LEVEL_MIN] > $level ) {
                    $level++;
                    continue;
                }
                
                if ( empty( $levelArray ) ) break;
                $level++;
                
                foreach ( $levelArray as $key => $value ) {
                    $result[$value->objectId] = $value;
                }
            }
            
            return $result;
        }
        
        /**
         * 
         * @param BaseTreeNode $object          Start node to get branch. 
         * @param array        $mapping         Object mappping to use. 
         * @param string       $connectionName  Name of the conneciton to use. 
         * @see TreeFactory::GetBranch()
         */
        public static function GetBranch( $object, $mapping, $connectionName = null ) {
            if ( $object->level == 1 ) {
                return array( $object );
            }
            
            $result = array( $object->objectId => $object );
            $o = $object;

            while ( !empty( $o->parentId ) ) {
                $o = self::GetById( $o->parentId, array(), array(), null, $mapping, $connectionName );
                $result[$o->objectId] = $o;
            }
            
            return array_reverse( $result );
        }
        
        /**
         * 
         * @param integer $id             Id of the object. 
         * @param array $searchArray      Search array. 
         * @param array $options          Array of the options to use. 
         * @param BaseTreeObject $object  Root object to use. 
         * @param array $mapping          Mapping for the object. 
         * @param string $connectionName  Name of hte database connection to use. 
         * @param string $mode            Mode of the tree storage. 
         * @return BaseTreeObject 
         * @see TreeFactory::GetById()
         */
        public static function GetById($id, $searchArray, $options, $object, $mapping, $connectionName) {
            if (  empty( $id ) ) {
                return null;
            }

            $key = null;
            
            foreach ( $mapping["fields"] as $field => $data ) {
                if ( !empty( $data["key"] ) ) {
                    $key = $field;
                    break;
                }
            }
            
            if ( empty( $key ) ) {
                Logger::Warning( 'Class %s has no primary key', $mapping['class'] );
                return null;    
            }
            
            $searchArray[$key] = $id;

            return self::GetOne( $searchArray, $options, $mapping, $connectionName );
        }
        
        /**
         * 
         * @param BaseTreeNode $object    Parent tree node. 
         * @param array $searchArray      Array of the search parameters. 
         * @param array $options          Array of the options to use. 
         * @param integer $level          Max level to get the children. 
         * @param string $connectionName  Name of the database connection to use. 
         * @param string $mode            Mode to use. 
         * @return array 
         * @see TreeFactory::GetChildren()
         */
        public static function GetChildren( $object, $searchArray = array(), $options = array(), $level = 1, $mapping, $connectionName = null) {
            $options[OPTION_LEVEL_MIN] = 1;
            $options[OPTION_LEVEL_MAX] = $level;
            return self::Get( $searchArray, $options, $object, $mapping, $connectionName );
        }


        /**
         *
         * @param array $searchArray      Search array.
         * @param array $options          Array of the options to use.
         * @param array $mapping          Mapping for the object.
         * @param string $connectionName  Name of hte database connection to use.
         *
         * @return BaseTreeObject
         * @see TreeFactory::GetOne()
         */
        public static function GetOne( $searchArray = array(), $options = array(), $mapping = array(), $connectionName = null ) {
            $result = self::Get( $searchArray, $options, null, $mapping, $connectionName );

            $result = BaseTreeHelper::Collapse( $result );

            if ( count( $result ) != 1 ) {
                return null;
            }

            if ( is_array( $result ) ) {
                foreach ( $result as $object ) {
                    return $object;
                }
            }

            return $result;
        }
        
        /**
         * Moves specified nodes to destination node.
         * @param BaseTreeNode $object       Tree node to move. 
         * @param BaseTreeNode $destination  Destination tree node to move. 
         * @param array $mapping             Mapping of the object. 
         * @param string $connectionName     Name of the database connection to use. 
         * @see TreeFactory::Move()
         */
        public static function Move( $object, $destination, $mapping, $connectionName = null) {
            $connection = ConnectionFactory::Get( $connectionName );

            $connection->begin();
            $command = ADJListsPrepare::PrepareUpdateCommand( $mapping, $connection );
            $cmd = new SqlCommand( $command, $connection );

            $cmd->setInt( "@level", $destination->level + 1 );
            $cmd->SetInt( "@objectId", $object->objectId );
            $cmd->SetInt( "@parentId", $destination->objectId );

            $result = $cmd->ExecuteNonQuery();

            if ( !$result ) {
                $connection->rollback();
                return false;
            }

            $command = ADJListsPrepare::PrepareMoveCommand( $mapping, $connection );
            $commandResult = true;

            $ids           = array( $object->objectId );
            $level         = $destination->level + 1;

            while ( !empty( $ids ) ) {
                $cmd = new SqlCommand( $command, $connection );

                $cmd->SetList("@_objectIds", $ids, TYPE_INTEGER);
                $cmd->SetInteger( "@level", $level );
                $result = $cmd->ExecuteNonQuery();

                if ( !$result ) {
                    $connection->rollback();
                    return false;
                }

                $objects = self::Get( array( "_parentId" => $ids ), array(), null, $mapping, $connectionName );
                $ids     = array_keys( $objects );
                $level   = $level + 1;
            }

            $connection->commit();
            return true;
        }
        
        /**
         * 
         * @param array $mapping 
         * @param string $connectionName Name of the database connection to use. 
         * @see TreeFactory::Restore()
         */
        public static function Restore( $mapping, $connectionName = "") {
            
        //TODO - Insert your code here
        }
        
        /**
         * 
         * @param mixed $object           node to update. 
         * @param mixed $destination      Parent node for the target instance. 
         * @param array $mapping          Object mapping. 
         * @param string $connectionName  Name of the database connection to use. 
         * @param mode $mode              Tree mode. 
         * @see TreeFactory::Update()
         */
        public static function Update($object, $destination, $mapping, $connectionName = null) {
            $connection = ConnectionFactory::Get( $connectionName );

            $connection->begin();

            if ( empty ( $object ) ) {
                return false;
            }
            $vars = get_class_vars(get_class( $object ) . "Factory" );

            $result = BaseFactory::Update( $object, $vars["mapping"], $connectionName );

            if ( is_string( $destination ) ) {
                $ids            = explode( '.', $destination );
                $objectId       = $ids[count( $ids ) - 1];

                $destinationNode = BaseTreeFactory::GetById( $objectId, array(), array(), null, $mapping, $connectionName );
            } else if ( is_int($destination) ) {
                $destinationNode = BaseTreeFactory::GetById( $destination, array(), array(), null, $mapping, $connectionName );
            } else if ( is_object( $destination ) ) {
            	$destinationNode = $destination;
            }

            $withMove = !empty($destinationNode) && ( $object->parentId !== $destinationNode->objectId );

            if ( $withMove && $result) {
                $result = self::Move( $object, $destinationNode, $mapping, $connectionName );
            }

            if ( !$result ) {
                $connection->rollback();
                return false;
            }

            $connection->commit();
            return true;
        }
    }

?><?php
    class ADJListsPrepare {
        public static function PrepareGetCommand( $searchArray, $options, $object, $mapping, $connection ) {
            $query       = 'SELECT * FROM  ' . $conn->quote( $mapping["view"] );
            $query      .= BaseFactoryPrepare::PrepareGetOrCountFields( $searchArray, $mapping, $options, $conn );
            $query      .= BaseFactoryPrepare::GetOrderByString( $options, $conn );
            
            return $query;
        }

        public static function PrepareAddCommand( $table, IConnection $conn ) {
            $result = "INSERT INTO " . $conn->quote( $table )
                . sprintf( " ( %s ", $conn->quote( "objectId" ) )
                . sprintf( " , %s ", $conn->quote( "parentId" ) )
                . sprintf( " , %s ", $conn->quote( "level" ) )
                . " ) VALUES ( "
                . " @objectId"
                . " , @parentId"
                . " , @level"
                . ");";

            return $result;
        }

        public static function PrepareMoveCommand( $mapping, $conn ) {
            $result = "UPDATE " . $conn->quote( $mapping["table"] . "Tree" );
            $result .= " SET level = @level";
            $result .= " WHERE " . $conn->quote( "objectId" ) . " IN @_objectIds";

            return $result;
        }

        public static function PrepareUpdateCommand( $mapping, $conn ) {
            $result = "UPDATE " . $conn->quote( $mapping["table"] . "Tree" );
            $result .= " SET level = @level";
            $result .= " , " . $conn->quote( "parentId" ) . " = @parentId";
            $result .= " WHERE " . $conn->quote( "objectId" ) . " = @objectId";

            return $result;
        }

        public static function PrepareDeleteCommand( $mapping, $conn ) {
            $result = "DELETE FROM " . $conn->quote( $mapping["table"] . "Tree" );
            $result .= " WHERE " . $conn->quote( "objectId" ) . " IN @_objectIds";

            return $result;
        }
    }
?>