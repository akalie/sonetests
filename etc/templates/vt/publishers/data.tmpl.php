<?php
    /** @var Publisher $object */

    $prefix = "publisher";

    if ( empty( $errors ) ) $errors = array();
	if ( empty( $jsonErrors ) ) $jsonErrors = '{}';

    if ( !empty($errors["fatal"] ) ) {
		?><h3 class="error"><?= LocaleLoader::Translate( 'errors.fatal.' . $errors["fatal"] ); ?></h3><?
	}
?>
<div class="tabs">
	<?= FormHelper::FormHidden( 'selectedTab', !empty( $selectedTab ) ? $selectedTab : 0, 'selectedTab' ); ?>
    <ul class="tabs-list">
        <li><a href="#page-0">{lang:vt.common.commonInfo}</a></li>
    </ul>

    <div id="page-0" class="tab-page rows">
        <div data-row="name" class="row required">
            <label>{lang:vt.publisher.name}</label>
            <?= FormHelper::FormInput( $prefix . '[name]', $object->name, 'name', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="vk_id" class="row required">
            <label>{lang:vt.publisher.vk_id}</label>
            <?= FormHelper::FormInput( $prefix . '[vk_id]', $object->vk_id, 'vk_id', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="vk_app" class="row required">
            <label>{lang:vt.publisher.vk_app}</label>
            <?= FormHelper::FormInput( $prefix . '[vk_app]', $object->vk_app, 'vk_app', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="vk_token" class="row required">
            <label>{lang:vt.publisher.vk_token}</label>
            <?= FormHelper::FormInput( $prefix . '[vk_token]', $object->vk_token, 'vk_token', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="vk_seckey" class="row required">
            <label>{lang:vt.publisher.vk_seckey}</label>
            <?= FormHelper::FormInput( $prefix . '[vk_seckey]', $object->vk_seckey, 'vk_seckey', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="statusId" class="row required">
            <label>{lang:vt.publisher.statusId}</label>
            <?= FormHelper::FormSelect( $prefix . '[statusId]', StatusUtility::$Common[$__currentLang], "", "", $object->statusId, null, null, false ); ?>
        </div>
	</div>
</div>
<script type="text/javascript">
	var jsonErrors = {$jsonErrors};
</script>
 