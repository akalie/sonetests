    /**
     * VFS Query
     */
    function VFSNavigator( vfsRoot, path, mode, param ) {
        this.vfsRoot    = vfsRoot;          // vfs root folder
        this.path       = path;             // full url path
        
        this.mode       = mode || "browse"; // mode : folder, file, path, browse
        this.param      = param || "";      // parameter for mode (typically folder id)
        this.page       = 0;
        
        
        this.currentFile = null; // current uploaded file
        
        
        this.loaderId   = "#loader";
        
        /**
         * Browse Screen
         */
        this.BrowseScreen = function() {
            this.ShowLoader();
            
            $.getJSON( this.getQueryUrl(), { "page" : this.page }, function(data) {
                vfs.page  = data.page;
                vfs.param = data.currentFolder.id;
                vfsHelper.SetQueueScriptUrl( vfs.getOperationUrl( "file/queue", vfs.param ) );
                vfsHelper.RenderFolders( data.folders, data.path, data.currentFolder );
                vfsHelper.RenderFavorites( data.favorites);
                vfsHelper.RenderPath( data.path );
                vfsHelper.RenderFiles( data.files, data.page , data.pageSize, data.pageCount );
                vfsHelper.RenderFilePreview();
                
                vfs.HideLoader();
            });
        }
        
        this.DoOpeation = function( operation, id, params, callback ) {
            this.ShowLoader();
            
            $.getJSON( this.getOperationUrl(operation, id),  params, callback );
        }
        
        
        /**
         * Navigate to folder id
         */        
        this.Navigate = function( folderId ) {
            this.param = folderId;
            this.mode  = 'folder';
            this.page  = 0;
            
            this.BrowseScreen();
        }
        
        
        this.NextPage = function() {
            this.page ++ ;
            this.mode  = 'folder';
            
            this.BrowseScreen();
        }
        
        
        this.PrevPage = function() {
            this.page  --;
            this.mode = 'folder';
            
            this.BrowseScreen();
        }
        
        this.GoToPage = function(page) {
            this.page  = page;
            this.mode  = 'folder';
            
            this.BrowseScreen();
        }        
        
        /** Loader */
        this.HideLoader = function() {
            $( this.loaderId ).hide();
        }        
        
        this.ShowLoader = function() {
            $( this.loaderId ).show();
        }
        
        /** Urls */
        this.getQueryUrl = function () {
            return this.path + this.mode + "/" + this.param;
        }
        
        this.getOperationUrl = function (operation, id ) {
            return this.path + "manage/" + operation + "/" + id;
        }        
        
        
        /** Delete File */
        this.DeleteFile = function( fileId ) {
            if ( confirm( vfsConstants.langDeleteFile ) ) {
                this.DoOpeation( "file/delete", fileId, {}, function(data) {
                    vfs.BrowseScreen();
                } );
            }
        }   

        
        /** Delete Folder */
        this.DeleteFolder = function( folderId ) {
            if ( confirm( vfsConstants.langDeleteFldr ) ) {
                this.DoOpeation( "folder/delete", folderId, {}, function(data) {
                    vfs.BrowseScreen();
                } );
            }
        }        
        
        /** Move Folder */
        this.MoveFolder = function( folderId, toFolderId ) {
            this.DoOpeation( "folder/move", folderId, { 'new' : toFolderId }, function(data) {
                vfs.BrowseScreen();
            } );
        }

        
        /** Start Folder */
        this.StarFolder = function( folderId ) {
            this.DoOpeation( "folder/star", folderId, {}, function(data) {
                vfs.BrowseScreen();
            } );
        }
        
        
        this.UnstarFolder = function( folderId ) {
            this.DoOpeation( "folder/unstar", folderId, {}, function(data) {
                if ( data.result == true ) {
                    $("#fav_" + folderId ).hide();
                }
                
                vfs.HideLoader();
            } );
        }
        
        
        this.CreateFolder = function( name ) {
            if ( $.trim( name ) == '' ) {
                alert( vfsConstants.langEmptyFolderName );
                return false;
            }
            
            this.DoOpeation( "folder/create", this.param, { 'title': name }, function(data) {
                if ( data.result == true ) {
                    vfs.BrowseScreen();    
                }
                
                $( "#newFolder" ).val('');
            } );
        }
        
        
        this.UploadTempFile = function( fileControlId ) {
            vfsHelper.BlockUploadScreen();
            
            $(this.loaderId).ajaxStart(function(){
                vfs.ShowLoader();
            })
            .ajaxComplete(function(){
                vfs.HideLoader();
            });
            
            $.ajaxFileUpload( {
                url:              vfs.getOperationUrl( "file/temp", vfs.param )
                , secureuri:      'false'
                , fileElementId:  fileControlId
                , dataType:       'json'
                , success: function (data, status) {
                        if( typeof(data.error) != 'undefined'
                                && data.error != '' ) {
                            alert( data.error );
                            vfs.currentFile = null;
                            
                            vfsHelper.UnBlockUploadScreen();
                            vfsHelper.RefreshUploadScreen();
                        } else {
                            vfs.currentFile = data.file;
                            
                            vfsHelper.UnBlockUploadScreen();
                            vfsHelper.RenderUploadedFile();
                        }
                    }
                , error: function (data, status, e) {
                    alert(e);
                    vfs.currentFile = null;
                    
                    vfsHelper.UnBlockUploadScreen();
                    vfsHelper.RefreshUploadScreen();
                }
            });
                
            return false;
        }
        
        
        this.CreateFile = function( name, callback ) {
            this.DoOpeation( "file/create",
                this.param
                , {
                    'name' : name
                    , 'path': vfs.currentFile.path
                    , 'type': vfs.currentFile.type
                }, function(data) {
                    callback(data);
                    vfs.HideLoader();
            } );            
        }
    }

    
    /**
     * VFS Helper
     */
    function VFSHelper() {
        this.foldersList     = "foldersList";
        this.favoritesList   = "favoritesList";
        this.favoritesTable  = "favoritesTable";
        this.fileList        = "fileList";
        this.pathList        = "subMenu";
        this.txtFileName     = "fileName";
        this.btnFileUpload   = "btnCreateFile";
        this.txtFileUpload   = "fileUpload";
        this.pnlAddScreen    = "addScreen";
        this.toolboxId       = "toolbox";
        this.currentFiles    = [];
        this.currentFolder   = null;
        this.folderIdToMove  = null;
        this.lastQueueScriptUrl = null;
        
        /**
         * Init
         */        
        this.Init = function() {
            $( "#" + this.favoritesTable ).hide();
            
            // Create New Fodlder By Enter
            $( "#newFolder" ).keypress( function(e) {
                var keycode;
                
                if (window.event) keycode = window.event.keyCode;
                else if (e) keycode = e.which;
                else return true;
                    
                if (keycode == 13) {
                    vfsHelper.CreateFolder();
                    return false;
                } else return true;
            });
            
            // File Upload By Enter
            $( "#" + this.txtFileName ).keypress( function(e) {
                var keycode;
                
                if (window.event) keycode = window.event.keyCode;
                else if (e) keycode = e.which;
                else return true;
                    
                if (keycode == 13) {
                    var disabled = $( "#" + vfsHelper.btnFileUpload ).attr("disabled");
                    if ( disabled == undefined ) {
                        vfsHelper.CreateFile();
                        return false;
                    }
                } else return true;
            });            
        }
        
        
        this.CreateFolder = function() {
            return vfs.CreateFolder( $("#newFolder").val() );
        }
        
        this.RenderPath = function( path ) {
            var xhtml = "";
            
            if ( path.length > 1 ) {
                xhtml +="<a class=\"icon_up\" href=\"javascript:vfs.Navigate('" + path[path.length-2].id + "');\">"
                + "<img src=\"" + vfsConstants.imgUp + "\" alt=\"" + vfsConstants.langUp + "\" title=\"" + vfsConstants.langUp + "\" /></a>";
            }
            
            xhtml += "<span id='" + this.toolboxId + "'>";
            if ( this.folderIdToMove != null && this.folderIdToMove != this.currentFolder.id ) {
                xhtml += '<a href="javascript:vfsHelper.Paste();">' + vfsConstants.langPaste + '</a>&nbsp;';
            }
            xhtml += "</span>";
            
            xhtml += "<img class=\"img_icon\" src=\"" + vfsConstants.imgFolder + "\" alt=\"\" />";
           
            $(path).each( function() {
                xhtml += "/<a href=\"javascript:vfs.Navigate( '" + this.id + "');\">" + this.name + "</a>";
            });
            
            $( "#" + this.pathList ).html( xhtml );
        }
        
        
        /**
         * Render Folders
         */        
        this.RenderFolders = function( folders, path, currentFolder ) {
            this.currentFolder = currentFolder;
            var xhtml = "";
            
            if ( path.length > 1 ) {
                folderId = path[path.length-2].id;
                xhtml += this.getFolderTemplateString( { 'id': folderId, 'name': '..' }, false );
            }
            // set up folders
            if ( folders != null && folders.length > 0 ) {
                $(folders).each( function() {
                    xhtml += vfsHelper.getFolderTemplateString( this, true );
                });
            }

            if ( xhtml == "" ) {
                $( "#" + this.foldersList ).empty();
            } else {
                $( "#" + this.foldersList ).html( xhtml );
            }
            
        }
        
        /**
         * Render Favorite Folders
         */        
        this.RenderFavorites = function( folders ) {
            var xhtml = "";
            
            if ( folders != null ) {
                $(folders).each( function() {
                    xhtml += vfsHelper.getFavoritesTemplateString( this );
                });
                
                $( "#" + this.favoritesTable ).show();
                $( "#" + this.favoritesList ).html( xhtml );
            } else {
                $( "#" + this.favoritesTable ).hide();
            }
        }
        
        /**
         * Render Files
         */        
        this.RenderFiles = function( files, page, pageSize, pageCount ) {
            var xhtml = "";
            
            if ( files != null ) {
                $(files).each( function() {
                    xhtml += vfsHelper.getFileTemplateString( this );
                    vfsHelper.currentFiles[this.id] = this;
                });
            }
                
            $( "#" + this.fileList ).html( xhtml );
            $( "#" + this.fileList + " tr:odd").addClass( "odd" );
            
            this.RenderNavigation( page, pageSize, pageCount );
        }
        
        
        /**
         * Render Navigation
         */
        this.RenderNavigation = function( page, pageSize, pageCount ) {
            var xhtml = "";
            
            // back
            if ( page == 0 ) {
                xhtml += "<img class=\"img_icon\" src=\"" + vfsConstants.imgBackD + "\" alt=\"" + vfsConstants.langBack + "\" title=\"" + vfsConstants.langBack + "\"  />";
            } else if ( page > 0 ) {
                xhtml += "<a href=\"javascript:vfs.PrevPage();\"><img class=\"img_icon\" src=\"" + vfsConstants.imgBack + "\" alt=\"" + vfsConstants.langBack + "\" title=\"" + vfsConstants.langBack + "\"  /></a>";
            }
            
            // page selector
            xhtml += " <input type=\"text\" value=\"" + (page+1) + "\" class=\"pageSelecter\" name=\"pageSelecter\" size=\"1\" maxlength=\"4\"/> " 
                    + vfsConstants.langOf + " " + pageCount + " ";

            // forward
            if ( page + 1 == pageCount || pageCount == 1 ) {
                xhtml += "<img class=\"img_icon\" src=\"" + vfsConstants.imgForwardD + "\" alt=\"" + vfsConstants.langForward + "\" title=\"" + vfsConstants.langForward + "\"  />";
            } else {
                xhtml += "<a href=\"javascript:vfs.NextPage();\"><img class=\"img_icon\" src=\"" + vfsConstants.imgForward + "\" alt=\"" + vfsConstants.langForward + "\" title=\"" + vfsConstants.langForward + "\"  /></a>";
            }
             
            $('.navigationBar').html( xhtml );       
            $( ".pageSelecter" ).keypress( function(e) {
                    var keycode;
                    
                    if (window.event) keycode = window.event.keyCode;
                    else if (e) keycode = e.which;
                    else return true;
                    
                    if (keycode == 13) {
                        newPage = $(this).val() - 1;
                        if ( newPage + 1 > pageCount ) newPage = 0;
                        vfs.GoToPage( newPage );
                        
                        return false;
                    } else return true;
                }
            );
        }
        
        
        this.RenderFilePreview = function() {
            if ($("[@name=viewMode]:checked").val() == "preview") {
                show = true;
            } else {
                show = false;
            }
            
            if ( show ) {
                $( ".fileRow" ).each(function(){
                    var row     = this;
                    var rowId   = $(this).attr("id");
                    var fileSrc = $(".fileSrc", this).attr("href");
                    
                    // preview add
                    if ( $("#preview_" + rowId).length == 0 ) {
                        $( ".fileLink", this ).after(  
                            '<div class="preview imgLayerHidden" id="preview_' + rowId + '"><img src="' + fileSrc + '"/></div>'
                        );
                    }

                                          
                    // mouse events
                    $(".fileLink", this ).mouseout( function() {
                        $( "#preview_" + rowId ).addClass( "imgLayerHidden" ).removeClass( "imgLayerNormal" );
                    })
                    .mouseover( function() {
                        $( "#preview_" + rowId ).addClass( "imgLayerNormal" ).removeClass( "imgLayerHidden" );
                    });
                });
            } else {
                $(".fileLink").mouseout( function() {} ).mouseover( function() {} );
                $(".preview").remove();
            }
        }

        
        this.getFileTemplateString = function( file ) {
            var xhtml = "<tr class=\"fileRow\" id=\"file_" + file.id + "\">"
                + "<td class=\"first\"><a href=\"javascript:selectFile(" + file.id + ");\" class=\"fileLink " + file.className + "\">" + file.name + "</a></td>"
                + "<td class=\"second\">" + file.type + "</td>"
                + "<td class=\"third\">" + Math.round( file.size/1024) + " " + vfsConstants.langKB + "</td>"
                + "<td class=\"fourth\"><a class=\"fileSrc\" href=\"" + file.path + "\" target=\"_blank\"><img class=\"img_icon\" src=\"" + vfsConstants.imgPath + "\" alt=\"" + vfsConstants.langPath + "\" title=\"" + vfsConstants.langPath + "\"  /></a>"
                + "<a href=\"javascript:vfs.DeleteFile( '" + file.id + "' );\"><img class=\"img_icon\" src=\"" + vfsConstants.imgDelete + "\" alt=\"" + vfsConstants.langDelete + "\" title=\"" + vfsConstants.langDelete + "\"  /></a></td>"
                + "</tr>";
            return xhtml;
        }
        
        
        /**
         * Get Folder Template
         */        
        this.getFolderTemplateString = function( folder, showOperations ) {
            var xhtml = "<tr>"
                + "     <td class=\"first\"><a class=\"icon_folder\" href=\"javascript:vfs.Navigate('" + folder.id + "');\" >" + folder.name + "</a></td>"
                + "     <td class=\"cat_actions\">";
            if ( showOperations ) {
                // star
                xhtml += "<a href=\"javascript:vfs.StarFolder( '" + folder.id + "');\"><img class=\"img_icon\" src=\"" + vfsConstants.imgStar + "\" alt=\"" + vfsConstants.langStar + "\" title=\"" + vfsConstants.langStar + "\"  /></a>"
                
                // operations
                xhtml += "<!--<a href=\"\"><img class=\"img_icon\" src=\"" + vfsConstants.imgEdit + "\" alt=\"" + vfsConstants.langEdit + "\" title=\"" + vfsConstants.langEdit + "\"  /></a>-->"
                  + "<a href=\"javascript:vfs.DeleteFolder( '" +folder.id+ "' );\"><img class=\"img_icon\" src=\"" + vfsConstants.imgDelete + "\" alt=\"" + vfsConstants.langDelete + "\" title=\"" + vfsConstants.langDelete + "\"  /></a>"
                  + "<a href=\"javascript:vfsHelper.CutFolder( '" +folder.id+ "' );\"><img class=\"img_icon\" src=\"" + vfsConstants.imgEdit + "\" alt=\"" + '' + "\" /></a>";
            }                
            
            xhtml += "</td></tr>";
            
            return xhtml;
        }
        
        /**
         * Get Favorite Folder Template
         */        
        this.getFavoritesTemplateString = function( folder ) {
            var xhtml = "<tr id=\"fav_" + folder.id + "\">"
                + "     <td class=\"first\"><a class=\"icon_folder\" href=\"javascript:vfs.Navigate('" + folder.id + "');\" >" + folder.name + "</a></td>"
                + "     <td class=\"cat_actions\">"
                + "<a href=\"javascript:vfs.UnstarFolder( '" + folder.id + "' );\"><img class=\"img_icon\" src=\"" + vfsConstants.imgUnStar + "\" alt=\"" + vfsConstants.langDelete + "\" title=\"" + vfsConstants.langDelete + "\"  /></a>"
                + "</td></tr>";
                
            return xhtml;
        }
        
        this.CreateFile = function() {
            var fileName = $( "#" + this.txtFileName ).val();
            vfs.CreateFile( fileName, function( data ) {
                showScreen('browse');
                vfs.BrowseScreen();
                vfsHelper.RefreshUploadScreen();
            } );
        }
        
        this.CutFolder = function( folderId ) {
            this.folderIdToMove = folderId;
            $("#" + this.toolBoxId ).html('');
        }
        
        this.Paste = function()  {
            $("#" + this.toolBoxId ).html('');
            vfs.MoveFolder( this.folderIdToMove, this.currentFolder.id );
            this.folderIdToMove = null;
        }
        
        
        this.RefreshUploadScreen = function() {
            $( "#" + this.txtFileName ).val( '' );
            $( "#" + this.txtFileUpload ).val( '' );
            $( "#" + this.btnFileUpload ).attr( "disabled", "disabled" );
        }
        
        this.RenderUploadedFile = function() {
            var txtFileName = $( "#" + this.txtFileName );
            if ( $.trim( txtFileName.val() ) == '' ) {
               txtFileName.val( vfs.currentFile.normal );
            }
            txtFileName.focus();
            
            $( "#" + this.btnFileUpload ).removeAttr( "disabled" );
        }
        
        this.BlockUploadScreen = function() {
            var message =  '<table border="0" cellspacing=5><tr valign=middle><td><img src="'  + vfsConstants.imgLoader + '" /></td><td><h3>' + vfsConstants.langWait + '</h3></td></tr></table>';
            $( "#" + this.pnlAddScreen ).block( message );
        }   

        this.UnBlockUploadScreen = function() {
            $( "#" + this.pnlAddScreen ).unblock();
        }
        
        this.SetQueueScriptUrl = function( url ) {
        	this.lastQueueScriptUrl = url;
        	//swfu.addSetting("upload_url", url );
        	//swfu.setUploadURL(url);
        	//swfu.loadFlash();
        }
        
        this.UpdateQueueScriptUrl = function() {
//        	/**
//        	 * Empty upload_url (perhaps first start)
//        	 */
//        	if ( !swfu.settings['upload_url'] ) {
//        		swfu.setUploadURL( this.lastQueueScriptUrl );
//        	} else {
//        		/**
//        		 * Update upload_url only when it changes
//        		 */
//        		if ( swfu.settings['upload_url'] != this.lastQueueScriptUrl ) {
//        			alert( 'event!!!' );
//        			swfu.setUploadURL( this.lastQueueScriptUrl );
//        		}	
//        	}
        	
        	swfu.setUploadURL( this.lastQueueScriptUrl );
        }
    }
    
    var vfsHelper = new VFSHelper();