<?php
    /** @var Article $object */

    $prefix = "article";

    if ( empty( $errors ) ) $errors = array();
	if ( empty( $jsonErrors ) ) $jsonErrors = '{}';

    if ( !empty($errors["fatal"] ) ) {
		?><h3 class="error"><?= LocaleLoader::Translate( 'errors.fatal.' . $errors["fatal"] ); ?></h3><?
	}

    CssHelper::PushFile( 'js://ext/uploadify/uploadify.css' );
    JsHelper::PushFiles( array(
        'js://ext/swfobject/swfobject.js'
        , 'js://ext/uploadify/jquery.uploadify.js'
        , 'js://vt/files.js'
    ));
?>
<div class="tabs">
	<?= FormHelper::FormHidden( 'selectedTab', !empty( $selectedTab ) ? $selectedTab : 0, 'selectedTab' ); ?>
    <ul class="tabs-list">
        <li><a href="#page-0">{lang:vt.common.commonInfo}</a></li>
        <li><a href="#page-1">{lang:vt.article.recordInfo}</a></li>
    </ul>

    <div id="page-0" class="tab-page rows">
        <div data-row="importedAt" class="row required">
            <label>{lang:vt.article.importedAt}</label>
            <?= FormHelper::FormDateTime( $prefix . '[importedAt]', $object->importedAt, 'd.m.Y G:i' ); ?>
        </div>
        <div data-row="createdAt" class="row required">
            <label>{lang:vt.article.createdAt}</label>
            <?= FormHelper::FormDateTime( $prefix . '[createdAt]', $object->createdAt, 'd.m.Y G:i' ); ?>
        </div>
        <div data-row="externalId" class="row required">
            <label>{lang:vt.common.externalId}</label>
            <?= FormHelper::FormInput( $prefix . '[externalId]', $object->externalId, 'externalId', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="rate" class="row required">
            <label>{lang:vt.articleRecord.rate}</label>
            <?= FormHelper::FormInput( $prefix . '[rate]', $object->rate, 'rate', null, array( 'size' => 80, 'style' => 'width: 100px;' ) ); ?>
        </div>
        <div data-row="sourceFeedId" class="row required">
            <label>{lang:vt.article.sourceFeedId}</label>
            <?= FormHelper::FormSelect( $prefix . '[sourceFeedId]', $sourceFeeds, "sourceFeedId", "title", $object->sourceFeedId, null, null, false ); ?>
        </div>
        <div data-row="statusId" class="row required">
            <label>{lang:vt.article.statusId}</label>
            <?= FormHelper::FormSelect( $prefix . '[statusId]', StatusUtility::$Common[$__currentLang], "", "", $object->statusId, null, null, false ); ?>
        </div>
	</div>
    <div id="page-1" class="tab-page rows">
        {increal:tmpl://vt/articles/record.tmpl.php}
    </div>
</div>
<script type="text/javascript">
    var jsonErrors  = {$jsonErrors};
</script>