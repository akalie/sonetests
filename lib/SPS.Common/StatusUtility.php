<?php
    /**
     * Status Utility
     *
     */
    class StatusUtility {

        const Queued = 4;
        const Finished = 5;

        /**
         * Common Statuses
         *
         * @var array
         */
        public static $Common = array(
            "en" => array(
                1   => "Enabled"
                , 2 => "Disabled"
            )
            , "ru" => array(
                1   => "Опубликован"
                , 2 => "Не опубликован"
			)
        );

        /**
         * Common Statuses
         *
         * @var array
         */
        public static $Queue = array(
            "en" => array(
                1   => "Waiting"
                , 4 => "Queued"
                , 5 => "Finished"
            )
            , "ru" => array(
                1   => "В ожидании"
                , 4 => "Обрабатывается"
                , 5 => "Отправлена"
            )
        );
        
        /**
         * Get Status Template
         *
         * @param int $statusId
         * @return string
         */
        public static function GetStatusTemplate( $statusId ) {
            $status = self::$Common[LocaleLoader::$CurrentLanguage][$statusId];

            switch ($statusId) {
            	case 1:
            	    return sprintf( '<span class="status green" title="%s">%s</span>', $status, $status);
                case 5:
            	    return sprintf( '<span class="status blue" title="%s">%s</span>', $status, $status);
            	default:
            	    return sprintf( '<span class="status" title="%s">%s</span>', $status, $status);
            }
        }

        /**
         * Get Queue Status Template
         *
         * @param int $statusId
         * @return string
         */
        public static function GetQueueStatusTemplate( $statusId ) {
            $status = self::$Queue[LocaleLoader::$CurrentLanguage][$statusId];

            switch ($statusId) {
                case 1:
                    return sprintf( '<span class="status" title="%s">%s</span>', $status, $status);
                case 4:
                    return sprintf( '<span class="status red" title="%s">%s</span>', $status, $status);
                case 5:
                    return sprintf( '<span class="status green" title="%s">%s</span>', $status, $status);
                default:
                    return sprintf( '<span class="status" title="%s">%s</span>', $status, $status);
            }
        }

        /**
         * Get Bool Template
         *
         * @param $bool bool  The bool Value
         * @return string
         */
        public static function GetBoolTemplate( $bool = false ) {
            if ( $bool ) {
                return sprintf( '<span class="status green" title="%s">%s</span>', "Да", "Да");
            } else {
                return sprintf( '<span class="status" title="%s">%s</span>', "Нет", "Нет");
            }
        }        
    }
?>