<?php
    Package::Load( 'SPS.VK' );

    /**
     * VKCheckAuth Action
     * @package    SPS
     * @subpackage VK
     * @author     Shuler
     */
    class VKCheckAuth {

        public function Execute() {
            $vk_auth = AuthVkontakte::IsAuth();
            if ($vk_auth === false) return 'login';
        }
    }
?>