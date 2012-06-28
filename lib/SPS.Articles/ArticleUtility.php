<?php
    /**
     * ArticleUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class ArticleUtility {

        public static function IsTopArticleWithSmallPhoto(SourceFeed $sourceFeed, ArticleRecord $articleRecord) {
            if (!empty($articleRecord->photos) && count($articleRecord->photos) == 1 && SourceFeedUtility::IsTopFeed($sourceFeed)) {
                $photoItem = current($articleRecord->photos);
                $path = MediaUtility::GetFilePath( 'Article', 'photos', 'original', $photoItem['filename'], MediaServerManager::$MainLocation);
                $dimensions = ImageHelper::GetImageSizes($path);

                if ($dimensions['width'] < 250 && $dimensions['height'] < 250) {
                    return true;
                }
            }

            return false;
        }
    }
?>