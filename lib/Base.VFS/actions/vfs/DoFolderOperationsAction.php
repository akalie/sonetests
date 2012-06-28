<?php
    Package::Load( "Base.VFS");
    
    /**
     * Do Folder Operations
     *
     */
    class DoFolderOperationsAction {
        
        /**
         * Execute 
         */
        public function Execute() {
            switch ( Page::$RequestData[1]) {
            	case "star":
            	    $response = VfsUtility::Star(Convert::ToInt( Page::$RequestData[2] ) );
            	    break;
            	case "unstar":      
            		$response = VfsUtility::UnStar( Convert::ToInt( Page::$RequestData[2] ) );
            		break;
            	case "delete":
            		$response = VfsUtility::DeleteFolder( Convert::ToInt( Page::$RequestData[2] ) );
            		break;
            	case "create":
                    $response = VfsUtility::CreateFolder( Convert::ToInt( Page::$RequestData[2] ), LocaleLoader::TryFromUTF8( Request::getString( "title" ) ) );
            	   break;
            	case "move":
            	    $newFolder = Request::getInteger( "new" );
            	    $response  = VfsUtility::MoveFolder( Convert::ToInt( Page::$RequestData[2] ), $newFolder );
            }
            
            header("Content-Type: text/html; charset=utf-8");
            echo $response;
        }
    }
?>