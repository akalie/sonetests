<?php
    /** @var ArticleQueue[] $list */

    $__pageTitle = LocaleLoader::Translate( "vt.screens.articleQueue.list");

    $grid = array(
        "columns" => array(
           LocaleLoader::Translate( "vt.articleQueue.startDate" )
            , LocaleLoader::Translate( "vt.articleQueue.endDate" )
            , LocaleLoader::Translate( "vt.articleQueue.createdAt" )
            , LocaleLoader::Translate( "vt.articleQueue.sentAt" )
            , LocaleLoader::Translate( "vt.articleQueue.articleId" )
            , LocaleLoader::Translate( "vt.articleQueue.targetFeedId" )
            , LocaleLoader::Translate( "vt.articleQueue.statusId" )
        )
        , "colspans"	=> array()
        , "sorts"		=> array(0 => "startDate", 1 => "endDate", 2 => "createdAt", 3 => "sentAt", 4 => "articleId", 5 => "targetFee.title", 6 => "statusId")
        , "operations"	=> true
        , "allowAdd"	=> true
        , "canPages"	=> ArticleQueueFactory::CanPages()
        , "basepath"	=> Site::GetWebPath( "vt://article-queues/" )
        , "addpath"		=> Site::GetWebPath( "vt://article-queues/add" )
        , "title"		=> $__pageTitle
		, "description"	=> ''
        , "pageSize"	=> HtmlHelper::RenderToForm( $search["pageSize"] )
        , "deleteStr"	=> LocaleLoader::Translate( "vt.articleQueue.deleteString")
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
                    <label>{lang:vt.articleQueue.targetFeedId}</label>
                    <?= FormHelper::FormSelect( "search[targetFeedId]", $targetFeeds, "targetFeedId", "title", $search['targetFeedId'], null, null, true ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.articleQueue.statusId}</label>
                    <?= FormHelper::FormSelect( "search[statusId]", StatusUtility::$Queue[$__currentLang], "", "", $search['statusId'], null, null, true ); ?>
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
        $id         = $object->articleQueueId;
        $editpath   = $grid['basepath'] . "edit/" . $id;
?>
			<tr data-object-id="{$id}">
                <td><?= ( !empty( $object->startDate ) ? $object->startDate->DefaultFormat() : '' ) ?></td>
                <td><?= ( !empty( $object->endDate ) ? $object->endDate->DefaultFormat() : '' ) ?></td>
                <td><?= ( !empty( $object->createdAt ) ? $object->createdAt->DefaultFormat() : '' ) ?></td>
                <td><?= ( !empty( $object->sentAt ) ? $object->sentAt->DefaultFormat() : '' ) ?></td>
                <td>{$object.articleId}</td>
                <td><?= !empty($targetFeeds[$object->targetFeedId]) ? $targetFeeds[$object->targetFeedId]->title : '' ?></td>
                <td><?= StatusUtility::GetQueueStatusTemplate($object->statusId) ?></td>
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