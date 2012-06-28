<?php
    /** @var Article[] $list */

    $__pageTitle = LocaleLoader::Translate( "vt.screens.article.list");

    $grid = array(
        "columns" => array(
           LocaleLoader::Translate( "vt.article.createdAt" )
            , LocaleLoader::Translate( "vt.articleQueue.articleId" )
            , LocaleLoader::Translate( "vt.common.externalId" )
            , LocaleLoader::Translate( "vt.article.sourceFeedId" )
            , LocaleLoader::Translate( "vt.article.statusId" )
        )
        , "colspans"	=> array()
        , "sorts"		=> array(0 => "createdAt", 1 => "articleId", 2 => "externalId", 3 => "sourceFeedId", 4 => "statusId")
        , "operations"	=> true
        , "allowAdd"	=> true
        , "canPages"	=> ArticleFactory::CanPages()
        , "basepath"	=> Site::GetWebPath( "vt://articles/" )
        , "addpath"		=> Site::GetWebPath( "vt://articles/add" )
        , "title"		=> $__pageTitle
		, "description"	=> ''
        , "pageSize"	=> HtmlHelper::RenderToForm( $search["pageSize"] )
        , "deleteStr"	=> LocaleLoader::Translate( "vt.article.deleteString")
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
                    <label>{lang:vt.articleQueue.articleId}</label>
                    <?= FormHelper::FormInput( 'search[articleId]', $search['articleId'], 'articleId', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.common.externalId}</label>
                    <?= FormHelper::FormInput( "search[externalId]", $search['externalId'], 'externalId', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.article.sourceFeedId}</label>
                    <?= FormHelper::FormSelect( "search[sourceFeedId]", $sourceFeeds, "sourceFeedId", "title", $search['sourceFeedId'], null, null, true ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.article.statusId}</label>
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
        $id         = $object->articleId;
        $editpath   = $grid['basepath'] . "edit/" . $id;
?>
			<tr data-object-id="{$id}">
                <td class="header"><?= ( !empty( $object->createdAt ) ? $object->createdAt->DefaultFormat() : '' ) ?></td>
                <td>{$object.articleId}</td>
                <td>{form:$object.externalId}</td>
                <td><?= !empty($sourceFeeds[$object->sourceFeedId]) ? $sourceFeeds[$object->sourceFeedId]->title : '' ?></td>
                <td><?= StatusUtility::GetStatusTemplate($object->statusId) ?></td>
				<td width="10%">
					<ul class="actions">
						<li class="edit"><a href="{$editpath}" title="{$langEdit}">{$langEdit}</a></li><li class="delete"><a href="#" class="delete-object" title="{$langDelete}">{$langDelete}</a></li>
					</ul>

                    <a href="{web:vt://article-queues/}?search[articleId]={$id}" style="color: #291;" title="Найти в очереди">Найти в очереди</a>
                    <a href="{web:vt://article-queues/add}?articleId={$id}" style="color: #C00;" title="Добавить в очередь">Добавить в очередь</a>
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