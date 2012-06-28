<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * Publisher
     *
     * @package SPS
     * @subpackage Articles
     */
    class Publisher {

        /** @var int */
        public $publisherId;

        /** @var string */
        public $name;

        /** @var int */
        public $vk_id;

        /** @var int */
        public $vk_app;

        /** @var string */
        public $vk_token;

        /** @var string */
        public $vk_seckey;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?>