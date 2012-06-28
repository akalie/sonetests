<?
    /** @var $article Article */
    /** @var $articleRecord ArticleRecord */
    /** @var $sourceFeed SourceFeed */
    /** @var $sourceInfo array */

    if (!empty($article)) {

        $extLinkLoader  = false;

        if (SourceFeedUtility::IsTopFeed($sourceFeed) && !empty($articleRecord->photos)) {
            $extLinkLoader = true;
        }
?>

<div class="post bb <?= ($sourceFeed->type != SourceFeedUtility::Ads) ? 'movable' : '' ?>" data-id="<?= $article->articleId; ?>">
    <div class="l d-hide">
        <div class="userpic"><img src="<?=$sourceInfo[$article->sourceFeedId]['img']?>" alt="" /></div>
    </div>
    <div class="name d-hide"><?=$sourceInfo[$article->sourceFeedId]['name']?></div>
    <div class="content">
        <?
        $content = nl2br(HtmlHelper::RenderToForm($articleRecord->content));
        $contentPart1 = mb_substr($content, 0, 300);
        $contentPart2 = mb_substr($content, 300);
        $contentPart1 = !empty($contentPart1) ? $contentPart1 : ''
        ?>
        <div class="shortcut"><?= $contentPart1; ?></div>
        <? if($contentPart2) { ?>
        <a href="javascript:;" class="show-cut">Показать полностью...</a>
        <div class="cut"><?= $contentPart2; ?></div>
        <? } ?>

        <?
        if (!empty($articleRecord->link)) {
            ?>
            <div class="link-info-content">
                <div class="link-description-content">
                    <img src="<?= Site::GetWebPath("images://fe/ajax-loader.gif") ;?>" alt="" class="<?= ($extLinkLoader) ? 'ajax-loader-ext' : 'ajax-loader' ?>" rel="<?= FormHelper::RenderToForm( $articleRecord->link ); ?>" />
                </div>
            </div>
            <?
        }
        ?>

        <?
        if (!empty($articleRecord->photos)) {
            $i = 0;

            ?><div class="images-ready"><?
                foreach($articleRecord->photos as $photoItem) {
                    $i++;
                    $path = MediaUtility::GetFilePath( 'Article', 'photos', 'original', $photoItem['filename'], MediaServerManager::$MainLocation);
                    $photoTitle = !empty($photoItem['title']) ? $photoItem['title'] : '';
                    $photoTitle = nl2br($photoTitle);

                    if ($i == 1) {
                        $count = count($articleRecord->photos);
                        $imgClass = 'first';
                        $imgWidth = ($count == 1) ? false : 228;
                        $imgHeight = ($count == 1) ? false : (ceil(($count - 1) / 3) * 100) - 2;
                    } else {
                        $imgClass = 'else';
                        $imgWidth = 98;
                        $imgHeight = 98;
                    }
                    $imgWidth = $imgWidth ? $imgWidth.'px' : 'auto';
                    $imgHeight = $imgHeight ? $imgHeight.'px' : 'auto';

                    ?><a class="fancybox-thumb" rel="fancybox-thumb-<?= $article->articleId; ?>" href="<?= $path; ?>" title="<?= FormHelper::RenderToForm( $photoTitle ); ?>">
                        <div style="width:<?= $imgWidth; ?>;height:<?= $imgHeight; ?>" class="<?= $imgClass; ?> post-image <?= SourceFeedUtility::IsTopFeed($sourceFeed) ? 'post-image-top' : '' ?>">
                            <img src="<?= $path; ?>" alt="" />
                        </div>
                    </a>
                    <?
                }
                ?></div><?
        }
        ?>
    </div>
    <div class="bottom d-hide">
        <div class="l"><span class="timestamp"><?= $article->createdAt->defaultFormat(); ?></span> | <a class="edit" href="javascript:;">Редактировать</a> | <a class="clear-text" href="javascript:;">Очистить текст</a></div>
        <div class="r">
            <? if (!empty($articleRecord->link)) { ?>
            <span class="attach-icon attach-icon-link" title="Пост со ссылкой"><!-- --></span>
            <? } ?>
            <? if (UrlParser::IsContentWithLink($articleRecord->content)) { ?>
            <span class="attach-icon attach-icon-link-red" title="Пост со ссылкой в контенте"><!-- --></span>
            <? } ?>
            <? if (UrlParser::IsContentWithHash($articleRecord->content)) { ?>
            <span class="hash-span" title="Пост с хештэгом">#hash</span>
            <? } ?>
            <span class="original">
                        <? if($article->externalId != -1){ ?>
                <a href="http://vk.com/wall-<?= $article->externalId; ?>" target="_blank">Оригинал</a>
                <? } else { ?>
                Добавлена вручную
                <? } ?>
                    </span> | <span class="likes spr"></span><span class="likes-count"><?= ($article->rate > 100) ? 'TOP' : $article->rate ?></span>
        </div>
    </div>
    <div class="delete spr"></div>
    <div class="clear"></div>
</div>
<? } ?>