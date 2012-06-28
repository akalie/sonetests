<?
    CssHelper::PushFile('css://fe/login.css');
?>
{increal:tmpl://fe/elements/header.tmpl.php}
    <div id="vk_api_transport"></div>    
    <div id="vk_login" onclick="VK_doLogin()"></div>
    <script src="{web:js://fe/vk_auth.js}" type="text/javascript"></script>
{increal:tmpl://fe/elements/footer.tmpl.php}