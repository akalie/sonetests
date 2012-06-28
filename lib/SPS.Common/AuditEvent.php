<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * AuditEvent
     *
     * @package SPS
     * @subpackage Common
     */
    class AuditEvent {

        /** @var int */
        public $auditEventId;

        /** @var string */
        public $object;

        /** @var string */
        public $objectId;

        /** @var string */
        public $message;

        /** @var DateTimeWrapper */
        public $createdAt;

        /** @var int */
        public $auditEventTypeId;

        /** @var AuditEventType */
        public $auditEventType;
    }
?>