<?php
    /**
     * Get Navigations
     */
    class GetNavigations {

        /**
         * Navigations
         * @var array
         */
        private $navigations = array();


        /**
         * Get Header Menu
         * @return array
         */
        private function getMenu() {
            $result = NavigationUtility::GetByAlias($this->navigations, NavigationUtility::MainMenu);

            return $result;
        }

        /**
         * Execute
         */
        public function Execute() {
            $this->navigations = NavigationFactory::Get(array(),array(BaseFactory::WithoutPages => true));

            Response::setParameter("__menu",  $this->getMenu() );
        }
    }
?>