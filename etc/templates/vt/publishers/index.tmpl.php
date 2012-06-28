<?php
    /** @var Publisher[] $list */

    $__pageTitle = LocaleLoader::Translate( "vt.screens.publisher.list");

    $grid = array(
        "columns" => array(
           LocaleLoader::Translate( "vt.publisher.name" )
            , LocaleLoader::Translate( "vt.publisher.vk_id" )
            , LocaleLoader::Translate( "vt.publisher.vk_app" )
            , LocaleLoader::Translate( "vt.publisher.vk_token" )
            , LocaleLoader::Translate( "vt.publisher.vk_seckey" )
            , LocaleLoader::Translate( "vt.publisher.statusId" )
        )
        , "colspans"	=> array()
        , "sorts"		=> array(0 => "name", 1 => "vk_id", 2 => "vk_app", 3 => "vk_token", 4 => "vk_seckey", 5 => "statusId")
        , "operations"	=> true
        , "allowAdd"	=> true
        , "canPages"	=> PublisherFactory::CanPages()
        , "basepath"	=> Site::GetWebPath( "vt://publishers/" )
        , "addpath"		=> Site::GetWebPath( "vt://publishers/add" )
        , "title"		=> $__pageTitle
		, "description"	=> ''
        , "pageSize"	=> HtmlHelper::RenderToForm( $search["pageSize"] )
        , "deleteStr"	=> LocaleLoader::Translate( "vt.publisher.deleteString")
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
                    <label>{lang:vt.publisher.name}</label>
                    <?= FormHelper::FormInput( "search[name]", $search['name'], 'name', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.publisher.vk_id}</label>
                    <?= FormHelper::FormInput( "search[vk_id]", $search['vk_id'], 'vk_id', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.publisher.vk_app}</label>
                    <?= FormHelper::FormInput( "search[vk_app]", $search['vk_app'], 'vk_app', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.publisher.statusId}</label>
                    <?= FormHelper::FormSelect( "search[statusId]", StatusUtility::$Common[$__currentLang], "", "", $search['statusId'], null, null, true ); ?>
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
        $id         = $object->publisherId;
        $editpath   = $grid['basepath'] . "edit/" . $id;
?>
			<tr data-object-id="{$id}">
                <td class="header">{$object.name}</td>
                <td>{$object.vk_id}</td>
                <td>{$object.vk_app}</td>
                <td>{$object.vk_token}</td>
                <td>{$object.vk_seckey}</td>
                <td><?= StatusUtility::GetStatusTemplate($object->statusId) ?></td>
				<td width="10%">
					<ul class="actions">
						<li class="edit"><a href="{$editpath}" title="{$langEdit}">{$langEdit}</a></li><li class="delete"><a href="#" class="delete-object" title="{$langDelete}">{$langDelete}</a></li>
					</ul>
				</td>
	        </tr>
<?php
    }
?>
		{increal:tmpl://vt/elements/datagrid/footer.tmpl.php}
		<!-- EOF GRID -->
	</div>
</div>
{increal:tmpl://vt/footer.tmpl.php}