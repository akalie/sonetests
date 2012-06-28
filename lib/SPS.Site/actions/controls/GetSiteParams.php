<?php 
    class GetSiteParams {
 
        /**
         * Execute GetSiteParams
         */
        public function Execute() {
            $container = Request::getString( "gsp_Container" );
            
            $params = SiteParamFactory::Get( array(), array( BaseFactory::WithoutPages => true ) );
            $params = BaseFactoryPrepare::Collapse( $params, "alias", false );            

            Response::setArray( $container, $params );
        }
    }
?>