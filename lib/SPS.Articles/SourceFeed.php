<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * SourceFeed
     *
     * @package SPS
     * @subpackage Articles
     */
    class SourceFeed {

        /** @var int */
        public $sourceFeedId;

        /** @var string */
        public $title;

        /** @var string */
        public $externalId;

        /** @var bool */
        public $useFullExport;

        /** @var string */
        public $processed;

        /** @var string */
        public $targetFeedIds;

        /** @var string */
        public $type;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?>