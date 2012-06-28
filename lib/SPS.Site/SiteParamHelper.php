<?php 
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
?>