<?php
    /**
     * SourceFeedUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class SourceFeedUtility {
        const TOP_FEMALE = 'top-female';
        const TOP_MALE = 'top-male';

        public static $Tops = array(self::TOP_FEMALE, self::TOP_MALE);

        const Source = 'source';

        const Ads = 'ads';

        public static $Types = array(
            self::Source => 'Источник',
            self::Ads => 'Рекламная лента',
        );

        public static function IsTopFeed(SourceFeed $sourceFeed) {
            return in_array($sourceFeed->externalId, self::$Tops);
        }

        public static function GetInfo($sourceFeeds) {
            $sourceInfo = array();

            foreach ($sourceFeeds as $sourceFeed) {
                $sourceInfo[$sourceFeed->sourceFeedId] = array(
                    'name' => $sourceFeed->title,
                    'img' => ''
                );

                //group image
                $path = 'temp://userpic-' . $sourceFeed->externalId . '.jpg';
                $filePath = Site::GetRealPath($path);
                if (!file_exists($filePath)) {
                    $avatarPath = Site::GetWebPath('images://fe/no-avatar.png');

                    try {
                        $parser = new ParserVkontakte();
                        $info = $parser->get_info(ParserVkontakte::VK_URL . '/public' . $sourceFeed->externalId);

                        if (!empty($info['avatarа'])) {
                            $avatarPath = $info['avatarа'];
                        }
                    } catch (Exception $Ex) {}

                    file_put_contents($filePath, file_get_contents($avatarPath));
                }

                $sourceInfo[$sourceFeed->sourceFeedId]['img'] = Site::GetWebPath($path);
            }

            return $sourceInfo;
        }
    }
?>