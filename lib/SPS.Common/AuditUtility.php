<?php
    /**
     * AuditUtility
     * @author Shuler
     */
    class AuditUtility {
        public static function CreateEvent( $type, $object, $objectId, $message = '' ) {
            $eventType = AuditEventTypeFactory::GetOne( array( 'alias' => $type ) );
            if( empty( $eventType ) ) {
                return;
            }

            $event = new AuditEvent();
            $event->object = $object;
            $event->objectId = $objectId;
            $event->auditEventTypeId = $eventType->auditEventTypeId;
            $event->message = $message;
            $event->createdAt = DateTimeWrapper::Now();

            AuditEventFactory::Add( $event );
        }
    }
?>