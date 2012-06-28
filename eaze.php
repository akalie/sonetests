<?php
    /* Don't Forget to turn on mod_rewrite!  */

    define( 'WITH_PACKAGE_COMPILE', true  );

    if ( !WITH_PACKAGE_COMPILE ) {
        include_once 'lib/Eaze.Core/Logger.php';
        include_once 'lib/Base.Tree/ITreeFactory.php';
        include_once 'lib/Base.Tree/TreeFactory.php';
    }

    include_once 'lib/Eaze.Core/Package.php' ;

    Package::Load( 'Eaze.Core');

    Package::Load( 'Eaze.Site');
    Package::Load( 'Eaze.Modules');
    Package::Load( 'Eaze.Model' );
    Package::Load( 'Eaze.Database/PgSql' );

    Package::Load( 'Base.Tree' );
    Package::Load( 'Base.VFS' );

    Package::Load( 'SPS.Common' );
    Package::Load( 'SPS.System' );
    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.VK' );
    Package::Load( 'SPS.FB' );
    Package::Load( 'SPS.Site' );

    // Initialize Logger
    Logger::Init( ELOG_DEBUG  );
    Logger::Init( ELOG_WARNING );

    mb_internal_encoding( 'utf-8' );
    mb_http_output( 'utf-8' );

    BaseTreeFactory::SetCurrentMode( TREEMODE_ADJ );

    if ( defined( 'WITH_PACKAGE_COMPILE' ) && WITH_PACKAGE_COMPILE ) Logger::Info( 'With package compiled' );

    Request::Init();
    //if ( Request::getRemoteIp() == '127.0.0.1' ) {
        $__level = Request::getParameter( '__level' );
        if ( !is_null( $__level ) ) {
            Logger::LogLevel( $__level );
        }
    //}
    SiteManager::DetectSite();

    Logger::Info( __METHOD__, 'Done' );
?>