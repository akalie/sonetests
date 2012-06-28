<?php
    /**
     * AccessUtility
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class AccessUtility {

        private static $targetFeedIds = null;
        private static $sourceFeedIds = null;

        public static function GetTargetFeedIds() {
            if (!is_null(self::$targetFeedIds)) {
                return self::$targetFeedIds;
            }

            $result = array(-1 => -1);
            $userId = AuthVkontakte::IsAuth();

            if (empty($userId)) {
                self::$targetFeedIds = $result;
                return $result;
            }

            //redactor check
            $redactors = SiteParamHelper::GetCachedParamValue(SiteParamHelper::Redactors);
            if (!empty($redactors)) {
                $redactors = explode(',', $redactors);
                if (in_array($userId, $redactors)) {
                    $result = array();
                    self::$targetFeedIds = $result;
                    return $result;
                }
            }

            $checkData = TargetFeedFactory::Get(
                array()
                , array(BaseFactory::WithoutPages => true, BaseFactory::WithColumns => '"targetFeedId", "vkIds"')
            );

            if (!empty($checkData)) {
                foreach ($checkData as $checkDataItem) {
                    $vkIds = explode(',', $checkDataItem->vkIds);
                    if (in_array($userId, $vkIds)) {
                        $result[$checkDataItem->targetFeedId] = $checkDataItem->targetFeedId;
                    }
                }
            }

            self::$targetFeedIds = $result;
            return $result;
        }

        public static function GetSourceFeedIds($currentTargetFeedId = 0) {
            $result = array(-1 => -1);

            if (is_array(self::$sourceFeedIds) && array_key_exists($currentTargetFeedId, self::$sourceFeedIds)) {
                return self::$sourceFeedIds[$currentTargetFeedId];
            }

            $userId = AuthVkontakte::IsAuth();

            if (empty($userId)) {
                self::$sourceFeedIds[$currentTargetFeedId] = $result;
                return $result;
            }


            //redactor check
            if (empty($currentTargetFeedId)) {
                $redactors = SiteParamHelper::GetCachedParamValue(SiteParamHelper::Redactors);
                if (!empty($redactors)) {
                    $redactors = explode(',', $redactors);
                    if (in_array($userId, $redactors)) {
                        $result = array();
                        self::$sourceFeedIds[$currentTargetFeedId] = $result;
                        return $result;
                    }
                }
            }

            $checkData = SourceFeedFactory::Get(
                array()
                , array(BaseFactory::WithoutPages => true, BaseFactory::WithColumns => '"sourceFeedId", "targetFeedIds"')
            );

            if (!empty($checkData)) {
                foreach ($checkData as $checkDataItem) {
                    $targetFeedIds = explode(',', $checkDataItem->targetFeedIds);
                    if (!empty($targetFeedIds)) {
                        foreach ($targetFeedIds as $targetFeedId) {
                            if (self::HasAccessToTargetFeedId($targetFeedId)) {
                                if (empty($currentTargetFeedId)) {
                                    $result[$checkDataItem->sourceFeedId] = $checkDataItem->sourceFeedId;
                                    break;
                                } else if($targetFeedId == $currentTargetFeedId) {
                                    $result[$checkDataItem->sourceFeedId] = $checkDataItem->sourceFeedId;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            self::$sourceFeedIds[$currentTargetFeedId] = $result;
            return $result;
        }

        public static function HasAccessToTargetFeedId($targetFeedId) {
            $accessIds = self::GetTargetFeedIds();
            return empty($accessIds) || array_key_exists($targetFeedId, $accessIds);
        }

        public static function HasAccessToSourceFeedId($sourceFeedId) {
            $accessIds = self::GetSourceFeedIds();
            return empty($accessIds) || array_key_exists($sourceFeedId, $accessIds);
        }
    }
?><?php
    /**
     * Navigation Utility
     */
    class NavigationUtility {
        /**
         * Main Menu
         */
        const MainMenu = 'main-menu';

        /**
         * Footer Menu
         */
        const FooterMenu = 'footer-menu';


        /**
         * Get Navigations by alias
         * @param array  $navigations   source array
         * @param string $alias         navigation type alias
         * @return array
         */
        public static function GetByAlias( $navigations, $alias ) {
            $result = array();
            foreach( $navigations as $navigation ) {
                if ( $navigation->navigationType->alias == $alias ) {
                    $result[$navigation->navigationId] = $navigation;
                }
            }

            return $result;
        }
    }
?><?php 
    class SiteParamHelper {
        const Email   			= "Email";
        const YandexAPI 		= "Yandex.API";
        const YandexMeta 		= "Yandex.Meta";
        const YandexMetrika		= "Yandex.Metrika";
        const GoogleMeta 		= "Google.Meta";
        const GoogleAnalytics 	= "Google.Analytics";
		const GoogleAPI         = "Google.API";
		const Redactors         = "Redactors";

        public static $SiteParamAliases = array(
              self::YandexAPI
            , self::YandexMeta
			, self::YandexMetrika
			, self::GoogleMeta
            , self::GoogleAnalytics
			, self::GoogleAPI
			, self::Redactors
        );

        public static $Params = array ();

        public static function GetCachedParamValue( $alias ) {
            if (!isset (self::$Params[$alias])) {
                $ParamsArray = SiteParamFactory::Get( array(
                        'alias'  => $alias
                    )
                );
                if (count($ParamsArray) == 0) return false;
                foreach ($ParamsArray as self::$Params[$alias] ) break;
            }
            return self::$Params[$alias]->value;
        }
    }
?><?php
    /**
     * UrlParser
     * @package    SPS
     * @subpackage VK
     * @author     Shuler
     */
    class UrlParser {
        public static function Parse($url) {
            $result = array();

            $html = self::GetUrlContent($url);
            if (empty($html)) {
                return $result;
            }

            $document = phpQuery::newDocument($html);

            $title = $document->find('title')->html();
            $description = $document->find("meta[name='description']")->attr('content');
            $img = $document->find("link[rel='image_src']")->attr('href');
            $imgOriginal = $document->find("#original_image_src")->attr('value');

            $title = trim($title);
            $description = trim($description);

            $result['title'] = !empty($title) ? $title : $url;
            if (!empty($description)) $result['description'] = $description;

            if (!empty($img)) {
                $result['img'] = $img;
                $result['imgOriginal'] = !empty($imgOriginal) ? $imgOriginal : $img;
            }

            return $result;
        }

        public static function IsContentWithLink($content) {
            if (preg_match('%([a-zA-Z0-9-.]+\.(?:ru|com|net|me|edu|org|info|biz|uk|ua))([a-zA-Z0-9-_?/#,&;]+)?%uim', $content)) {
                return true;
            } else {
                return false;
            }
        }

        public static function IsContentWithHash($content) {
            if (preg_match('/(^|\s)#(\w+)/uim', $content)) {
                return true;
            } else {
                return false;
            }
        }

        public static function getUrlContent($url) {
            $hnd = curl_init($url);
            curl_setopt($hnd, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($hnd, CURLOPT_FOLLOWLOCATION, true);
            $result = curl_exec($hnd);
            if (curl_errno($hnd)) return false;
            return $result;
        }
    }
?>