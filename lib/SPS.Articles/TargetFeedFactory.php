<?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.Articles' );

    /**
     * TargetFeed Factory
     *
     * @package SPS
     * @subpackage Articles
     */
    class TargetFeedFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** TargetFeed instance mapping  */
        public static $mapping = array (
            'class'       => 'TargetFeed'
            , 'table'     => 'targetFeeds'
            , 'view'      => 'getTargetFeeds'
            , 'flags'     => array( 'CanCache' => 'CanCache' )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'targetFeedId' => array(
                    'name'          => 'targetFeedId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'title' => array(
                    'name'          => 'title'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 500
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'externalId' => array(
                    'name'          => 'externalId'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 100
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'startTime' => array(
                    'name'          => 'startTime'
                    , 'type'        => TYPE_TIME
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'period' => array(
                    'name'          => 'period'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'vkIds' => array(
                    'name'          => 'vkIds'
                    , 'type'        => TYPE_STRING
                )
                ,'type' => array(
                    'name'          => 'type'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 10
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'params' => array(
                    'name'          => 'params'
                    , 'type'        => TYPE_ARRAY
                    , 'complexType' => 'php'
                )
                ,'publisherId' => array(
                    'name'          => 'publisherId'
                    , 'type'        => TYPE_INTEGER
                    , 'foreignKey'  => 'Publisher'
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                ))
            , 'lists'     => array(
                'grids' => array(
                    'name'         => 'targetFeedId'
                    , 'foreignKey' => 'TargetFeedGrid'
                )
                ,'publishers' => array(
                    'name'         => 'targetFeedId'
                    , 'foreignKey' => 'TargetFeedPublisher'
                ))
            , 'search'    => array(
                '_targetFeedId' => array(
                    'name'         => 'targetFeedId'
                    , 'type'       => TYPE_INTEGER
                    , 'searchType' => SEARCHTYPE_ARRAY
                ))
        );
        
        /** @return array */
        public static function Validate( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Validate( $object, self::$mapping, $options, $connectionName );
        }

        /** @return array */
        public static function ValidateSearch( $search, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::ValidateSearch( $search, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool|array */
        public static function UpdateByMask( $object, $changes, $searchArray = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateByMask( $object, $changes, $searchArray, self::$mapping, $connectionName );
        }

        public static function SaveArray( $objects, $originalObjects = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::SaveArray( $objects, $originalObjects, self::$mapping, $connectionName );
        }

        public static function CanPages() {
            return BaseFactory::CanPages( self::$mapping );
        }        
        
        /** @return bool|array */
        public static function Add( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Add( $object, self::$mapping, $options, $connectionName );
        }
        
        /** @return bool */
        public static function AddRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::AddRange( $objects, self::$mapping, $options, $connectionName );
        }

        /** @return bool|array */
        public static function Update( $object, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Update( $object, self::$mapping, $options, $connectionName );
        }

        /** @return bool */
        public static function UpdateRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::UpdateRange( $objects, self::$mapping, $options, $connectionName );
        }

        public static function Count( $searchArray, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Count( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return TargetFeed[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return TargetFeed */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return TargetFeed */
        public static function GetOne( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetOne( $searchArray, self::$mapping, $options, $connectionName );
        }
        
        public static function GetCurrentId( $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetCurrentId( self::$mapping, $connectionName );
        }

        public static function Delete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Delete( $object, self::$mapping, $connectionName );
        }

        public static function DeleteByMask( $searchArray, $connectionName = self::DefaultConnection ) {
            return BaseFactory::DeleteByMask( $searchArray, self::$mapping, $connectionName );
        }

        public static function PhysicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::PhysicalDelete( $object, self::$mapping, $connectionName );
        }

        public static function LogicalDelete( $object, $connectionName = self::DefaultConnection ) {
            return BaseFactory::LogicalDelete( $object, self::$mapping, $connectionName );
        }

        /** @return TargetFeed */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?>