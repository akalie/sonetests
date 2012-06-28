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
?><?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.Articles' );

    /**
     * Article Factory
     *
     * @package SPS
     * @subpackage Articles
     */
    class ArticleFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** Article instance mapping  */
        public static $mapping = array (
            'class'       => 'Article'
            , 'table'     => 'articles'
            , 'view'      => 'getArticles'
            , 'flags'     => array( 'CanPages' => 'CanPages', 'CanCache' => 'CanCache' )
            , 'cacheDeps' => array( 'sourceFeeds' )
            , 'fields'    => array(
                'articleId' => array(
                    'name'          => 'articleId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'importedAt' => array(
                    'name'          => 'importedAt'
                    , 'type'        => TYPE_DATETIME
                    , 'nullable'    => 'No'
                )
                ,'createdAt' => array(
                    'name'          => 'createdAt'
                    , 'type'        => TYPE_DATETIME
                )
                ,'externalId' => array(
                    'name'          => 'externalId'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 100
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'rate' => array(
                    'name'          => 'rate'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'No'
                )
                ,'sourceFeedId' => array(
                    'name'          => 'sourceFeedId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'SourceFeed'
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                ))
            , 'lists'     => array()
            , 'search'    => array(
                '_externalId' => array(
                    'name'         => 'externalId'
                    , 'type'       => TYPE_STRING
                    , 'searchType' => SEARCHTYPE_ARRAY
                )
                ,'_sourceFeedId' => array(
                    'name'         => 'sourceFeedId'
                    , 'type'       => TYPE_INTEGER
                    , 'searchType' => SEARCHTYPE_ARRAY
                )
                ,'rateGE' => array(
                    'name'         => 'rate'
                    , 'type'       => TYPE_INTEGER
                    , 'searchType' => SEARCHTYPE_GE
                )
                ,'rateLE' => array(
                    'name'         => 'rate'
                    , 'type'       => TYPE_INTEGER
                    , 'searchType' => SEARCHTYPE_LE
                )
                ,'page' => array(
                    'name'         => 'page'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 0
                )
                ,'pageSize' => array(
                    'name'         => 'pageSize'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 25
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

        /** @return Article[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return Article */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return Article */
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

        /** @return Article */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
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
?><?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.Articles' );

    /**
     * ArticleQueue Factory
     *
     * @package SPS
     * @subpackage Articles
     */
    class ArticleQueueFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** ArticleQueue instance mapping  */
        public static $mapping = array (
            'class'       => 'ArticleQueue'
            , 'table'     => 'articleQueues'
            , 'view'      => 'getArticleQueues'
            , 'flags'     => array( 'CanPages' => 'CanPages', 'CanCache' => 'CanCache' )
            , 'cacheDeps' => array( 'articles', 'targetFeeds' )
            , 'fields'    => array(
                'articleQueueId' => array(
                    'name'          => 'articleQueueId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'startDate' => array(
                    'name'          => 'startDate'
                    , 'type'        => TYPE_DATETIME
                    , 'nullable'    => 'No'
                )
                ,'endDate' => array(
                    'name'          => 'endDate'
                    , 'type'        => TYPE_DATETIME
                    , 'nullable'    => 'No'
                )
                ,'createdAt' => array(
                    'name'          => 'createdAt'
                    , 'type'        => TYPE_DATETIME
                    , 'nullable'    => 'No'
                )
                ,'sentAt' => array(
                    'name'          => 'sentAt'
                    , 'type'        => TYPE_DATETIME
                )
                ,'articleId' => array(
                    'name'          => 'articleId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Article'
                )
                ,'targetFeedId' => array(
                    'name'          => 'targetFeedId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'TargetFeed'
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                ))
            , 'lists'     => array()
            , 'search'    => array(
                'startDateAsDate' => array(
                    'name'         => 'startDate'
                    , 'type'       => TYPE_DATE
                )
                ,'page' => array(
                    'name'         => 'page'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 0
                )
                ,'pageSize' => array(
                    'name'         => 'pageSize'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 25
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

        /** @return ArticleQueue[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return ArticleQueue */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return ArticleQueue */
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

        /** @return ArticleQueue */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * ArticleRecord
     *
     * @package SPS
     * @subpackage Articles
     */
    class ArticleRecord {

        /** @var int */
        public $articleRecordId;

        /** @var string */
        public $content;

        /** @var int */
        public $likes;

        /** @var string */
        public $link;

        /** @var array */
        public $photos;

        /** @var int */
        public $rate;

        /** @var array */
        public $retweet;

        /** @var array */
        public $video;

        /** @var array */
        public $music;

        /** @var string */
        public $map;

        /** @var string */
        public $poll;

        /** @var array */
        public $text_links;

        /** @var string */
        public $doc;

        /** @var int */
        public $articleId;

        /** @var Article */
        public $article;

        /** @var int */
        public $articleQueueId;

        /** @var ArticleQueue */
        public $articleQueue;
    }
?><?php
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
?><?php
    /**
     * ArticleUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class ArticleUtility {

        public static function IsTopArticleWithSmallPhoto(SourceFeed $sourceFeed, ArticleRecord $articleRecord) {
            if (!empty($articleRecord->photos) && count($articleRecord->photos) == 1 && SourceFeedUtility::IsTopFeed($sourceFeed)) {
                $photoItem = current($articleRecord->photos);
                $path = MediaUtility::GetFilePath( 'Article', 'photos', 'original', $photoItem['filename'], MediaServerManager::$MainLocation);
                $dimensions = ImageHelper::GetImageSizes($path);

                if ($dimensions['width'] < 250 && $dimensions['height'] < 250) {
                    return true;
                }
            }

            return false;
        }
    }
?><?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * Publisher
     *
     * @package SPS
     * @subpackage Articles
     */
    class Publisher {

        /** @var int */
        public $publisherId;

        /** @var string */
        public $name;

        /** @var int */
        public $vk_id;

        /** @var int */
        public $vk_app;

        /** @var string */
        public $vk_token;

        /** @var string */
        public $vk_seckey;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?><?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.Articles' );

    /**
     * Publisher Factory
     *
     * @package SPS
     * @subpackage Articles
     */
    class PublisherFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** Publisher instance mapping  */
        public static $mapping = array (
            'class'       => 'Publisher'
            , 'table'     => 'publishers'
            , 'view'      => 'getPublishers'
            , 'flags'     => array( 'CanCache' => 'CanCache' )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'publisherId' => array(
                    'name'          => 'publisherId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'name' => array(
                    'name'          => 'name'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 100
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'vk_id' => array(
                    'name'          => 'vk_id'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'No'
                )
                ,'vk_app' => array(
                    'name'          => 'vk_app'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'No'
                )
                ,'vk_token' => array(
                    'name'          => 'vk_token'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 128
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'vk_seckey' => array(
                    'name'          => 'vk_seckey'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 64
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                ))
            , 'lists'     => array()
            , 'search'    => array()
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

        /** @return Publisher[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return Publisher */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return Publisher */
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

        /** @return Publisher */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * SourceFeed
     *
     * @package SPS
     * @subpackage Articles
     */
    class SourceFeed {

        /** @var int */
        public $sourceFeedId;

        /** @var string */
        public $title;

        /** @var string */
        public $externalId;

        /** @var bool */
        public $useFullExport;

        /** @var string */
        public $processed;

        /** @var string */
        public $targetFeedIds;

        /** @var string */
        public $type;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?><?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.Articles' );

    /**
     * SourceFeed Factory
     *
     * @package SPS
     * @subpackage Articles
     */
    class SourceFeedFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** SourceFeed instance mapping  */
        public static $mapping = array (
            'class'       => 'SourceFeed'
            , 'table'     => 'sourceFeeds'
            , 'view'      => 'getSourceFeeds'
            , 'flags'     => array( 'CanCache' => 'CanCache' )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'sourceFeedId' => array(
                    'name'          => 'sourceFeedId'
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
                ,'useFullExport' => array(
                    'name'          => 'useFullExport'
                    , 'type'        => TYPE_BOOLEAN
                    , 'nullable'    => 'No'
                )
                ,'processed' => array(
                    'name'          => 'processed'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 100
                )
                ,'targetFeedIds' => array(
                    'name'          => 'targetFeedIds'
                    , 'type'        => TYPE_STRING
                )
                ,'type' => array(
                    'name'          => 'type'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 100
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                ))
            , 'lists'     => array()
            , 'search'    => array(
                '_sourceFeedId' => array(
                    'name'         => 'sourceFeedId'
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

        /** @return SourceFeed[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return SourceFeed */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return SourceFeed */
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

        /** @return SourceFeed */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
    /**
     * SourceFeedUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class SourceFeedUtility {
        const TOP_FEMALE = 'top-female';
        const TOP_MALE = 'top-male';

        public static $Tops = array(self::TOP_FEMALE, self::TOP_MALE);

        const Source = 'source';

        const Ads = 'ads';

        public static $Types = array(
            self::Source => 'Источник',
            self::Ads => 'Рекламная лента',
        );

        public static function IsTopFeed(SourceFeed $sourceFeed) {
            return in_array($sourceFeed->externalId, self::$Tops);
        }

        public static function GetInfo($sourceFeeds) {
            $sourceInfo = array();

            foreach ($sourceFeeds as $sourceFeed) {
                $sourceInfo[$sourceFeed->sourceFeedId] = array(
                    'name' => $sourceFeed->title,
                    'img' => ''
                );

                //group image
                $path = 'temp://userpic-' . $sourceFeed->externalId . '.jpg';
                $filePath = Site::GetRealPath($path);
                if (!file_exists($filePath)) {
                    $avatarPath = Site::GetWebPath('images://fe/no-avatar.png');

                    try {
                        $parser = new ParserVkontakte();
                        $info = $parser->get_info(ParserVkontakte::VK_URL . '/public' . $sourceFeed->externalId);

                        if (!empty($info['avatarа'])) {
                            $avatarPath = $info['avatarа'];
                        }
                    } catch (Exception $Ex) {}

                    file_put_contents($filePath, file_get_contents($avatarPath));
                }

                $sourceInfo[$sourceFeed->sourceFeedId]['img'] = Site::GetWebPath($path);
            }

            return $sourceInfo;
        }
    }
?><?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * TargetFeed
     *
     * @package SPS
     * @subpackage Articles
     */
    class TargetFeed {

        /** @var int */
        public $targetFeedId;

        /** @var string */
        public $title;

        /** @var string */
        public $externalId;

        /** @var DateTimeWrapper */
        public $startTime;

        /** @var int */
        public $period;

        /** @var string */
        public $vkIds;

        /** @var string */
        public $type;

        /** @var array */
        public $params;

        /** @var int */
        public $publisherId;

        /** @var Publisher */
        public $publisher;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;

        /** @var TargetFeedGrid[] */
        public $grids;

        /** @var TargetFeedPublisher[] */
        public $publishers;
    }
?><?php
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
?><?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * TargetFeedGrid
     *
     * @package SPS
     * @subpackage Articles
     */
    class TargetFeedGrid {

        /** @var int */
        public $targetFeedGridId;

        /** @var DateTimeWrapper */
        public $startDate;

        /** @var int */
        public $period;

        /** @var int */
        public $targetFeedId;

        /** @var TargetFeed */
        public $targetFeed;
    }
?><?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.Articles' );

    /**
     * TargetFeedGrid Factory
     *
     * @package SPS
     * @subpackage Articles
     */
    class TargetFeedGridFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** TargetFeedGrid instance mapping  */
        public static $mapping = array (
            'class'       => 'TargetFeedGrid'
            , 'table'     => 'targetFeedGrids'
            , 'view'      => 'getTargetFeedGrids'
            , 'flags'     => array( 'CanCache' => 'CanCache', 'WithoutTemplates' => 'WithoutTemplates' )
            , 'cacheDeps' => array( 'targetFeeds' )
            , 'fields'    => array(
                'targetFeedGridId' => array(
                    'name'          => 'targetFeedGridId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'startDate' => array(
                    'name'          => 'startDate'
                    , 'type'        => TYPE_DATETIME
                    , 'nullable'    => 'No'
                )
                ,'period' => array(
                    'name'          => 'period'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'No'
                )
                ,'targetFeedId' => array(
                    'name'          => 'targetFeedId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'TargetFeed'
                ))
            , 'lists'     => array()
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

        /** @return TargetFeedGrid[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return TargetFeedGrid */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return TargetFeedGrid */
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

        /** @return TargetFeedGrid */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * TargetFeedPublisher
     *
     * @package SPS
     * @subpackage Articles
     */
    class TargetFeedPublisher {

        /** @var int */
        public $targetFeedId;

        /** @var TargetFeed */
        public $targetFee;

        /** @var int */
        public $publisherId;

        /** @var Publisher */
        public $publisher;
    }
?><?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.Articles' );

    /**
     * TargetFeedPublisher Factory
     *
     * @package SPS
     * @subpackage Articles
     */
    class TargetFeedPublisherFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** TargetFeedPublisher instance mapping  */
        public static $mapping = array (
            'class'       => 'TargetFeedPublisher'
            , 'table'     => 'targetFeedPublishers'
            , 'view'      => 'getTargetFeedPublishers'
            , 'flags'     => array( 'CanCache' => 'CanCache', 'WithoutTemplates' => 'WithoutTemplates' )
            , 'cacheDeps' => array( 'targetFeeds', 'publishers' )
            , 'fields'    => array(
                'targetFeedId' => array(
                    'name'          => 'targetFeedId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'TargetFeed'
                )
                ,'publisherId' => array(
                    'name'          => 'publisherId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Publisher'
                ))
            , 'lists'     => array()
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

        /** @return TargetFeedPublisher[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return TargetFeedPublisher */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return TargetFeedPublisher */
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

        /** @return TargetFeedPublisher */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?><?php
    /**
     * TargetFeedUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class TargetFeedUtility {
        const VK = 'vk';

        const FB = 'fb';

        public static $Types = array(
            self::VK => 'ВКонтакте',
            self::FB => 'Facebook',
        );
    }

?>