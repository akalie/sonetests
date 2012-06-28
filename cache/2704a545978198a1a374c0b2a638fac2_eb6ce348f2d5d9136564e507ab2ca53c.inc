<?
    /** @var $articlesQueue ArticleQueue[] */

    foreach ($articlesQueue as $articleQueueItem) {
        $articleQueueId = $articleQueueItem->articleQueueId;
        $articleRecord = !empty($articleRecords[$articleQueueId]) ? $articleRecords[$articleQueueId] : new ArticleRecord();
        if (empty($articleRecord)) continue;
        ?>
            <div class="slot locked">
                <div class="time">
                    <?= !empty($articleQueueItem->sentAt) ? $articleQueueItem->sentAt->DefaultFormat() : '' ?>
                    <? if (!empty($articleRecord->link)) { ?>
                    <span class="attach-icon attach-icon-link" title="Пост со ссылкой"><!-- --></span>
                    <? } ?>
                    <? if (UrlParser::IsContentWithLink($articleRecord->content)) { ?>
                    <span class="attach-icon attach-icon-link-red" title="Пост со ссылкой в контенте"><!-- --></span>
                    <? } ?>
                    <? if (UrlParser::IsContentWithHash($articleRecord->content)) { ?>
                    <span class="hash-span" title="Пост с хештэгом">#hash</span>
                    <? } ?>
                </div>
                <div class="post blocked <?= empty($articleQueueItem->sentAt) ? 'failed' : '' ?>">
                    <div class="content">
                        <?
                        $content = nl2br(HtmlHelper::RenderToForm($articleRecord->content));
                        ?>
                        <?= $content; ?>

                        <? if (!empty($articleRecord->photos)) { ?>
                        <? foreach($articleRecord->photos as $photoItem) { ?>
                            <br /><img src="<?= MediaUtility::GetFilePath( 'Article', 'photos', 'small', $photoItem['filename'], MediaServerManager::$MainLocation) ?>">
                            <? } ?>
                        <? } ?>
                    </div>
                </div>
            </div>
        <?
    }
?>