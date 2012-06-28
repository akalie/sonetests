<?php
    /** @var ArticleRecord $articleRecord */

    $prefixRecord = "articleRecord";
?>
<div data-row="content" class="row">
    <label>{lang:vt.articleRecord.content}</label>
    <?= FormHelper::FormTextArea( $prefixRecord . '[content]', $articleRecord->content, 'content', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
</div>
<div data-row="likes" class="row">
    <label>{lang:vt.articleRecord.likes}</label>
    <?= FormHelper::FormInput( $prefixRecord . '[likes]', $articleRecord->likes, 'likes', null, array( 'size' => 80, 'style' => 'width: 100px;' ) ); ?>
</div>
<div data-row="rate" class="row">
    <label>{lang:vt.articleRecord.rate}</label>
    <?= FormHelper::FormInput( $prefixRecord . '[rate]', $articleRecord->rate, 'rate', null, array( 'size' => 80, 'style' => 'width: 100px;' ) ); ?>
</div>

<div data-row="link" class="row">
    <label>{lang:vt.articleRecord.link}</label>
    <?= FormHelper::FormInput( $prefixRecord . '[link]', $articleRecord->link, 'link', null, array( 'size' => 80, 'style' => 'width: 200px;' ) ); ?>
</div>
<div data-row="map" class="row">
    <label>{lang:vt.articleRecord.map}</label>
    <?= FormHelper::FormInput( $prefixRecord . '[map]', $articleRecord->map, 'map', null, array( 'size' => 80, 'style' => 'width: 200px;' ) ); ?>
</div>
<div data-row="poll" class="row">
    <label>{lang:vt.articleRecord.poll}</label>
    <?= FormHelper::FormInput( $prefixRecord . '[poll]', $articleRecord->poll, 'poll', null, array( 'size' => 80, 'style' => 'width: 200px;' ) ); ?>
</div>
<div data-row="doc" class="row">
    <label>{lang:vt.articleRecord.doc}</label>
    <?= FormHelper::FormInput( $prefixRecord . '[doc]', $articleRecord->doc, 'doc', null, array( 'size' => 80, 'style' => 'width: 200px;' ) ); ?>
</div>

<div data-row="retweet" class="row">
    <label>{lang:vt.articleRecord.retweet}</label>
    <?= FormHelper::FormInput( $prefixRecord . '[retweet]', ObjectHelper::ToJSON($articleRecord->retweet), 'retweet', null, array( 'size' => 80, 'style' => 'width: 200px;' ) ); ?>
</div>
<div data-row="video" class="row">
    <label>{lang:vt.articleRecord.video}</label>
    <?= FormHelper::FormInput( $prefixRecord . '[video]', ObjectHelper::ToJSON($articleRecord->video), 'video', null, array( 'size' => 80, 'style' => 'width: 200px;' ) ); ?>
</div>
<div data-row="music" class="row">
    <label>{lang:vt.articleRecord.music}</label>
    <?= FormHelper::FormInput( $prefixRecord . '[music]', ObjectHelper::ToJSON($articleRecord->music), 'music', null, array( 'size' => 80, 'style' => 'width: 200px;' ) ); ?>
</div>
<div data-row="textLinks" class="row">
    <label>{lang:vt.articleRecord.textLinks}</label>
    <?= FormHelper::FormInput( $prefixRecord . '[text_links]', ObjectHelper::ToJSON($articleRecord->text_links), 'text_links', null, array( 'size' => 80, 'style' => 'width: 200px;' ) ); ?>
</div>

<div data-row="files" class="row">
    <label>{lang:vt.articleRecord.photos}</label>
    <div style="display: inline-block; //display: inline;">
        <input id="file_upload" name="file_upload" type="file" />
    </div>
</div>
<script type="text/javascript">
    var filesJSON   = {$filesJSON};
</script>
<script type="text/javascript">
    function uploadCallback( file,data ) {
        t = $("#fileTemplate").tmpl( {title: '', filename: data.filename, isTemp: data.isTemp, path: data.path, name : file.name}, { counter: filesCounter } );
        $('#' + file.id).replaceWith( t );
    }
</script>
<script id="fileTemplate" type="text/x-jquery-tmpl">
    <div class="uploadifyQueueItem sort" id="file-${ $item.counter.nextIndex() }">
        <input type="hidden" name="files[${ $item.counter.index }][filename]" value="${filename}">
        <img src="{web:images://vt/common/objects/sort-link.gif}" style="cursor: move;" class="handle">
        <div class="cancel"><a href="#" title="Удалить" class="delete-file"><img src="{web:js://ext/uploadify/uploadify-cancel.png}" border="0"></a></div>
        <span class="fileName">${filename}</span>
        <br /><br /><img src="${path}" alt="" />
        <br /><br /><textarea rows="2" cols="80" name="files[${ $item.counter.index }][title]" style="width: 95%;">${title}</textarea>
    </div>
</script><script>''</script>