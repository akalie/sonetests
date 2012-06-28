<?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.Articles' );

    /**
     * ArticleRecord Factory
     *
     * @package SPS
     * @subpackage Articles
     */
    class ArticleRecordFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** ArticleRecord instance mapping  */
        public static $mapping = array (
            'class'       => 'ArticleRecord'
            , 'table'     => 'articleRecords'
            , 'view'      => 'getArticleRecords'
            , 'flags'     => array( 'CanCache' => 'CanCache', 'WithoutTemplates' => 'WithoutTemplates' )
            , 'cacheDeps' => array( 'articles', 'articleQueues' )
            , 'fields'    => array(
                'articleRecordId' => array(
                    'name'          => 'articleRecordId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'content' => array(
                    'name'          => 'content'
                    , 'type'        => TYPE_STRING
                )
                ,'likes' => array(
                    'name'          => 'likes'
                    , 'type'        => TYPE_INTEGER
                )
                ,'link' => array(
                    'name'          => 'link'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 500
                )
                ,'photos' => array(
                    'name'          => 'photos'
                    , 'type'        => TYPE_ARRAY
                    , 'complexType' => 'php'
                )
                ,'rate' => array(
                    'name'          => 'rate'
                    , 'type'        => TYPE_INTEGER
                )
                ,'retweet' => array(
                    'name'          => 'retweet'
                    , 'type'        => TYPE_ARRAY
                    , 'complexType' => 'php'
                )
                ,'video' => array(
                    'name'          => 'video'
                    , 'type'        => TYPE_ARRAY
                    , 'complexType' => 'php'
                )
                ,'music' => array(
                    'name'          => 'music'
                    , 'type'        => TYPE_ARRAY
                    , 'complexType' => 'php'
                )
                ,'map' => array(
                    'name'          => 'map'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 500
                )
                ,'poll' => array(
                    'name'          => 'poll'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 500
                )
                ,'text_links' => array(
                    'name'          => 'text_links'
                    , 'type'        => TYPE_ARRAY
                    , 'complexType' => 'php'
                )
                ,'doc' => array(
                    'name'          => 'doc'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 500
                )
                ,'articleId' => array(
                    'name'          => 'articleId'
                    , 'type'        => TYPE_INTEGER
                    , 'foreignKey'  => 'Article'
                )
                ,'articleQueueId' => array(
                    'name'          => 'articleQueueId'
                    , 'type'        => TYPE_INTEGER
                    , 'foreignKey'  => 'ArticleQueue'
                ))
            , 'lists'     => array()
            , 'search'    => array(
                '_articleId' => array(
                    'name'         => 'articleId'
                    , 'type'       => TYPE_INTEGER
                    , 'searchType' => SEARCHTYPE_ARRAY
                )
                ,'_articleQueueId' => array(
                    'name'         => 'articleQueueId'
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

        /** @return ArticleRecord[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return ArticleRecord */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return ArticleRecord */
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

        /** @return ArticleRecord */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?>