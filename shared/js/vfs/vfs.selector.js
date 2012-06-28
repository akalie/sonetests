function VfsWindow( url, title, width, height ) {
    var dialog = $('<div style="position:relative;"><iframe id="vfsFrame" src="'+url+'" style="width:100%; border: 0; margin: 0; height: 100%;"></iframe></div>').dialog({'height':height, 'width': width, 'title':title, modal: true});
    window.vfsDialog = dialog;
    $('#vfsFrame').css({'width':'100%'});
    return dialog;
}

function VfsSelector( path ) {
    this.path              = path;
    this.currentElementId  = null;
    this.lastFile          = null;
    this.lastFolder        = null;
    this.mode              = "single";
    
    this.ImagePreviewType = "image";
    this.FlashPreviewType = "flash";
    this.AutoPreviewType  = "auto";
    this.DefaultFolderId  = "";
    
    this.Open = function( folderId, currentElementId ) {
        this.currentElementId = currentElementId;
        this.mode = "single";
        if ( folderId == null ) {
            folderId = this.DefaultFolderId;
        }
        
        var explorerWindow = VfsWindow( this.path + "fldr" + folderId, this.title, this.width, this.height );
    }    
    
    this.OpenFile = function( fileId, currentElementId ) {
        this.currentElementId = currentElementId;
        this.mode = "single";
        
        var explorerWindow = VfsWindow( this.path + fileId, this.title, this.width, this.height );
    }
    
    this.OpenFileRow = function( fileId, currentElementId ) {
        this.currentElementId = currentElementId;
        this.mode = "multi";
        
        var explorerWindow = VfsWindow( this.path + fileId, this.title, this.width, this.height );
    }    
    
    
    this.OpenRow = function( folderId, currentElementId ) {
        this.currentElementId = currentElementId;
        this.mode = "multi";
        if ( folderId == null ) {
            folderId = this.DefaultFolderId;
        }
        
        var explorerWindow = VfsWindow( this.path + "fldr" + folderId, this.title, this.width, this.height );
    }    

    
    this.OpenFolder = function( folderId, currentElementId ) {
        this.currentElementId = currentElementId;
        if ( folderId == null ) {
            folderId = this.DefaultFolderId;
        }
        
        var explorerWindow = VfsWindow( this.path + "sfldr" + folderId, this.title, this.width, this.height );
    }

    this.Feedback = function( result ) {
        this.lastFile = result;
        this.setFileObject(this.currentElementId);
        this.drawFile( $( "#"  + this.currentElementId ) );
    }

    this.FolderFeedback = function( result ) {
        this.lastFolder = result;
        
        this.setFolderObject(this.currentElementId);
        this.drawFolder( $( "#"  + this.currentElementId ) );
    }
    
    
    /**
     * Init Draw
     */
    this.Init =  function() {        
        this.controlName = "vfsSelector";
        $(".vfsFile").each( function() { vfsSelector.drawFile( $( this )) });
        $(".vfsMultiFile").each( function() { vfsSelector.drawFile( $( this ), "multi" ) });
        $(".vfsFolder").each( function() { vfsSelector.drawFolder( $( this )  ) });
    }
    
    
    this.DeleteFile = function( fileId ) {
        $("#" + fileId ).val( '-1' );
        this.drawFile( $( "#"  + fileId ) );
    } 

    
    this.DeleteFolder = function( folderId ) {
        $("#" + folderId ).val( '-1' );
        this.drawFolder( $( "#"  + folderId ) );
    }
    
    
    /**
     * Set File Object 
     */
    this.setFileObject = function( vfsFileId ) {
        $( "#" + vfsFileId ).attr( "vfs:name", this.lastFile.name ).val( this.lastFile.id ).attr( "vfs:src", this.lastFile.path  );
    }

    
    /**
     * Set Folder Object 
     */
    this.setFolderObject = function( vfsFolderId ) {
        $( "#" + vfsFolderId ).attr( "vfs:name", this.lastFolder.name ).val( this.lastFolder.id );
    }
    
    
    /**
     * Draw File
     */
    this.drawFile = function( node, mode  ) {
        if ( !mode ) {
            mode = this.mode;
        }
        
        var current = node;
        var areaId  = "vfsArea_" +  current.attr( 'id' );
        $( "#" + areaId).remove();
        
        var xhtml = '<div id="'+ areaId +'" class="fileinput">';
        
        if ( current.val() == '' || current.val() == '-1' ) {
            if ( mode == "single" ) {
                xhtml = xhtml + '<a href="javascript:vfsSelector.Open( null, \'' + current.attr('id') + '\' );">' + vfsConstants.langOpen + '</a>';   
            }
        } else {
            if ( current.attr( 'vfs:previewType' ) == vfsSelector.ImagePreviewType ) {
                xhtml = xhtml + '<a href="' + current.attr('vfs:src') + '" class="fancy"><img class="image" width="50" height="50" src="' + current.attr('vfs:src') + '"/></a>';
                xhtml = xhtml + '<div class="info">';
            } else {
                xhtml = xhtml + '<div class="info-short">';
            }

            if ( mode == "single") {
                xhtml = xhtml + '<p><a class="filename" href="' + current.attr('vfs:src') + '" target="_blank">' + current.attr( 'vfs:name' ) + '</a>&nbsp;&nbsp;';
                xhtml = xhtml + '<a class="delete" href="javascript:vfsSelector.DeleteFile( \'' + current.attr('id') + '\' );" title="' + vfsConstants.langDelete + '">' + vfsConstants.langDelete + '</a>';
                xhtml = xhtml + '</p>';
                xhtml = xhtml + '<a class="edit" href="javascript:vfsSelector.OpenFile( ' + current.val() + ',\'' + current.attr('id') + '\' );">' + vfsConstants.langEdit + '</a> ';
            } else if ( mode == "multi" ) {
                xhtml = xhtml + '<p>' + current.attr( 'vfs:name' ) + '</p>';
                xhtml = xhtml + '<a href="javascript:vfsSelector.OpenFileRow( ' + current.val() + ',\'' + current.attr('id') + '\' );">' + vfsConstants.langEdit + '</a> ';
                xhtml = xhtml + '<a href="javascript:removeVfsFileRow( \'' + current.attr('id') + '\' );">' + vfsConstants.langDelete + '</a>';       
            }
            xhtml = xhtml + '</div>';
        }
        
        xhtml = xhtml +  '</div>';
        
        current.after( xhtml );

        $('a.fancy').fancybox();
    } 

    
    /**
     * Draw Folder
     */
    this.drawFolder = function( node ) {
        var current = node;
        var areaId  = "vfsArea_" +  current.attr( 'id' );
        $( "#" + areaId).remove();
        
        var xhtml = '<div id="'+ areaId +'">';
        
        if ( current.val() == '' || current.val() == '-1' ) {
            xhtml = xhtml + '<a href="javascript:vfsSelector.OpenFolder( null, \'' + current.attr('id') + '\' );">' + vfsConstants.langOpenFolder + '</a>';
        } else {
            xhtml = xhtml + current.attr( 'vfs:name' ) + "<br />";
            xhtml = xhtml + '<a href="javascript:vfsSelector.OpenFolder( ' + current.val() + ',\'' + current.attr('id') + '\' );">' + vfsConstants.langOpenFolder + '</a> ';
            xhtml = xhtml + '<a href="javascript:vfsSelector.DeleteFolder( \'' + current.attr('id') + '\' );">' + vfsConstants.langDelete + '</a>';
        }
        
        xhtml = xhtml +  '</div>';
        
        current.after( xhtml );
    }
}

VfsSelector.prototype = new BaseSelector;

var vfsConstants = new VFSConstants( '' );