{increal:tmpl://vt/header.tmpl.php}
<script type="text/javascript" src="{web:js://vfs/vfs.selector.js}"></script>
<script type="text/javascript">
    var vfsSelector = new VfsSelector( "{web:vt://vfs/}" );
</script>
<?php
    Package::Load( "Base.VFS");
?>
<div id="wrap">
    <div id="cont">
    	<h1>VFS Attach</h1>
    	<div class="blockVert">
            <table class="vertList">
                <tr>
                    <td><input type="hidden"  name="image_1" class="vfsFile" id="image_1" /></td>
                    <td><input type="hidden"  name="image_2" class="vfsFile" id="image_2" /></td>
                </tr>
                <tr>
                    <td>vfshelper1 image <?= VfsHelper::FormVfsFile( "image_3", "image_3", null, "image" );?></td>
                    <td>vfshelper2 <?= VfsHelper::FormVfsFile( "image_4", "image_4", null );?></td>
                </tr>
                <tr>
                    <td colspan="2">folder<?= VfsHelper::FormVfsFolder( "folderId", "folderId", null );?></td>
                </tr>
            </table>
        </div>
<script type="text/javascript">
    vfsSelector.Init();
</script>
{increal:tmpl://vt/footer.tmpl.php}