<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * Article
     *
     * @package SPS
     * @subpackage Articles
     */
    class Article {

        /** @var int */
        public $articleId;

        /** @var DateTimeWrapper */
        public $importedAt;

        /** @var DateTimeWrapper */
        public $createdAt;

        /** @var string */
        public $externalId;

        /** @var int */
        public $rate;

        /** @var int */
        public $sourceFeedId;

        /** @var SourceFeed */
        public $sourceFeed;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?>