<?php
    /** @var TargetFeed $object */

    $prefix = "targetFeed";

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
        <li><a href="#page-1">{lang:vt.targetFeed.grids}</a></li>
    </ul>

    <div id="page-0" class="tab-page rows">
        <div data-row="type" class="row required">
            <label>{lang:vt.sourceFeed.type}</label>
            <?= FormHelper::FormSelect( $prefix . '[type]', TargetFeedUtility::$Types, "", "", $object->type, null, null, false ); ?>
        </div>
        <div data-row="title" class="row required">
            <label>{lang:vt.targetFeed.title}</label>
            <?= FormHelper::FormInput( $prefix . '[title]', $object->title, 'title', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="externalId" class="row required">
            <label>{lang:vt.common.externalId}</label>
            <?= FormHelper::FormInput( $prefix . '[externalId]', $object->externalId, 'externalId', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="vkIds" class="row">
            <label>{lang:vt.targetFeed.vkIds}</label>
            <div class="hint">
                <a href="#" class="hint-icon">?</a>
                <div class="hint-text" style="display:none;">
                    <span>?</span>
                    Введите vkId редакторов через запятую
                </div>
            </div>
            <?= FormHelper::FormInput( $prefix . '[vkIds]', $object->vkIds, 'vkIds', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="token" class="row">
            <label>{lang:vt.targetFeed.token}</label>
            <div class="hint">
                <a href="#" class="hint-icon">?</a>
                <div class="hint-text" style="display:none;">
                    <span>?</span>
                    Используется только для Facebook
                </div>
            </div>
            <?= FormHelper::FormInput( $prefix . '[params][token]', !empty($object->params['token']) ? $object->params['token'] : '', 'token', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="publishers" class="row">
            <label>{lang:vt.targetFeed.publishers}</label>
            <?= FormHelper::FormSelectMultiple( 'publisherIds[]', $publishers, 'publisherId', 'name', $publisherIds, 'publisherIds', null, null, array('style' => 'height: 200px;') ) ?>
        </div>
        <div data-row="statusId" class="row required">
            <label>{lang:vt.targetFeed.statusId}</label>
            <?= FormHelper::FormSelect( $prefix . '[statusId]', StatusUtility::$Common[$__currentLang], "", "", $object->statusId, null, null, false ); ?>
        </div>
	</div>
    <div id="page-1" class="tab-page rows">
        <div data-row="grids" class="row" style="display: none;"></div>
        <table class="objects objects-inner" style="width: 40%;" id="grids-table">
            <tr>
                <th>Дата начала</th>
                <th>Шаг (мин.)</th>
                <th></th>
            </tr>
        </table>
        <div style="width: 40%; text-align: center;">
            <a href="#" class="add-row">Добавить строку</a>
        </div>
    </div>
</div>
<script type="text/javascript">
	var jsonErrors = {$jsonErrors};
	var gridData = {$gridData};

    var gridRows = 0;

    $(document).ready( function () {
        $("li.delete a").live('click', function(e) {
            $(this).parents('tr').remove();
            e.preventDefault();
        });
        $("a.add-row").live('click', function(e) {
            addGridRow(null);
            e.preventDefault();
        });

        if (gridData) {
            for (i in gridData) {
                addGridRow(gridData[i]);
            }
        }

        if (jsonErrors && jsonErrors.grids) {
            for (i in jsonErrors.grids.errors) {
                error = jsonErrors.grids.errors[i];
                id = error.replace('errors.', '');
                $('[data-row-id=' + id + ']').addClass('error');
            }
        }
    });

    function addGridRow(item) {
        id = gridRows++;

        var tpl = '<tr data-row-id="{id}">\
                <td><input type="hidden" name="targetFeed[grids][{id}][startDate]" value="{startDate}" class="dtpicker" rel="dateTime"></td>\
                <td>\
                    <input type="text" name="targetFeed[grids][{id}][period]" value="{period}">\
                    <input type="hidden" name="targetFeed[grids][{id}][targetFeedGridId]" value="{targetFeedGridId}">\
                </td>\
                <td><ul class="actions"><li class="delete"><a href="#"></a></li></ul></td>\
            </tr>';

        tpl = tpl.replace(/{id}/g, id);
        if (item) {
            tpl = tpl.replace(/{startDate}/g, item.startDate);
            tpl = tpl.replace(/{period}/g, item.period);
            tpl = tpl.replace(/{targetFeedGridId}/g, item.targetFeedGridId);
        } else {
            tpl = tpl.replace(/{startDate}/g, '');
            tpl = tpl.replace(/{period}/g, '60');
            tpl = tpl.replace(/{targetFeedGridId}/g, '');
        }

        $('#grids-table').append(tpl);
        $('.dtpicker').datetimepicker();
    }
</script>
 