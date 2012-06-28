<?php
    Package::Load( "Base.VFS");
    
    class GetBrowseScreenAction {
        
        /**
         * Execute GetBrowseScreen
         */
        public function Execute() {
            $page     = Request::getInteger( "page" );
            $pageSize = Request::getInteger( "pageSize" );
            $pageSize = 20;
            $response = "";
            
            switch ( Page::$RequestData[1]) {
            	case "folder":
            	case "file":      
            	case "path":      	    
            		$response = VfsUtility::GetBrowseScreen(  Page::$RequestData[2], Page::$RequestData[1], $page, $pageSize );
            		break;
            	case "browse":
            		$response = VfsUtility::GetBrowseScreen( null, null, $page, $pageSize );
            		break;
            }
            
            header("Content-Type: text/html; charset=utf-8");
            echo $response;
        }
    }
?>