<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * TargetFeedGrid
     *
     * @package SPS
     * @subpackage Articles
     */
    class TargetFeedGrid {

        /** @var int */
        public $targetFeedGridId;

        /** @var DateTimeWrapper */
        public $startDate;

        /** @var int */
        public $period;

        /** @var int */
        public $targetFeedId;

        /** @var TargetFeed */
        public $targetFeed;
    }
?>