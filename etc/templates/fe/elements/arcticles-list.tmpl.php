<?
    /** @var $articles Article[] */
    /** @var $articleRecords ArticleRecord[] */
    /** @var $sourceFeeds SourceFeed[] */
    if (!empty($articles)) {
        foreach($articles as $article) {
            $articleRecord  = !empty($articleRecords[$article->articleId]) ? $articleRecords[$article->articleId] : new ArticleRecord();
            $sourceFeed     = $sourceFeeds[$article->sourceFeedId];

            ?>{increal:tmpl://fe/elements/arcticle-item.tmpl.php}<?
        }
    }
?>
<script type="text/javascript">
    <?
        if (!empty($hasMore)) {
            ?>$("#wallloadmore").removeClass('hidden');<?
        } else {
            ?>$("#wallloadmore").addClass('hidden');<?
        }
    ?>
</script>