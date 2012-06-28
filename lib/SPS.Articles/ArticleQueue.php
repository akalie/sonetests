<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * ArticleQueue
     *
     * @package SPS
     * @subpackage Articles
     */
    class ArticleQueue {

        /** @var int */
        public $articleQueueId;

        /** @var DateTimeWrapper */
        public $startDate;

        /** @var DateTimeWrapper */
        public $endDate;

        /** @var DateTimeWrapper */
        public $createdAt;

        /** @var DateTimeWrapper */
        public $sentAt;

        /** @var int */
        public $articleId;

        /** @var Article */
        public $article;

        /** @var int */
        public $targetFeedId;

        /** @var TargetFeed */
        public $targetFeed;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?>