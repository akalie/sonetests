<?php
    $__pageTitle = LocaleLoader::Translate( "vt.login.enter" );
    $__noMenu    = true;

    $cssFilesAdds = array(
        AssetHelper::AnyBrowser => array(
            'css://vt/login.css'
        )
    );
?>
<? include( Template::GetCachedRealPath("tmpl://vt/header.tmpl.php") );?>
<div class="main">
    <div class="inner">
        <form method="post" action="<?= Site::GetWebPath("vt://login") ;?>" class="login-form">
            <input type="hidden" value="1" name="loginForm" />
            <div class="rows">
                <div class="row required <?= ( empty( $login ) && !empty( $error ) ) ? 'error' : '' ?>">
                    <label><?= LocaleLoader::Translate("vt.login.user") ;?></label>
                    <input type="text" class="text" name="login" value="<?= FormHelper::RenderToForm( $login ); ?>" />
                </div>
                <div class="row required <?= !empty( $error ) ? 'error' : '' ?>">
                    <label><?= LocaleLoader::Translate("vt.login.password") ;?></label>
                    <input type="password" class="text" name="password" value="" />
                    <? if ( ! empty( $error ) ) { ?>
                        <p class="error"><?= LocaleLoader::Translate($error) ; ?></p>
                    <? } ?>
                </div>
                <div class="buttons">
                    <div class="buttons-inner">
                        <input type="submit" class="" value="<?= LocaleLoader::Translate("vt.login.enter") ;?>" />
                    </div>
                </div>
            </div>
        </form>
	</div>
</div>
<? include( Template::GetCachedRealPath("tmpl://vt/footer.tmpl.php") );?>
