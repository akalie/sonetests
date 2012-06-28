<?php
    Package::Load( 'SPS.VK' );

    /**
     * VKLogout Action
     * @package    SPS
     * @subpackage VK
     * @author     Shuler
     */
    class VKLogout {

        public function Execute() {
            AuthVkontakte::Logout();
            return 'success';
        }
    }
?>