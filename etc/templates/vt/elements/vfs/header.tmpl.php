<?php
    if ( class_exists( "LocaleLoader" ) ) {
        $__currentLang = LocaleLoader::$CurrentLanguage;    
    } else {
        $__currentLang = "ru";
    }
    
    $__pageTitle = "";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru" xml:lang="ru">
<head>
	<title>{$__pageTitle}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?= LocaleLoader::$HtmlEncoding ?>" />
	<link rel="stylesheet" type="text/css" href="{web:css://vfs/vfs.css}" />
	<link rel="stylesheet" type="text/css" href="{web:js://ext/swfupload/swfupload.css}" />
	<script src="{web:js://ext/jquery/jquery.js}"></script>
	<script src="{web:js://ext/jquery.plugins/jquery.blockui.js}"></script>
	<script src="{web:js://ext/jquery.plugins/jquery.ajaxfileupload.js}?1"></script>
	<script src="{web:js://vfs/vfsConstants.$__currentLang.js}"></script>
	<script src="{web:js://vfs/vfs.js}"></script>
	
    
    <script type="text/javascript" src="{web:js://ext/swfupload/swfupload.js}"></script>
    <script type="text/javascript" src="{web:js://ext/swfupload/swfupload.queue.js}"></script>
    <script type="text/javascript" src="{web:js://ext/swfupload/fileprogress.js}"></script>
    <script type="text/javascript" src="{web:js://ext/swfupload/handlers.js}"></script>
    
<? if ( !empty( $isMCE ) ) { ?>
    <script language="javascript" type="text/javascript" src="{web:js://ext/tiny_mce/tiny_mce_popup.js}"></script>
    <script type="text/javascript">
        InitFunction = function () {
            // patch TinyMCEPopup.close
            tinyMCEPopup.close_original = tinyMCEPopup.close;
            tinyMCEPopup.close = function () {
                tinyMCE.selectedInstance.fileBrowserAlreadyOpen = false;
                tinyMCEPopup.close_original();
            };
            
            var allLinks = document.getElementsByTagName("link");
            allLinks[allLinks.length-1].parentNode.removeChild(allLinks[allLinks.length-1]);
        }
    </script>
<? } ?>
</head>
<?
    if ( empty( $__vfsPath ) ) {
        $__vfsPath = 'vt://vfs/';
    }
?>
<script type="text/javascript">
    var swfu;
    var vfsConstants = new VFSConstants( '{web:shared://images/vfs/}' );
<?php if ( !empty( $folderId ) ) { ?>
    var vfs = new VFSNavigator( '{web:shared://vfs/}', '{web:$__vfsPath}', "folder", '{$folderId}' );
<?php } elseif ( !empty( $fileId )) { ?>
    var vfs = new VFSNavigator( '{web:shared://vfs/}', '{web:$__vfsPath}', "file", '{$fileId}' );
<?php } else { ?>
    var vfs = new VFSNavigator( '{web:shared://vfs/}', '{web:$__vfsPath}' );
<?php } ?>
	var swfu;
	var settings = {
        flash_url : "{web:js://ext/swfupload/swfupload.swf}",
        //upload_url: "", // Relative to the SWF file
        post_params: {"PHPSESSID" : "<?= Session::getId() ?>"},
        file_size_limit : "100 MB",
        file_types : "*.*",
        file_types_description : "All Files",
        file_upload_limit : 100,
        file_queue_limit : 0,
        custom_settings : {
            progressTarget : "fsUploadProgress",
            cancelButtonId : "btnCancel"
        },
        debug: false,
        prevent_swf_caching : false,

        // Button settings
        button_image_url: "{web:js://ext/swfupload/images/TestImageNoText_65x29.png}", // Relative to the Flash file
        button_width: "1",
        button_height: "1",
        button_placeholder_id: "spanButtonPlaceHolder",
        button_text: '<span class="theFont">Upload</span>',
        button_text_style: ".theFont { font-size: 16; }",
        button_text_left_padding: 7,
        button_text_top_padding: 3,
        
        // The event handler functions are defined in handlers.js
        file_queued_handler : fileQueued,
        file_queue_error_handler : fileQueueError,
        file_dialog_complete_handler : fileDialogComplete,
        upload_start_handler : uploadStart,
        upload_progress_handler : uploadProgress,
        upload_error_handler : uploadError,
        upload_success_handler : uploadSuccess,
        upload_complete_handler : uploadComplete,
        queue_complete_handler : queueComplete  // Queue plugin event
    };
    $(document).ready(function(){
    	swfu = new SWFUpload(settings);
        
        vfsHelper.Init();
        vfs.BrowseScreen();
        
//		$("[@name=viewMode]").change( function() {
//		    vfsHelper.RenderFilePreview();
//		});
		<? if (!empty( $isMCE ) ) { ?>
		tinyMCEPopup.executeOnLoad('InitFunction();');
		<? } ?>
   });

<?php
    if ( !empty( $isNormal ) ) {
?>
    if (window.opener && !window.opener.closed) {
        window.opener.vfsSelector.Block();
    }
<?php
    }
?>
</script>
<body>