{increal:tmpl://fe/elements/header.tmpl.php}
<div id="go-to-top">Наверх</div>
<div class="layer">
    <div class="left-panel">
        <div class="block">
            <div class="header bb">

                <select multiple="multiple" id="source-select">
                    <?
                        foreach ($sourceFeeds as $sourceFeed) {
                            ?><option value="{$sourceFeed.sourceFeedId}" <?= (in_array($sourceFeed->sourceFeedId, $currentSourceFeedIds)) ? 'selected="selected"' : '' ?>>{$sourceFeed.title}</option><?
                        }
                    ?>
                </select>

                <div class="type-selector">
                    <? foreach(SourceFeedUtility::$Types as $sourceType => $sourceTypeTitle) { ?>
                        <a href="#" class="<?= ($sourceType == $currentSourceType) ? 'active' : '' ?>" data-type="{$sourceType}">{$sourceTypeTitle}</a>
                    <? } ?>
                </div>

                <!--div class="controls">
                    <div class="ctl spr gear"></div>
                    <div class="ctl spr plus"></div>
                    <div class="ctl spr del"></div>
                </div -->

                <p style="padding: 5px; <?= ($currentSourceType == SourceFeedUtility::Ads) ? 'display: none;' : '' ?>" id="slider-text">
                    <label>Лайки:</label>
                    <span id="slider-value"></span>
                </p>
                <div style="padding: 10px !important; <?= ($currentSourceType == SourceFeedUtility::Ads) ? 'display: none;' : '' ?>" id="slider-cont">
                    <div id="slider-range"></div>
                </div>
            </div>
            {increal:tmpl://fe/elements/new-post-form.tmpl.php}

            <div class="wall" id="wall">

            </div>

            <div id="wallloadmore" class="hidden">Больше</div>
        </div>
    </div>

    <div class="right-panel">
        <div class="block">
            <div class="header bb">

                <div class="calendar">
                    <input type="text" id="calendar" value="<?= $currentDate->DefaultDateFormat() ?>"/>
                    <div class="caption default">Дата</div>
                    <div class="tip"><b>cal</b></div>
                </div>

                <div class="drop-down right-drop-down">
                    <div class="caption default">Паблик</div>
                    <div class="tip"><s></s></div>
                    <ul>
                        <?
                        foreach ($targetFeeds as $targetFeed) {
                            ?><li data-id="{$targetFeed.targetFeedId}" class="<?= $targetFeed->targetFeedId == $currentTargetFeedId ? 'active' : '' ?>">{$targetFeed.title}</li><?
                        }
                        ?>
                    </ul>
                </div>

                <!--div class="controls">
                    <div class="ctl spr gear"></div>
                    <div class="ctl spr plus"></div>
                    <div class="ctl spr del"></div>
                </div -->

            </div>

            <div class="items block drop" id="queue" style="display: none;">
            </div>
        </div>
    </div>
</div>
{increal:tmpl://fe/elements/footer.tmpl.php}