<?
    CssHelper::PushFile('css://fe/login.css');
?>
<? include( Template::GetCachedRealPath("tmpl://fe/elements/header.tmpl.php") );?>
    <div id="vk_api_transport"></div>    
    <div id="vk_login" onclick="VK_doLogin()"></div>
    <script src="<?= Site::GetWebPath("js://fe/vk_auth.js") ;?>" type="text/javascript"></script>
<? include( Template::GetCachedRealPath("tmpl://fe/elements/footer.tmpl.php") );?>