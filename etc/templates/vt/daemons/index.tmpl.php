<?php
    $__pageTitle = LocaleLoader::Translate( "vt.screens.daemonLock.list");

    $grid = array(
        "columns" => array(
           LocaleLoader::Translate( "vt.daemonLock.title" )
            , LocaleLoader::Translate( "vt.daemonLock.packageName" )
            , LocaleLoader::Translate( "vt.daemonLock.methodName" )
            , LocaleLoader::Translate( "vt.daemonLock.runAt" )
            , LocaleLoader::Translate( "vt.daemonLock.maxExecutionTime" )
            , LocaleLoader::Translate( "vt.daemonLock.isActive" )
        )
        , "colspans"   => array()
        , "operations" => false
        , "allowAdd"   => false
        , "canPages"   => DaemonLockFactory::CanPages()
        , "basepath"   => Site::GetWebPath( "vt://daemons/" )
        , "title"      => $__pageTitle
        , "pageSize"   => FormHelper::RenderToForm( $search["pageSize"] )
        , "deleteStr"  => LocaleLoader::Translate( "vt.screens.daemonLock.deleteString")
    );

    $__breadcrumbs = array( array( 'link' => $grid['basepath'], 'title' => $__pageTitle ) );
?>
{increal:tmpl://vt/header.tmpl.php}
<div class="main">
	<div class="inner">
		{increal:tmpl://vt/elements/menu/breadcrumbs.tmpl.php}
		<div class="pagetitle">
			<? if( $grid['allowAdd'] ) { ?>
			<div class="controls"><a href="{$grid[addpath]}" class="add"><span>{lang:vt.common.add}</span></a></div>
			<? } ?>
			<h1>{$__pageTitle}</h1>
		</div>
        
		<div class="search<?= $hideSearch == "true" ? " closed" : ""  ?>">
			<a href="#" class="search-close"><span>{lang:vt.common.closeSearch}</span></a>
			<a href="#" class="search-open"><span>{lang:vt.common.openSearch}</span></a>
			<form class="search-form" id="searchForm" method="post" action="{$grid[basepath]}">
				<input type="hidden" value="1" name="searchForm" />
				<input type="hidden" value="" id="pageId" name="page" />
				<input type="hidden" value="{$grid[pageSize]}" id="pageSize" name="search[pageSize]" />
				<input type="hidden" value="{form:$sortField}" id="sortField" name="sortField" />
				<input type="hidden" value="{form:$sortType}" id="sortType" name="sortType" />
                    <div class="row">
                        <label>{lang:vt.daemonLock.title}</label>
                        <td><?= FormHelper::FormInput( "search[title]", $search["title"], "80", "title" ); ?></td>
                    </div>
                    <div class="row">
                        <label>{lang:vt.daemonLock.packageName}</label>
                        <td><?= FormHelper::FormInput( "search[packageName]", $search["packageName"], "80", "packageName" ); ?></td>
                    </div>
                    <div class="row">
                        <label>{lang:vt.daemonLock.methodName}</label>
                        <td><?= FormHelper::FormInput( "search[methodName]", $search["methodName"], "80", "methodName" ); ?></td>
                    </div>
				<input type="submit" value="{lang:vt.common.find}" />
			</form>
		</div>
    <!-- GRID -->
{increal:tmpl://vt/elements/datagrid/header.tmpl.php}
<?php
    $langEdit   = LocaleLoader::Translate( "vt.common.edit" );
    $langDelete = LocaleLoader::Translate( "vt.common.delete" );

    foreach ( $list as $object )  {
        $id         = $object->daemonLockId;
        $editpath   = $grid['basepath'] . "edit/" . $id;
?>
			<tr data-object-id="{$id}">
                <td>{$object.title}</td>
                <td>{$object.packageName}</td>
                <td>{$object.methodName}</td>
                <td><?= ( !empty( $object->runAt ) ? $object->runAt->DefaultFormat() : '' ) ?></td>
                <td>{$object.maxExecutionTime.DefaultTimeFormat()}</td>
                <td><?= StatusUtility::GetBoolTemplate( $object->isActive ) ?></td>
	        </tr>
<?php
    }
?>
{increal:tmpl://vt/elements/datagrid/footer.tmpl.php}
    <!-- EOF GRID -->
    	</div>
</div>
{increal:tmpl://vt/footer.tmpl.php}