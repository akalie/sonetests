<?php
    /** @var AuditEvent[] $list */

    $__pageTitle = LocaleLoader::Translate( "vt.screens.auditEvent.list");

    $grid = array(
        "columns" => array(
           LocaleLoader::Translate( "vt.auditEvent.auditEventTypeId" )
            , LocaleLoader::Translate( "vt.auditEvent.object" )
            , LocaleLoader::Translate( "vt.auditEvent.objectId" )
            , LocaleLoader::Translate( "vt.auditEvent.message" )
            , LocaleLoader::Translate( "vt.auditEvent.createdAt" )
        )
        , "colspans"	=> array()
        , "sorts"		=> array(0 => "auditEventType.title", 1 => "object", 2 => "objectId", 3 => "message", 4 => "createdAt")
        , "operations"	=> false
        , "allowAdd"	=> false
        , "canPages"	=> AuditEventFactory::CanPages()
        , "basepath"	=> Site::GetWebPath( "vt://" )
        , "addpath"		=> Site::GetWebPath( "vt://audit/add" )
        , "title"		=> $__pageTitle
		, "description"	=> ''
        , "pageSize"	=> HtmlHelper::RenderToForm( $search["pageSize"] )
        , "deleteStr"	=> LocaleLoader::Translate( "vt.auditEvent.deleteString")
    );
	
	//$__breadcrumbs = array( array( 'link' => $grid['basepath'], 'title' => $__pageTitle ) );
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
		{$grid[description]}
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
                    <label>{lang:vt.auditEvent.object}</label>
                    <?= FormHelper::FormInput( "search[object]", $search['object'], 'object', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.auditEvent.objectId}</label>
                    <?= FormHelper::FormInput( "search[objectId]", $search['objectId'], 'objectId', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.auditEvent.auditEventTypeId}</label>
                    <?= FormHelper::FormSelect( "search[auditEventTypeId]", $auditEventTypes, "auditEventTypeId", "title", $search['auditEventTypeId'], null, null, true ); ?>
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
        $id         = $object->auditEventId;
        $editpath   = $grid['basepath'] . "edit/" . $id;
?>
			<tr data-object-id="{$id}">
                <td class="header">{$object.auditEventType.title}</td>
                <td>{$object.object}</td>
                <td>{$object.objectId}</td>
                <td>{form:$object.message}</td>
                <td><?= ( !empty( $object->createdAt ) ? $object->createdAt->DefaultFormat() : '' ) ?></td>
	        </tr>
<?php
    }
?>
		{increal:tmpl://vt/elements/datagrid/footer.tmpl.php}
		<!-- EOF GRID -->
	</div>
</div>
{increal:tmpl://vt/footer.tmpl.php}