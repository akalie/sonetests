<script type="text/javascript">
    function closeWindow() {
        window.parent.vfsDialog.dialog('close');
    }

    /**  AutoClose on Exit */
    if ($.browser.opera) {
        opera.setOverrideHistoryNavigationMode('compatible');
        history.navigationMode = 'compatible';
    }

    window.onbeforeunload = function (evt) {
        if (!$.browser.msie) {
            closeWindow();
        }
    }

    /**
     * Show Screen
     */ 
    function showScreen( mode ) {
        if ( mode == 'browse' ) {
            $("#mainMenu  li").removeClass( "active" );
            $("#mainMenu  li:eq(0)").addClass( "active");
            
            $("#addScreen" ).hide();
            $("#queueScreen" ).hide();
            $("#browseScreen" ).show();

    		$("#swfuContainer").addClass('hiddenDiv'); //--
    		if ( swfu ) {
    			swfu.setButtonDimensions(1,1); //--
    		}	
    		//
        } else if ( mode == 'queue' ) {
        	$("#swfuContainer").removeClass('hiddenDiv'); //--
            $("#mainMenu  li").removeClass( "active" );
            $("#mainMenu  li:eq(2)").addClass( "active");

            $("#addScreen" ).hide();
            $("#browseScreen" ).hide();
            $("#queueScreen" ).show( 'fast', function() {
        		swfu.setButtonDimensions(65,29); //--
            });
        } else if ( mode == 'add' ) {
            $("#mainMenu  li").removeClass( "active" );
            $("#mainMenu  li:eq(1)").addClass( "active");
            
            $("#addScreen" ).show();
            $("#queueScreen" ).hide();
            $("#browseScreen" ).hide();
            
    		$("#swfuContainer").addClass('hiddenDiv'); //--
    		swfu.setButtonDimensions(1,1); //--
        }
    }
    
    /**
     * Finish Work
     */
    function finish() {
        alert( this );
    }
    
    
    function selectFile( fileId ) {
<?php
    if ( !empty($isMCE)) {
?>
        
        //call this function only after page has loaded
        //otherwise tinyMCEPopup.close will close the
        //"Insert/Edit Image" or "Insert/Edit Link" window instead
        var URL = vfsHelper.currentFiles[fileId].path;
        //var win = tinyMCE.getWindowArg("window"); for 2.x versions
        var win = tinyMCEPopup.getWindowArg("window");
        
        
        // insert information now
        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;
        
        // for image browsers: update image dimensions
        //if (win.getImageData) win.getImageData();
        
        // close popup window
        tinyMCEPopup.close();
        self.close();
<?php
    } else
    
    if ( empty( $isFolder ) ) {
?>
        window.parent.vfsSelector.Feedback(vfsHelper.currentFiles[fileId] );
        closeWindow();
<?php
    }
?>
    }
    
    
    function selectFolder() {
        window.parent.vfsSelector.FolderFeedback(vfsHelper.currentFolder);
        closeWindow();
    }
    
    /**
     * Attach File
     */
    function attachFile( currentFile ) {
        var result = new Array();
        result[0] = currentFile;
        result[1] = currentPage;
        
        window.parent.feedback( result );
        closeWindow();
    }
    
    
    showScreen( 'browse' );
</script>
</body>
</html>