<?php
    class AuthVkontakte {

        public static $AppId;

        private static $password;

        public static function Init( DOMNodeList $params ) {
            foreach ( $params as $param ) {
                $name   = $param->getAttribute( 'name' );
                $value  = $param->nodeValue;

                if ($name == 'appId') self::$AppId = $value;
                if ($name == 'password') self::$password = $value;
            }
        }

        /**
         * Проверяет, залогинен пользователь. Если да - возвращает его ID ВКонтакте, в противном случае - false.
         * @return mixed
         */
        public static function IsAuth() {
            $vk_cookie = Cookie::getString('vk_app_trust' . self::$AppId);
            if (empty($vk_cookie)) {
                if (!isset($_COOKIE['vk_app_' . self::$AppId]))
                    return false;

                $vk_cookie = $_COOKIE['vk_app_' . self::$AppId];
            }

            if (!empty($vk_cookie)) {
                $cookie_data = array();

                foreach (explode('&', $vk_cookie) as $item) {
                    $item_data = explode('=', $item);
                    $cookie_data[$item_data[0]] = $item_data[1];
                }

                // Проверяем sig
                $string = sprintf("expire=%smid=%ssecret=%ssid=%s%s", $cookie_data['expire'], $cookie_data['mid'], $cookie_data['secret'], $cookie_data['sid'], self::$password);

                if (md5($string) == $cookie_data['sig']) {
                    // sig не подделан - возвращаем ID пользователя ВКонтакте.
                    // авторизуем пользователя совсем надолго
                    $cookie_data['expire'] = time() + 86400 * 7;
                    $cookie_data['sig'] = md5(sprintf("expire=%smid=%ssecret=%ssid=%s%s", $cookie_data['expire'], $cookie_data['mid'], $cookie_data['secret'], $cookie_data['sid'], self::$password));

                    $newCookie = '';
                    foreach ($cookie_data as $key => $value) {
                        $newCookie .= "&$key=$value";
                    }
                    $newCookie = trim($newCookie, '&');

                    Cookie::setCookie('vk_app_trust' . self::$AppId, $newCookie, $cookie_data['expire'], '/');

                    return $cookie_data['mid'];
                }
            }

            return false;
        }

        /**
         * Производит разлогинивание
         */
        public static function Logout() {
            // Заменяем куку от ВКонтакте на пустую
            setcookie('vk_app_' . self::$AppId, '', 0, "/", '.'.$_SERVER['HTTP_HOST']);
        }
    }
?>