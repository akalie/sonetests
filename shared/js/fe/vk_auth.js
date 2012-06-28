window.vkAsyncInit = function() {
    VK.init({
        apiId: vk_appId,
        nameTransportPath: '/xd_receiver.htm'
    });

    VK.UI.button('vk_login');
};

(function() {
    var el = document.createElement("script");
    el.type = "text/javascript";
    el.charset = "windows-1251";
    el.src = "http://vkontakte.ru/js/api/openapi.js";
    el.async = true;
    document.getElementById("vk_api_transport").appendChild(el);
}());

function VK_doLogin() {
    VK.Auth.login(
        function(responce) {
            if (responce.session) {
                VK_getInitData();
            }
        }
    );
}

function VK_getInitData() {
    var code;
    var fields = 'activity,nickname,sex,bdate,city,country,timezone,photo,photo_medium,photo_big,online';
    code = 'return {';
    code += 'me: API.getProfiles({uids: API.getVariable({key: 1280}), fields: "'+ fields +'"})[0]';
    code += '};';

    VK.Api.call('execute', {'code': code}, VK_onGetInitData);
}

function VK_onGetInitData(data) {

    options = {};
    options.domain = '.' + hostname;
    options.path = '/';

    if (data.response) {
        r = data.response;

        if (r.me && r.me.uid != "") {
            $.cookie('uid',      r.me.uid    ,options);
            $.cookie('first_name',r.me.first_name ,options);
            $.cookie('last_name', r.me.last_name ,options);
            $.cookie('nickname', r.me.nickname ,options);
            $.cookie('sex',      r.me.sex      ,options);
            $.cookie('bdate',    r.me.bdate    ,options);
            $.cookie('city',     r.me.city     ,options);
            $.cookie('country',  r.me.country  ,options);
            $.cookie('timezone', r.me.timezone ,options);
            $.cookie('photo',    r.me.photo    ,options );
            $.cookie('photo_medium', r.me.photo_medium ,options);
            $.cookie('photo_big',r.me.photo_big,options);

            window.location = '/';
        } else {
            VK_getInitData();
        }
    }
}