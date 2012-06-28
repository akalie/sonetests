{increal:tmpl://vt/elements/vfs/header.tmpl.php}
<div id="wrap">
<ul id="mainMenu">
	<li class="active"><a href="javascript:showScreen('browse');">{lang:vt.vfs.explorer}</a></li>
	<li><a href="javascript:showScreen('add');">{lang:vt.vfs.uploadFile}</a></li>
	<li><a href="javascript:showScreen('queue');">{lang:vt.vfs.uploadFiles}</a></li>
</ul>

<div id="content">
	<div id="subMenu"></div>
    <div id="table">
		<div id="tableHeader">
		  <img align="left" id="loader" src="{web:images://vfs/ajax-loader.gif}" />
			<strong>{lang:vt.vfs.view}:</strong>
			<input onchange="vfsHelper.RenderFilePreview();" name="viewMode" id="viewMode1" checked="checked" value="normal" type="radio" />
			<label for="viewMode1">{lang:vt.vfs.tableView}</label>
			<input onchange="vfsHelper.RenderFilePreview();"  name="viewMode" id="viewMode2" value="preview" type="radio" />
			<label for="viewMode2">{lang:vt.vfs.preView}</label>
			<span class="navigationBar"></span>
		</div>

		<div id="browseScreen">
            <table>
            <tr>
                <td class="leftScreen">
                    <table cellspacing="0" class="catalogue" id="foldersList"></table>
                    <table cellspacing="0" class="catalogue" id="favoritesTable">
                        <caption>favorites</caption>
                        <tbody id="favoritesList"></tbody>
                    </table>
                    <br>
                    <p><strong>{lang:vt.vfs.createFolder}:</strong> <input id="newFolder" name="newFolder" value="" type="text" />
                    <input type="button" onclick="javascript:vfsHelper.CreateFolder();" value="OK"/></p>
                </td>
                <td class="rightScreen">
                    <table class="fileslist" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="first">{lang:vt.vfs.fileName}</th>
                            <th class="second">{lang:vt.vfs.fileType}</th>
                            <th class="third">{lang:vt.vfs.fileSize}</th>
                            <th class="fourth">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody id="fileList"></tbody>
                    </table>
                  </td>
                </table>
                <div id="tableFooter">
                    <span class="navigationBar"></span>
    		    </div>
		</div>

		<div id="addScreen">
    		<table cellspacing="0">
    			<tr>
    				<td class="tfbFirst">{lang:vt.vfs.fileName}</td>
    				<td class="tfbSecond"><input name="fileName" id="fileName" type="text" /></td>
    			</tr>
    			<tr>
    				<td class="tfbFirst">{lang:vt.vfs.file}</td>
    				<td class="tfbSecond"><input name="fileUpload" onchange="javascript:vfs.UploadTempFile('fileUpload');" id="fileUpload" type="file" /></td>
    			</tr>
    			<tr>
    			     <td></td>
    			     <td><button id="btnCreateFile" onclick="vfsHelper.CreateFile();" disabled="disabled">{lang:vt.vfs.uploadFile}</button> </td>
    			</tr>
    		</table>
		</div>

		<div id="queueScreen">
		</div>
		<div id="swfuContainer">
            <div class="fieldset flash" id="fsUploadProgress">
            <span class="legend">Upload Queue</span>
            </div>
            <div id="divStatus">0 Files Uploaded</div>
            <div>
                <span id="spanButtonPlaceHolder"></span>
                <input id="btnCancel" type="button" value="Cancel All Uploads" onclick="swfu.cancelQueue();" disabled="disabled" style="margin-left: 2px; font-size: 8pt; height: 29px;" />
            </div>
		</div>
	</div>
	<div id="contFooter">
<?php
    if ( !empty( $isFolder ) ) {
?>
		<input onclick="javascript:selectFolder()" value="{lang:vt.vfs.chooseFolder}" type="button" />
<?php
    }
?>
		<input onclick="javascript:closeWindow()" value="{lang:vt.common.exit}" type="button" />
	</div>
</div>
</div> <!-- #wrap -->
{increal:tmpl://vt/elements/vfs/footer.tmpl.php}