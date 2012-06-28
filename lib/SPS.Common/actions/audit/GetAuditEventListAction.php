<?php
    /**
     * Get AuditEvent List Action
     * 
     * @package SPS
     * @subpackage Common
     * @property AuditEvent[] list
     */
    class GetAuditEventListAction extends BaseGetAction {

        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => false
            );

            parent::$factory = new AuditEventFactory();
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {
            $auditEventTypes = AuditEventTypeFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            Response::setArray( "auditEventTypes", $auditEventTypes );
        }
    }
?>