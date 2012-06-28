<?php
    class InitBrowseScreenAction {
        
        /**
         * Execute InitBrowseScreen
         */
        public function Execute() {
            $mode = Page::$RequestData[1];
            
            switch ($mode) {
            	case "/mce": 
        	        Response::setBoolean( "isMCE", true );
            	    
        	        // detect folder id from url
        	        $fName = Request::getString( "file" );
            	    if ( !empty( $fName ) && preg_match( "#.*/([0-9]+)_([0-9]+)*#", $fName, $regs ) ) {
                        Response::setInteger( "folderId", $regs[1]);
            	    }
            	    
            		break;
            	case "/fldr":
            	case "/sfldr":
            	    Response::setBoolean( "isNormal", true );
            	    
            	    if ( $mode == "/sfldr" ) {
            	        Response::setBoolean( "isFolder", true );
            	    }
            	    
            	    if ( !empty( Page::$RequestData[2] ) ) {
            	        Response::setInteger( "folderId", Page::$RequestData[2] );
            	    }
            	    
            		break;
                default:
            	    Response::setBoolean( "isNormal", true );
            	    
            	    if ( !empty( Page::$RequestData[2] ) ) {
            	        Response::setInteger( "fileId", Page::$RequestData[2] );
            	    }
            	    
            		break;
            }
        }
    }
?>