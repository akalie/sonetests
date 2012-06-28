<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetSourceFeedsListControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetSourceFeedsListControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $targetFeedId = Request::getInteger( 'targetFeedId' );

            $type = Request::getString( 'type' );
            if (empty($type) || empty(SourceFeedUtility::$Types[$type])) {
                $type = SourceFeedUtility::Source;
            }

            $sourceFeeds = SourceFeedFactory::Get(
                array('_sourceFeedId' => AccessUtility::GetSourceFeedIds($targetFeedId), 'type' => $type)
                , array( BaseFactory::WithoutPages => true )
            );

            Session::setString('currentSourceType', $type);

            echo ObjectHelper::ToJSON(array_values($sourceFeeds));
        }
    }

?>