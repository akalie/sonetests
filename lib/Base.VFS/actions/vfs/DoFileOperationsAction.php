<?php
    Package::Load( "Base.VFS");
    
    /**
     * Do Folder Operations
     *
     */
    class DoFileOperationsAction {
        
        /**
         * Execute 
         */
        public function Execute() {
            VfsUtility::$Resizable         = Request::getBoolean( "resizable" );
            VfsUtility::$ResizableSettings = Request::getArray( "settings" );
            
            $response = null;
            
            switch ( Page::$RequestData[1]) {
            	case "temp":
                    $file = Request::getFile( 'fileUpload' );
                    
                    if ( !empty( $file ) ) {
                        $tempFile = VfsUtility::SaveTempFile( $file );
                    } 
                    
                    if ( !empty( $tempFile ) ) {
                        $tempFile["name"]   = LocaleLoader::TryToUTF8( $tempFile["name" ] );
                        $tempFile["normal"] = LocaleLoader::TryToUTF8( $tempFile["normal" ] );
                        $response["file"]   = $tempFile;
                    } else {
                        $response["error"] = "vfsConstants.langEmptyFile";
                    }
                    
                    $response = ObjectHelper::ToJSON( $response );
                    break;
            	case "create":
            	    $name     = LocaleLoader::TryFromUTF8( Request::getString( "name" ) );
            	    $path     = LocaleLoader::TryFromUTF8( Request::getString( "path" ) );
            	    $type     = Request::getString( "type" );
            	    $response = VfsUtility::CreateFile( Page::$RequestData[2], $name, $path, $type );
            	    
            	    break;
            	case "delete":
            		$response = VfsUtility::DeleteFile( Convert::ToInt( Page::$RequestData[2] ) );
            		
            	    break;
            	    
            	case "queue":
                    $file = Request::getFile( 'Filedata' );
                    if ( !empty( $file ) ) {
                        $tempFile = VfsUtility::SaveTempFile( $file );
                        
                        if ( !empty( $tempFile ) ) {
                            $response = VfsUtility::CreateFile( 
                                Page::$RequestData[2]
                                , LocaleLoader::TryFromUTF8( $tempFile["normal"] )
                                , $tempFile["path"]
                                , $tempFile["type"] 
                            );
                        }
                    } 
            	    
            	    break;
            }
            
            header("Content-Type: text/html; charset=utf-8");
            echo $response;
        }
    }
?>