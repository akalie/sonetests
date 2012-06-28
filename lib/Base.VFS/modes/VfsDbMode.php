<?php
    Package::Load( "Base.VFS");

    class VfsDbMode {
        /**
         * Root Folder
         *
         */
        const RootFolder = 1;


        /**
         * Get Browse Screen
         *
         * @param unknown_type $parameter
         * @param unknown_type $inputType
         */
        public static function GetBrowseScreen( $parameter = null, $inputType = null, $page = 0, $pageSize = 10, $connectionName = null ) {
            switch ( $inputType ) {
                case null:
                case VfsUtility::InputTypeFolder:
                    return self::getBrowseScreenByFolderId( $parameter, $page, $pageSize, $connectionName );

                case VfsUtility::InputTypeFile:
                    $file = VfsFileFactory::GetById( $parameter, array(), array(), $connectionName );
                    if ( !empty( $file ) ) {
                        $parameter = $file->folderId;
                    } else {
                        $parameter = self::RootFolder ;
                    }

                    return self::getBrowseScreenByFolderId( $parameter, $page, $pageSize, $connectionName );
                default:
                    Logger::Error( 'Unknown inputtype', $inputType );
                    break;
            }
        }


        /**
         * Star Or Unstar Folder
         *
         * @param int $folderId
         * @param bool $addToFavorites
         * @param string $connectionName
         * @return bool
         */
        public static function StarFolder( $folderId, $addToFavorites = true, $connectionName = null ) {
            if ( empty( $folderId ) || $folderId == 1 ) {
                return false;
            }

            $folder = VfsFolderFactory::GetById( $folderId, array(), array(), $connectionName );
            if ( empty( $folder ) ){
                return false;
            }

            $folder->isFavorite = $addToFavorites;
            return VfsFolderFactory::Update( $folder, $connectionName );
        }


        /**
         * Move folder
         *
         * @param int $folderId
         * @param int $newFolderId
         * @param string $connectionName
         * @return bool
         */
        public static function MoveFolder( $folderId, $newFolderId, $connectionName = null ) {
            if ( empty( $folderId ) || empty( $newFolderId ) || $folderId == 1 ) {
                return false;
            }

            $folder    = VfsFolderFactory::GetById( $folderId, array(), array(), $connectionName );
            $newFolder = VfsFolderFactory::GetById( $newFolderId, array(), array(), $connectionName );
            if ( empty( $folder ) || empty( $newFolderId ) ) {
                return false;
            }

            return VfsFolderFactory::Move( $folder, $newFolder, $connectionName );
        }


        public static function DeleteFolder( $folderId, $connectionName = null ) {
            if ( empty( $folderId ) || $folderId == 1 ) {
                return false;
            }

            $folder = VfsFolderFactory::GetById( $folderId, array(), array(), $connectionName );
            if ( empty( $folder ) ){
                return false;
            }

            return VfsFolderFactory::Delete( $folder, $connectionName );
        }


        public static function DeleteFile( $fileId, $connectionName = null ) {
            if ( empty( $fileId )) {
                return false;
            }

            $file = VfsFileFactory::GetById( $fileId, array(), array(), $connectionName );
            if ( empty( $file ) ){
                return false;
            }

            return VfsFileFactory::Delete( $file, $connectionName );
        }


        public static function CreateFolder( $folderId, $title, $connectionName = null ) {
            if ( empty( $folderId ) || empty( $title ) ) {
                return false;
            }

            $folder = VfsFolderFactory::GetById( $folderId, array(), array(), $connectionName );
            if ( empty( $folder ) ){
                return false;
            }

            $newFolder = new VfsFolder();
            $newFolder->parentFolderId = $folderId;
            $newFolder->parentId = $folderId;
            $newFolder->parent = $folder;
            $newFolder->title = $title;
            $newFolder->statusId = 1;

            return VfsFolderFactory::Add( $newFolder, $connectionName );
        }


        /**
         * Get Current Dir
         *
         * @return unknown
         */
        private static function getCurrentDir() {
            $dateDir = date( "Ym" ) . "/";
        	$path    = Site::GetRealPath( "vfs://" . $dateDir );

        	if ( !( file_exists( $path ) || is_dir( $path ) ) ) {
                mkdir( $path );
            }

            return $path;
        }


        /**
         * Create File
         *
         * @param integer $folderId
         * @param string $name
         * @param string $path
         * @param string $connectionName
         * @param string $newFileName
         * @return bool
         */
        public static function CreateFile( $folderId, $name, $path, $type = null, $connectionName = null, &$newFileName = null ) {
            if ( empty( $folderId ) || empty( $name ) || empty( $path )  ) {
                return false;
            }

            $folder = VfsFolderFactory::GetById( $folderId, array(), array(), $connectionName );
            if ( empty( $folder ) ){
                return false;
            }

            $salt = (VfsUtility::$SaltedFileNames ) ? "_" . substr(md5(time() . "-" . microtime()), 0, 8) : "";

            /** Add File */
            $fileId      = VfsFileFactory::GetCurrentId( $connectionName ) + 1;
            $newFileName = sprintf( "%s%s_%s%s.%s"
                , self::getCurrentDir()
                , $folder->folderId
                , $fileId
                , $salt
                , DirectoryInfo::GetExtension( $path )
            );

            $tmpFile = new FileInfo( $path );
            if ( $tmpFile->MoveTo( $newFileName ) ) {
                $file = new VfsFile();
                $file->title      = $name;
                $file->statusId   = 1;
                $file->fileExists = file_exists( $newFileName );
                $file->folderId   = $folder->folderId;
                $file->fileSize   = $tmpFile->GetFileSize();
                $file->mimeType   = (empty($type)) ? $tmpFile->GetType() : $type;
                $file->path       = sprintf( "%s/%s_%s%s.%s", date("Ym"), $folder->folderId, $fileId, $salt, $tmpFile->GetExtension()  );
                

                return VfsFileFactory::Add( $file, $connectionName );
            }

            return false;
        }

        /**
         * Create File by Folder
         *
         * @param integer $folderId
         * @param string $name
         * @param string $path
         * @param string $connectionName
         * @param string $newFileName
         * @return bool
         */
        public static function CreateFileByFolder( $folder, $name, $path, $type = null, $connectionName = null, &$newFileName = null ) {
            if ( empty( $folder ) || empty( $name ) || empty( $path )  ) {
                return false;
            }

            $salt = (VfsUtility::$SaltedFileNames ) ? "_" . substr(md5(time() . "-" . microtime()), 0, 8) : "";

            /** Add File */
            $fileId      = VfsFileFactory::GetCurrentId( $connectionName ) + 1;
            $newFileName = sprintf( "%s%s_%s%s.%s"
                , self::getCurrentDir()
                , $folder->folderId
                , $fileId
                , $salt
                , DirectoryInfo::GetExtension( $path )
            );


            $tmpFile = new FileInfo( $path );
            if ( $tmpFile->MoveTo( $newFileName ) ) {
                $file = new VfsFile();
                $file->title      = $name;
                $file->statusId   = 1;
                $file->fileExists = file_exists( $newFileName );
                $file->folderId   = $folder->folderId;
                $file->fileSize   = $tmpFile->GetFileSize();
                $file->mimeType   = (empty($type)) ? $tmpFile->GetType() : $type;
                $file->path       = sprintf( "%s/%s_%s%s.%s", date("Ym"), $folder->folderId, $fileId, $salt, $tmpFile->GetExtension()  );

                $result = VfsFileFactory::Add( $file, $connectionName );
                if ( $result ) {
                    $file->fileId = $fileId; //VfsFileFactory::GetCurrentId();
                    return $file;
                } else return $result;

            }

            return false;
        }


        /**
         * Get Favorite Folders
         *
         * @return array
         */
        private static function getFavoriteFolders( $connectionName = null) {
            $folders = VfsFolderFactory::Get(  array( "isFavorite" => true ), null, null, $connectionName  );

            return $folders;
        }


        /**
         * Get Browse Screen By Folder Id
         *
         * @param integer $folderId
         */
        private static function getBrowseScreenByFolderId( $folderId = null, $page = 0, $pageSize = 10, $connectionName = null ) {
//            Logger::LogLevel( ELOG_DEBUG );
            $skeleton = VfsUtility::$SkeletonBrowseScreen;
            if ( empty( $folderId ) ) {
                $folderId = self::RootFolder ;
            }

            // get valid folder with childers
            $try = 0;
            do  {
                $folder = new BaseTreeObject();
                $folder->objectId = $folderId;
                $folder->folderId = $folderId;

                $folders = VfsFolderFactory::GetChildren( $folder, array(), array( OPTION_WITH_PARENT => true ), $connectionName );

                if ( empty( $folders[$folderId] ) ) {
                    $folderId = self::RootFolder;
                    $folder   = null;
                } else {
                    $folder = $folders[$folderId];
                    unset( $folders[$folderId] );
                }

                $try ++;

                if ( $try == 3 ) {
                    Logger::Error( 'Recursion! No Root Folder avaliable!' ); die();
                }
            } while( empty( $folder ) );

            // get sub folders and path
            $skeleton["currentFolder"]  = $folder;
            $skeleton["folders"]        = $folders;
            $skeleton["path"]           = VfsFolderFactory::GetBranch( $folder, $connectionName  );
            $skeleton["files"]          = VfsFileFactory::Get( array( "folderId" => $folder->folderId, "page" => $page, "pageSize" => $pageSize  ), array(), $connectionName  );
            $skeleton["favorites"]      = self::getFavoriteFolders( $connectionName );
            $skeleton["page"]           = $page;
            $skeleton["pageSize"]       = $pageSize;
            $skeleton["pageCount"]      = VfsFileFactory::Count( array( "folderId" => $folder->folderId, "pageSize" => $pageSize  ), array(), $connectionName );

            return $skeleton;
        }
    }
?>