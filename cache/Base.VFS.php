<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * VfsFile
     *
     * @package Base
     * @subpackage VFS
     */
    class VfsFile {

        /** @var int */
        public $fileId;

        /** @var int */
        public $folderId;

        /** @var VfsFolder */
        public $folder;

        /** @var string */
        public $title;

        /** @var string */
        public $path;

        /** @var string */
        public $params;

        /** @var bool */
        public $isFavorite;

        /** @var string */
        public $mimeType;

        /** @var int */
        public $fileSize;

        /** @var bool */
        public $fileExists;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;

        /** @var DateTimeWrapper */
        public $createdAt;
    }
?><?php
    /**
     * VfsFile Factory
     *
     * @package Base
     * @subpackage VFS
     */
    class VfsFileFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** VfsFile instance mapping  */
        public static $mapping = array (
            'class'       => 'VfsFile'
            , 'table'     => 'vfsFiles'
            , 'view'      => 'getVfsFiles'
            , 'flags'     => array( 'CanPages' => 'CanPages', 'CanCache' => 'CanCache' )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'fileId' => array(
                    'name'          => 'fileId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'folderId' => array(
                    'name'          => 'folderId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'VfsFolder'
                )
                ,'title' => array(
                    'name'          => 'title'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'path' => array(
                    'name'          => 'path'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'params' => array(
                    'name'          => 'params'
                    , 'type'        => TYPE_STRING
                )
                ,'isFavorite' => array(
                    'name'          => 'isFavorite'
                    , 'type'        => TYPE_BOOLEAN
                )
                ,'mimeType' => array(
                    'name'          => 'mimeType'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'fileSize' => array(
                    'name'          => 'fileSize'
                    , 'type'        => TYPE_INTEGER
                )
                ,'fileExists' => array(
                    'name'          => 'fileExists'
                    , 'type'        => TYPE_BOOLEAN
                    , 'nullable'    => 'No'
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                )
                ,'createdAt' => array(
                    'name'          => 'createdAt'
                    , 'type'        => TYPE_DATETIME
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

        /** @return VfsFile[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return VfsFile */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return VfsFile */
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

        /** @return VfsFile */
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
     * VfsFolder
     *
     * @package PandaTrunk
     * @subpackage Common
     */
    class VfsFolder extends BaseTreeObject {

        /** @var int */
        public $folderId;

        /** @var int */
        public $parentFolderId;

        /** @var VfsFolder */
        public $parentFolder;

        /** @var string */
        public $title;

        /** @var bool */
        public $isFavorite;

        /** @var DateTimeWrapper */
        public $createdAt;

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

    /**
     * VfsFolder Factory
     *
     * @package Base
     * @subpackage VFS
     */
    class VfsFolderFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** VfsFolder instance mapping  */
        public static $mapping = array (
            'class'       => 'VfsFolder'
            , 'table'     => 'vfsFolders'
            , 'view'      => 'getVfsFolders'
            , 'flags'     => array( 'CanCache' => 'CanCache', 'IsTree' => 'IsTree' )
            , 'cacheDeps' => array( 'vfsFolders' )
            , 'fields'    => array(
                'folderId' => array(
                    'name'          => 'folderId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'parentFolderId' => array(
                    'name'          => 'parentFolderId'
                    , 'type'        => TYPE_INTEGER
                    , 'foreignKey'  => 'VfsFolder'
                )
                ,'title' => array(
                    'name'          => 'title'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 255
                    , 'nullable'    => 'CheckEmpty'
                    , "searchType"  => SEARCHTYPE_ILIKE
                )
                ,'isFavorite' => array(
                    'name'          => 'isFavorite'
                    , 'type'        => TYPE_BOOLEAN
                )
                ,'createdAt' => array(
                    'name'          => 'createdAt'
                    , 'type'        => TYPE_DATETIME
                    , 'updatable'   => false
                    , 'addable'     => false
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                ))
            , 'lists'     => array()
            , 'search'    => array(
                '_id' => array(
                    'name'         => 'folderId'
                    , 'type'       => TYPE_INTEGER
                    , 'searchType' => SEARCHTYPE_ARRAY
                )
                ,'exactTitle' => array(
                    'name'         => 'title'
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
        public static function Update( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Update( $object, self::$mapping, $options, $connectionName );
        }

        /** @return bool */
        public static function UpdateRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateRange( $objects, self::$mapping, $options, $connectionName );
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
        
        public static function GetCurrentId( $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetCurrentId( self::$mapping, $connectionName );
        }

        public static function Delete( $object, $connectionName = self::DefaultConnection ) {
            return BaseTreeFactory::Delete( $object, self::$mapping, $connectionName );
        }

        public static function DeleteByMask( $searchArray, $connectionName = self::DefaultConnection ) {
            return BaseTreeFactory::DeleteByMask( $searchArray, self::$mapping, $connectionName );
        }

        public static function PhysicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseTreeFactory::PhysicalDelete( $object, self::$mapping, $connectionName );
        }

        public static function LogicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseTreeFactory::LogicalDelete( $object, self::$mapping, $connectionName );
        }

        /** @return VfsFolder */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
		/// Base Tree Operations.
        public static function Move( $object, $destination, $connectionName = self::DefaultConnection, $mode = TREEMODE_LTREE ) {
            return BaseTreeFactory::Move( $object, $destination, self::$mapping, $connectionName, $mode = TREEMODE_LTREE );
        }
        
        public static function Copy( $object, $destination, $connectionName = self::DefaultConnection, $mode = TREEMODE_LTREE ) {
            return BaseTreeFactory::Copy( $object, $destination, self::$mapping, $connectionName, $mode = TREEMODE_LTREE );
        }
        
        public static function Add( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseTreeFactory::Add( $object, self::$mapping, null, $connectionName );
        }
        
        public static function AddRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            // TODO: Implement AddRange() method.
        }

        public static function Get( $searchArray = null, $options = null, $object = null, $connectionName = self::DefaultConnection ) {
            return BaseTreeFactory::Get( $searchArray, $options, $object, self::$mapping, $connectionName );
        }

        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseTreeFactory::GetById( $id, $searchArray, $options, null, self::$mapping, $connectionName );
        }

        public static function GetOne( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseTreeFactory::GetOne( $searchArray, $options, self::$mapping, $connectionName );
        }

        public static function Count( $searchArray, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseTreeFactory::Count( $searchArray, self::$mapping, $options, $connectionName );
        }

        public static function GetChildren( $object, $searchArray, $options = null, $level = null, $connectionName = self::DefaultConnection, $mode = TREEMODE_LTREE ) {
            $level = empty( $level ) ? 1 : $level;
            return BaseTreeFactory::GetChildren( $object, $searchArray, $options, $level, self::$mapping, $connectionName, $mode );
        }
        
        public static function GetBranch( $object, $connectionName = self::DefaultConnection, $mode = TREEMODE_LTREE ) {
            return BaseTreeFactory::GetBranch( $object, self::$mapping, $connectionName, $mode );
        }
    }
?><?php
    Package::Load( "Base.VFS");
    
    class VfsHelper {
 
        /**
         * Form VFS File
         *
         * @param string   $controlName
         * @return string
         */
        public static function FormVfsFile( $controlName, $controlId, $file, $previewType = "none" ) {
        	return self::renderVfsFile( $controlName, $controlId, $file, $previewType, false );
        }
        
        public static function FormVfsMultiFile( $controlName, $controlId, $file, $previewType = "none" ) {
        	return self::renderVfsFile( $controlName, $controlId, $file, $previewType, true );
        }
        
        private static function renderVfsFile( $controlName, $controlId, $file, $previewType = "none", $selfDelete = false ) {
        	if ( !empty($selfDelete) ) {
        		$inputClass = "vfsMultiFile";
        	} else $inputClass = "vfsFile";
            if ( empty( $file ) ) {
                $xhtml = sprintf( '<input type="hidden" class="%s" name="%s" id="%s" vfs:previewType="%s" />'
                    , $inputClass
                    , $controlName
                    , $controlId
                    , $previewType
                );
            } else if ( is_object( $file ) ) {
                $xhtml = sprintf( '<input type="hidden" class="%s" name="%s" id="%s" vfs:previewType="%s" value="%s" vfs:src="%s" vfs:name="%s" />'
                    , $inputClass
                    , $controlName
                    , $controlId
                    , $previewType
                    , $file->fileId
                    , Site::GetWebPath( "vfs://" . $file->path )
                    , FormHelper::RenderToForm( $file->title )
                );
            }
            
            return $xhtml;
        }
        
        /**
         * Form VFS Folder
         *
         * @param string     $controlName
         * @param string     $controlId
         * @param VfsFolder  $folder
         * @return string
         */
        public static function FormVfsFolder( $controlName, $controlId, $folder) {
            if ( empty( $folder ) ) {
                $xhtml = sprintf( '<input type="hidden" class="vfsFolder" name="%s" id="%s" />'
                    , $controlName
                    , $controlId
                );
            } else if ( is_object( $folder ) ) {
                $xhtml = sprintf( '<input type="hidden" class="vfsFolder" name="%s" id="%s" value="%s" vfs:name="%s" />'
                    , $controlName
                    , $controlId
                    , $folder->folderId
                    , FormHelper::RenderToForm( $folder->title )
                );
            }
            
            return $xhtml;
        }
    }
?><?php
    Package::Load( 'Base.Tree' );
    Package::Load( 'Base.VFS' );
    Package::Load( 'Base.VFS/modes' );

    define( 'VFS_MODE_DB', 'vfs_db' );
    define( 'VFS_MODE_FS', 'vfs_fs');
    define( 'VFS_MODE',    VFS_MODE_DB );


    /**
     * VFS Utility
     *
     * @package Base
     * @subpackage Base.Common
     */
    class VfsUtility {
        const InputTypeFolder    = "folder";
        const InputTypeFile      = "file";
        const InputTypePath      = "path";
        const TempPath           = "temp://";
        const RootDir            = "vfs://";

        /**
         * Set Jail
         *
         * @var bool
         */
        public static $Jail = array(
            "favorites"    => true
            , "rootFolder" => null
            , "enabled"    => false
        );

        /**
         * Autoresize Images after upload via $ResizableSettings
         *
         * @var bool
         */
        public static $Resizable = false;

        /**
         * Settings for AutoResize
         *
         * @var array
         */
        public static $ResizableSettings = array(
            "prefix"  => "original_"
            , "keep"  => true
            , "modes" => array(
                array(
                    "prefix"   => "small_"
                    , "width"  => 93
                    , "height" => 93
                    , "scale"  => false
                    , "quality"=> 90
                )
            )
        );


        /**
         * Use Salted FileNames
         * @var bool
         */
        public static $SaltedFileNames = false;

        /**
         * Skeleton Browse Screen
         *
         * @var array
         */
        public static $SkeletonBrowseScreen = array(
            "currentFolder" => null     // current folders
            , "path"        => array()  // of folders
            , "folders"     => array()  // of folders
            , "files"       => array()  // of files
            , "page"        => 0        // current page
            , "pageSize"    => 10       // page size
            , "pageCount"   => 0        // page count
        );


        /**
         * Get Browse Screen
         *
         * @param string $parameter
         * @param string $inputType
         * @param string $vfsMode
         */
        public static function GetBrowseScreen( $parameter = null, $inputType = null, $page = 0, $pageSize = 10, $vfsMode = VFS_MODE ) {
            $response  = array();
            $parameter = self::SetJailRoot( $parameter, $vfsMode );

            switch ( $vfsMode ) {
                case VFS_MODE_DB:
                    $response = VfsDbMode::GetBrowseScreen( $parameter, $inputType, $page, $pageSize );
                    break;
            }
            
            self::SetJail($response);
            
            return self::BrowseResponseToJSON( $response );
        }


        /**
         * Set Jail Root For Query
         * @param mixed $parameter
         * @param string $vfsMode
         * @return mixed
         */
        public static function SetJailRoot( $parameter, $vfsMode ) {
            if ( self::$Jail["enabled"] && !empty(self::$Jail['rootFolder'] )) {
                switch ( $vfsMode ) {
                    case VFS_MODE_DB:
                        $parameter = Convert::ToInt($parameter);
                        if ( !empty( $parameter ) ) {
                            $folder = VfsFolderFactory::GetById( $parameter );
                            if ( !empty( $folder ) ) {
                                $path = VfsFolderFactory::GetBranch($folder);
                                if ( empty( $path[$parameter] ) ) {
                                    $parameter = null;
                                }
                            } else {
                                $parameter = null;
                            }
                        }

                        if ( empty($parameter) ){
                            $parameter = self::$Jail['rootFolder'];
                        }
                        break;
                }
            }
            
            return $parameter;
        }

        /**
         * Set Jail After Response
         * @param array $params
         */
        public static function SetJail( &$response ) {
            if ( !self::$Jail["enabled"] || empty( $response )) {
                return false;
            }

            $rootFolderId = self::$Jail['rootFolder'];
            if ( !empty($rootFolderId ) && !empty( $response["path"][$rootFolderId] ) ) {
                $i = 0;
                $f = current($response['path']);
                while( $f->folderId != $rootFolderId ) {
                    array_shift($response['path']);
                    $f = current($response['path']);
                    if ( $i ++ > 30 ) {
                        break;
                    }
                }
            }

            if ( !self::$Jail['favorites'] ) {
                $response['favorites'] = array();
            }
        }


        /**
         * Star Folder
         *
         * @param integer $folderId
         * @param string  $vfsMode
         * @return string
         */
        public static function Star( $folderId, $vfsMode = VFS_MODE ) {
            $response = array();
            switch ( $vfsMode ) {
                case VFS_MODE_DB:
                    $response["result"] = VfsDbMode::StarFolder( $folderId, true );
            }

            return ObjectHelper::ToJSON( $response );
        }


        /**
         * Unstar Folder
         *
         * @param string $folderId
         * @param string $vfsMode
         * @return string
         */
        public static function UnStar( $folderId, $vfsMode = VFS_MODE ) {
            $response = array();
            switch ( $vfsMode ) {
                case VFS_MODE_DB:
                    $response["result"] = VfsDbMode::StarFolder( $folderId, false );
            }

            return ObjectHelper::ToJSON( $response );
        }


        /**
         * Delete Folder
         *
         * @param string $folderId
         * @param string $vfsMode
         * @return string
         */
        public static function DeleteFolder( $folderId, $vfsMode = VFS_MODE ) {
            $response = array();
            switch ( $vfsMode ) {
                case VFS_MODE_DB:
                    $response["result"] = VfsDbMode::DeleteFolder( $folderId );
            }

            return ObjectHelper::ToJSON( $response );
        }


        /**
         * Delete File
         *
         * @param string $fileId
         * @param string $vfsMode
         * @return string
         */
        public static function DeleteFile( $fileId, $vfsMode = VFS_MODE ) {
            $response = array();
            switch ( $vfsMode ) {
                case VFS_MODE_DB:
                    $response["result"] = VfsDbMode::DeleteFile( $fileId );
            }

            return ObjectHelper::ToJSON( $response );
        }

        /**
         * Create Folder
         *
         * @param string $parentFolderId
         * @param string $title
         * @param string $vfsMode
         * @return string
         */
        public static function CreateFolder( $folderId, $title, $vfsMode = VFS_MODE ) {
            $response = array();
            switch ( $vfsMode ) {
                case VFS_MODE_DB:
                    $response["result"] = VfsDbMode::CreateFolder( $folderId, $title );
            }

            return ObjectHelper::ToJSON( $response );
        }


        /**
         * Move Folder
         *
         * @param string $folderId
         * @param string $newFolderId
         * @param string $vfsMode
         * @return string
         */
        public static function MoveFolder( $folderId, $newFolderId, $vfsMode = VFS_MODE ) {
            $response = array();
            switch ( $vfsMode ) {
                case VFS_MODE_DB:
                    $response["result"] = VfsDbMode::MoveFolder( $folderId, $newFolderId );
            }

            return ObjectHelper::ToJSON( $response );
        }


        /**
         * Save Temp File
         *
         * @param array $requestFile
         * @return array
         */
        public static function SaveTempFile( $requestFile, $vfsMode = VFS_MODE ) {
            if ( empty( $requestFile ) ) {
                return null;
            }

            $extension = DirectoryInfo::GetExtension( $requestFile["name"] );
            $tempFile = sprintf( "%s%s.%s"
                , Site::GetRealPath( self::TempPath  )
                , md5( $requestFile["tmp_name"] . time() )
                , $extension
            );

            if ( move_uploaded_file( $requestFile["tmp_name"], $tempFile ) ) {
                return array(
                    "name"      => $requestFile["name"]
                    , "path"    => $tempFile
                    , "size"    => $requestFile["size"]
                    , "type"    => $requestFile["type"]
                    , "normal"  => basename( $requestFile["name"], "." . $extension )
                    , "relpath" => basename( $tempFile )
                );
            }
        }

        public static function CreateFile( $folderId, $name, $path, $type = null, $vfsMode = VFS_MODE ) {
            $response = array();
            switch ( $vfsMode ) {
                case VFS_MODE_DB:
                    // Check For Resizable
                    if ( VfsUtility::$Resizable ) {
                        if ( false === ImageHelper::IsImage( $path ) ) {
                            VfsUtility::$Resizable = false;
                        }
                    }

                    if ( VfsUtility::$Resizable ) {
                        $settings = VfsUtility::$ResizableSettings;

                        /// check for keep
                        if ( $settings["keep"] ) {
                            $originalPath = null;
                            $response["result"] = VfsDbMode::CreateFile( $folderId, $settings["prefix"]  . $name, $path, $type, null, $originalPath );
                        } else {
                            $originalPath = $path;
                        }

                        // resizing and adding to db
                        foreach ( $settings["modes"] as $mode ) {
                            $resizedPath = Site::GetRealPath( "temp://for_resize.jpg" );
                            if ( file_exists( $resizedPath ) ) unlink( $resizedPath  );

                            // resize
                            $opResult = ImageHelper::Resize(
                                $originalPath, $resizedPath
                                , $mode["width"], $mode["height"] , $mode["quality"] , $mode["scale"]
                            );

                            if ( $opResult ) { //add
                                $response["result"] = VfsDbMode::CreateFile( $folderId, $mode["prefix"] . $name, $resizedPath, "image/jpeg" );
                            }
                        }


                        // check for keep flag
                        if ( empty( $settings["keep"] ) && file_exists( $originalPath ) ) {
                            unlink( $originalPath );
                        }
                    } else {
                        $response["result"] = VfsDbMode::CreateFile( $folderId, $name, $path, $type  );
                    }
            }

            if( $response["result"] ) {
                $response['id'] = VfsFileFactory::GetCurrentId();
            }

            return ObjectHelper::ToJSON( $response );
        }

        /**
         * Browse Response to XML
         *
         * @param array $response
         * @return string
         */
        public static function BrowseResponseToXML( $response ) {
            if ( empty($response) ) {
                return null;
            }


            $doc = new DOMDocument( "1.0", "utf-8" );
            $root = $doc->createElement( "response" );

            // current folder
            self::FolderToXml( $response["currentFolder"], $doc, $root );

            // sub folders
            $subFolders = $doc->createElement( "folders" );
            foreach ( $response["folders"] as $folder ) {
                self::FolderToXml( $folder, $doc, $subFolders );
            }

            // files
            $files = $doc->createElement( "files" );
            foreach ( $response["files"] as $file ) {
                self::FileToXml( $file, $doc, $files );
            }

            // path
            $path = $doc->createElement( "path" );
            foreach ( $response["path"] as $folder ) {
                self::FolderToXml( $folder, $doc, $path );
            }

            // favorites
            $favorites = $doc->createElement( "favorites" );
            foreach ( $response["favorites"] as $folder ) {
                self::FolderToXml( $folder, $doc, $favorites );
            }

            // values
            self::ValueToXml( "page", $response["page"], $doc, $root );
            self::ValueToXml( "pageSize", $response["pageSize"], $doc, $root );
            self::ValueToXml( "pageCount", $response["pageCount"], $doc, $root );

            $doc->appendChild( $root );
            $root->appendChild( $path );
            $root->appendChild( $subFolders );
            $root->appendChild( $favorites );
            $root->appendChild( $files );

            return $doc->saveXML();
        }


        /**
         * Browse Response to XML
         *
         * @param array $response
         * @return string
         */
        public static function BrowseResponseToJSON( $response ) {
            if ( empty($response) ) {
                return null;
            }

            $result = array();
            $result["currentFolder"] = self::folderToArray( $response["currentFolder"] );

            // folders
            foreach ( $response["folders"] as $folder ) {
                $result["folders"][] = self::folderToArray( $folder);
            }

            // files
            foreach ( $response["files"] as $file ) {
                $result["files"][] = self::fileToArray( $file );
            }

            // path
            foreach ( $response["path"] as $folder ) {
                $result["path"][] = self::folderToArray( $folder);
            }

            // favorites
            foreach ( $response["favorites"] as $folder ) {
                $result["favorites"][] = self::folderToArray( $folder);
            }

            $result["page"]      = $response["page"];
            $result["pageSize"]  = $response["pageSize"];
            $result["pageCount"] = ( $response["pageCount"] == 0  ) ? 1 : ceil( $response["pageCount"] );

            return ObjectHelper::ToJSON( $result );
        }


        /**
         * Transform Folder To XmlNode
         *
         * @param VfsFolder $folder
         * @param DOMDocument $doc
         * @param DOMNode $parent
         */
        private function folderToXml( VfsFolder $folder, DOMDocument $doc, DOMNode $parent = null ) {
            if ( empty( $parent ) ) {
                $parent = $doc;
            }

            $node = $doc->createElement( "folder" );
            $node->setAttribute( "id",    $folder->folderId );
            $node->setAttribute( "name",  LocaleLoader::TryToUTF8( FormHelper::RenderToForm( $folder->title ) ) );

            $parent->appendChild( $node );
        }

        private static function getFileIcon( $path ) {
            $extension = DirectoryInfo::GetExtension( $path );
            switch ($extension) {
                case "jpg":
                case "jpeg":
                case "png":
                case "gif":
                    return "icon_image";
                case "swf":
                case "fla":
                    return "icon_flash";
                case "wmv":
                case "flv":
                case "avi":
                    return "icon_video";
                default:
                    return "icon_file";
                    break;
            }
        }



        /**
         * Transform File to XmlNode
         *
         * @param VfsFile $file
         * @param DOMDocument $doc
         * @param DOMNode $parent
         */
        private function fileToXml( VfsFile $file, DOMDocument $doc, DOMNode $parent = null ) {
            if ( empty( $parent ) ) {
                $parent = $doc;
            }

            $node = $doc->createElement( "file" );
            $node->setAttribute( "id",   $file->fileId  );
            $node->setAttribute( "name", LocaleLoader::TryToUTF8( FormHelper::RenderToForm( $file->title ) )  );
            $node->setAttribute( "path", $file->path  );
            $node->setAttribute( "size", $file->fileSize  );
            $node->setAttribute( "type", $file->mimeType  );
            $node->setAttribute( "className", self::getFileIcon( $file->path )  );

            $parent->appendChild( $node );
        }


        /**
         * Transform Folder To Array
         *
         * @param VfsFolder $folder
         * @return array
         */
        private static function folderToArray( VfsFolder $folder ) {
            return array(
                "id"     => $folder->folderId
                , "name" => LocaleLoader::TryToUTF8( FormHelper::RenderToForm( $folder->title ) )
            );
        }


        /**
         * Transform File To Array
         *
         * @param \VfsFile $file
         *
         * @return array
         */
        private static function fileToArray( VfsFile $file ) {
            return array(
                "id"            => $file->fileId
                , "name"        => LocaleLoader::TryToUTF8( FormHelper::RenderToForm( $file->title ) )
                , "path"        => Site::GetWebPath( self::RootDir ) . $file->path
                , "size"        => $file->fileSize
                , "type"        => mb_strimwidth( $file->mimeType, 0, 32, '...' )
                , "className"   => self::getFileIcon( $file->path )
            );
        }


        /**
         * Value To Xml
         *
         * @param string $key
         * @param string $value
         * @param DOMDocument $doc
         * @param DOMNode $parent
         */
        private function ValueToXml( $key, $value, DOMDocument $doc, DOMNode $parent = null ) {
            if ( empty( $parent ) ) {
                $parent = $doc;
            }

            $node = $doc->createElement( $key );
            $node->setAttribute( "value",   $value  );

            $parent->appendChild( $node );
        }


        /**
         * Get Collapsed Files By Folder Id
         *
         * @param string $folderId
         * @param array $groups
         * @param string $vfsMode
         */
        public static function GetCollapsedFilesByFolderId( $folderId, $groups, $vfsMode = VFS_MODE ) {
            if ( empty( $folderId ) ) {
                return null;
            }

            switch ( $vfsMode ) {
                case VFS_MODE_DB:
                    $search["folderId"] = $folderId;
                    $files = VfsFileFactory::Get( $search, array( BaseFactory::WithoutPages => true ) );
            }

            if ( empty( $files ) ) {
                return null;
            }

            $result = array();
            foreach ( $files as $file ) {
                foreach ( $groups as $group ) {
                    if ( strpos( $file->title, $group ) === 0 ) {
                        $name = substr( $file->title, strlen($group) );
                        $result[$name][$group] = $file;
                        break;
                    }
                }

            }

            return $result;
        }
    }
?>