var pattern = /\b(https?|ftp):\/\/([\-A-Z0-9.]+)(\/[\-A-Z0-9+&@#\/%=~_|!:,.;]*)?(\?[A-Z0-9+&@#\/%=~_|!:,.;]*)?/im;

$(document).ready(function(){
    $("#calendar")
        .datepicker (
            {
                dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
                dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                dayNamesShort: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                monthNames: ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'],
                monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
                firstDay: 1,
                showAnim: '',
                dateFormat: "dd.mm.yy"
            }
        )
        .keydown(function(e){
            if(!(e.keyCode >= 112 && e.keyCode <= 123 || e.keyCode < 32)) e.preventDefault();
        })
        .change(function(){
            $(this).parent().find(".caption").toggleClass("default", !$(this).val().length);
            Events.fire('calendar_change', [])
        })
        .trigger('change');

    $(".calendar .tip").click(function(){
        $(this).closest(".calendar").find("input").focus();
    });

    $("#source-select").multiselect({
        height: 250,
        noneSelectedText: 'Источник',
        checkAll: function(){
            Events.fire('leftcolumn_dropdown_change', []);
        },
        uncheckAll: function(){
            Events.fire('leftcolumn_dropdown_change', []);
        }
    });
    $("#source-select").bind("multiselectclick", function(event, ui){
        Events.fire('leftcolumn_dropdown_change', []);
    });

    $(".drop-down").click(function(e){
        e.stopPropagation();
        $(document).click();
        var elem = $(this);
        var hidethis = function(){
            elem.removeClass("expanded");
            $(document).unbind("click", hidethis);
            elem.find("li").unbind("click", click_li);
        };
        var click_li = function(e){
            e.stopPropagation();
            elem.dd_sel($(this).data("id"));
            hidethis();
        };
        $(document).bind("click", hidethis);
        elem.find("li").click(click_li);
        elem.addClass("expanded");
    });

    $(".left-panel .drop-down").change(function(){
        Events.fire('leftcolumn_dropdown_change', []);
    });
    $(".right-panel .drop-down").change(function(){
        Events.fire('rightcolumn_dropdown_change', []);
    });
    $(".type-selector a").click(function(e){
        e.preventDefault();

        $(".type-selector a").removeClass('active');
        $(this).addClass('active');

        if ($(this).data('type') == 'ads') {
            $('#slider-text').hide();
            $('#slider-cont').hide();
        } else {
            $('#slider-text').show();
            $('#slider-cont').show();
        }

        Events.fire('rightcolumn_dropdown_change', []);
    });

    $(".wall")
        .delegate(".post .delete", "click", function(){
            var elem = $(this).closest(".post"),
                pid = elem.data("id");
            Events.fire('leftcolumn_deletepost', [pid, function(state){
                if (state) {
                    var deleteMessageId = 'deleted-post-' + pid;
                    if ($('#' + deleteMessageId).length) {
                        // если уже удаляли пост, то сообщение об удалении уже в DOMе
                        $('#' + deleteMessageId).show();
                    } else {
                        // иначе добавляем
                        elem.before($('<div id="' + deleteMessageId + '" class="post deleted-post" data-id="' + pid + '">Пост удален. <a href="javascript:;" class="recover">Восстановить.</a></div>'));
                    }

                    elem.hide();
                }
            }]);
        })
        .delegate('.post .recover', 'click', function() {
            var elem = $(this).closest(".post"),
                pid = elem.data("id");
            Events.fire('leftcolumn_recoverpost', [pid, function(state){
                if(state) {
                    elem.hide().next().show();
                }
            }]);
        });

    $(".items").delegate(".slot .post .delete", "click", function(){
        var elem = $(this).closest(".post"),
            pid = elem.data("id");
        Events.fire('rightcolumn_deletepost', [pid, function(state){
            if(state) {
                elem.closest(".slot").addClass('empty');
                elem.closest(".slot").find('span.attach-icon').remove();
                elem.closest(".slot").find('span.hash-span').remove();
                elem.remove();
            }
        }]);
    });

    $("#wallloadmore").click(function(){
        var b = $(this);
        if(b.hasClass("disabled")) { return; }
        b.addClass("disabled");
        Events.fire('wall_load_more', function(state){
            b.removeClass("disabled");
            if(!state) {
                b.addClass("disabled");
            }
        });
    });

    $(".left-panel").delegate(".clear-text", "click", function(){
        var id = $(this).closest(".post").data("id");
        var post = $(this).closest(".post");

        if (confirm("Вы уверены, что хотите очистить текст записи?") ) {
            Events.fire('leftcolumn_clear_post_text', [id, function(state){
                if(state) {
                    post.find('div.shortcut').html('');
                    post.find('div.cut').html('');
                    post.find('a.show-cut').remove();
                }
            }]);
        }
    });

    //init first source and target
    var currentTarget = $(".right-panel .drop-down ul li.active");
    if (currentTarget.length == 0) {
        currentTarget = $(".right-panel .drop-down ul :first-child");
    }

    if (currentTarget.length > 0) {
        Elements.rightdd(currentTarget.data("id"));
    }

    (function(){
        var addInput = function(elem, defaultvalue, id){
            var input = $("<input/>");
            elem.append(input);
            input.click(function(e){e.stopPropagation();});
            input.focus();
            input.blur(function(){
                $(this).remove();
            });
            input.keydown(function(e){
                if(e.keyCode == 27) {
                    $(this).remove();
                }
                if(e.keyCode == 13) {
                    var eventname,
                        column;
                    args = [$(this).val()];
                    column = (elem.closest(".right-panel").length) ? "right" : "left";
                    if(id) {
                        args.push(id);
                        eventname = column + "column_source_edited";
                    } else {
                        eventname = column + "column_source_added"
                    }
                    args.push(function(state){
                        if(!state) return;
                        if(id) {
                            elem.find("li[data-id=" + id + "]").text(state.value);
                        } else {
                            elem.find("ul").append('<li data-id="' + state.id + '">' + state.value + '</li>');
                        }
                        elem.dd_sel(state.id || id);
                    });
                    Events.fire(eventname, args);
                    $(this).remove();
                }
            });
            if(defaultvalue) input.val(defaultvalue);
            return input;
        };
        var getDD = function(elem){
            return $(elem).closest(".header").find(".drop-down");
        };
        $(".controls .del").click(function(){
            var dd = getDD(this),
                val = dd.data("selected");
            if(!val) {return}
            var column = (dd.closest(".right-panel").length) ? "right" : "left";
            Events.fire(column + "column_source_deleted", [val, function(state){
                if(!state) { return; }
                dd.find("li[data-id=" + val + "]").remove();
                dd.dd_sel(0);
            }]);
        });
        $(".controls .gear").click(function(){
            var dd = getDD(this);
            if(!dd.data("selected")) {return}
            addInput(dd,dd.find(".caption").text(),dd.data("selected"));
        });
        $(".controls .plus").click(function(){
            addInput(getDD(this));
        });
    })();

    // Автоподгрузка записей
    (function(){
        var w = $(window),
            b = $("#wallloadmore");
        w.scroll(function(){
            if(w.scrollTop() > (b.offset().top - w.outerHeight(true))) {
                b.click();
            }
        });
    })();

    // Автовысота у textarea
    function autoResize(input) {
        if (!input.autoResize) {
            input.autoResize = $('<div/>')
                .appendTo('body')
                .css({
                    width: input.width(),
                    minHeight: input.height(),
                    padding: input.css('padding'),
                    lineHeight: input.css('line-height'),
                    font: input.css('font'),
                    fontSize: input.css('font-size'),
                    position: 'absolute',
                    wordWrap: 'break-word',
                    top: -10000
                });
        }
        input.autoResize.html(input.val().split('\n').join('<br/>$nbsp;'));
        input.css({
            height: input.autoResize.height() + 15
        });
    }

    // Добавление записи в борд
    (function(){
        var form = $(".newpost"),
            input = $("textarea", form),
            tip = $(".tip", form);

        var $linkInfo = $('.link-info', form),
            $linkDescription = $('.link-description', $linkInfo),
            $linkStatus = $('.link-status', $linkInfo),
            foundLink, foundDomain;

        tip.click(function(){input.focus();});
        form.click(function(e){ e.stopPropagation(); });
        input
            .focus(function(){
                form.removeClass("collapsed");
                $(window).bind("click", stop);
            })
            .bind('paste', function() {
                setTimeout(function() {
                    parseUrl(input.val());
                }, 10);
            })
            .keyup(function (e) {
                if (e.ctrlKey && e.keyCode == 13) {
                    form.find('.save').click();
                }
                autoResize(input);
            }).keyup()
        ;

        var parseUrl = function(txt){
            var matches = txt.match(pattern);

            // если приаттачили ссылку
            if (matches && matches[0] && matches[1] && !foundLink) {
                foundLink   = matches[0];
                foundDomain = matches[2];

                Events.fire("post_describe_link", [
                    foundLink,
                    function(result) {
                        if (result) {
                            $linkDescription.empty();
                            $linkStatus.empty();

                            var $descriptionLayout = $('<div></div>',{'class':'post_describe_layout'});
                            $linkDescription.append($descriptionLayout);

                            // отрисовываем ссылку
                            if (result.img) {
                                var $imgBlock = $('<div></div>',{'class':'post_describe_image','title':'Редактировать картинку'}).css(
                                    {
                                        'background-image' : 'url('+result.img+')'
                                    }
                                );

                                $linkDescription.prepend($imgBlock);
                            }
                            if (result.title) {
                                var $a = $('<a />', {
                                    href: foundLink,
                                    target: '_blank',
                                    html: '<span>'+result.title+'</span>',
                                    title:'Редактировать заголовок'
                                });
                                var $h = $('<div></div>',{'class':'post_describe_header'});
                                $h.append($a);
                                $descriptionLayout.append($h);
                            }
                            if (result.description) {
                                var $p = $('<p />', {
                                    html: '<span>'+result.description+'</span>',
                                    title:'Редактировать описание'
                                });
                                $descriptionLayout.append($p);
                            }

                            editPostDescribeLink.load($h,$p,$imgBlock,result.imgOriginal);

                            var $span = $('<span />', { text: 'Ссылка: ' });
                            $span.append($('<a />', { href: foundLink, target: '_blank', text: foundDomain }));

                            var $deleteLink = $('<a />', { href: 'javascript:;', 'class': 'delete-link', text: 'удалить' }).click(function() {
                                // убираем аттач ссылки
                                deleteLink();
                            });
                            var $reloadLink = $('<a />', { href: 'javascript:;', 'class': 'reload-link', text: 'обновить', 'css' : {'display': 'none'} }).click(function() {
                                link = foundLink;
                                deleteLink();
                                parseUrl(link);
                            });
                            $span.append($deleteLink);
                            $span.append($reloadLink);

                            $linkStatus.html($span);

                            $linkInfo.show();
                        }
                    }
                ]);
            }
        };

        // Редактирование ссылки
        var editPostDescribeLink = {
            load: function ($header,$description,$image,$imageSrc) {
                this.header = $header;
                this.description = $description;
                this.image = $image;
                this.imageSrc = $imageSrc;
                this.renderEditor();
            },
            renderEditor: function() {
                var $editField = $('<input />',{type:'text',id:'post_header'});
                var $editArea = $('<textarea />',{id: 'post_description'});
                if (this.header) {
                    this.header.append($editField.val(this.header.text()));
                }
                if (this.description) {
                    this.description.append($editArea.val(this.description.text()));
                }

                this.bindEvts();
            },
            bindEvts: function() {
                var t = this;
                if (this.header) {
                    this.header.click(function() {
                        t.edit(t.header);
                        return false;
                    });
                }
                if (this.description) {
                    this.description.click(function() {
                        t.edit(t.description);
                        return false;
                    });
                }
                if (this.image) {
                    this.image.click(function() {
                        t.editImage(t.description);
                        return false;
                    });
                }
            },
            editImage: function() {
                this.renderEditImagePopup();
            },
            renderEditImagePopup: function() {
                var $popup = $('<div></div>',{
                    'class': 'editImagePopup',
                    'html': '<h2>Редактировать изображение</h2>'+
                        '<table><tr><td><img src="'+this.imageSrc+'" id="originalImage" /></td>'+
                        '<td><div class="previewContainer">'+
                        '<div class="previewLayout"><img id="preview" src="'+this.imageSrc+'" /></div>'+
                        '<div class="button spr save">Сохранить</div>'+
                        '<div id="attach-image-file" class="buttons attach-file">'+
                        '</div>'+
                        '</div></td></tr></table><b class="close"></b>'
                }),
                    t = this;
                $('body').append($popup);
                $('<div class="substrate"></div>').appendTo('body');
                $('#originalImage').load(function(){
                    $popup.css({
                        left: $('body').width()/2 - $popup.width()/2,
                        top: $('.link-info').position().top
                    });
                    $('.substrate').css({
                        height: $(document).height()
                    });
                });

                $popup.find('.save').click(function() {
                    t.post();
                });


                this.closeImagePopup($popup);
                this.crop();
                this.upload();
            },
            closeImagePopup: function($popup) {
                $('.substrate,.editImagePopup .close').click(function() {
                    $('.substrate').remove();
                    $popup.remove();
                });
            },
            crop: function() {
                var t = this;
                this.originalImage = $('#originalImage');
                this.previewImage = $('#preview');
                this.originalImage.load(function (){
                    t.Jcrop = $.Jcrop($(this), {
                        onChange: t.showPreview,
                        onSelect: t.showPreview,
                        aspectRatio : 2.06,
                        minSize: [130,63],
                        setSelect: [0,0,130,63]
                    });
                });
            },
            upload: function() {
                var t = this;
                try {
                    new qq.FileUploader({
                        debug: true,
                        element: $('#attach-image-file')[0],
                        action: root + 'int/controls/image-upload/',
                        template: ' <div class="qq-uploader">' +
                            '<ul class="qq-upload-list"></ul>' +
                            //'<a href="#" class="button spr qq-upload-button">Загрузить картинку</a>' +
                            '</div>',
                        onComplete: function(id, fileName, responseJSON) {
                            popupNotice('Не реализовано');
//                            $('.jcrop-holder').remove();
//                            t.originalImage.attr({src:responseJSON.image}).show();
//                            t.previewImage.attr({src:responseJSON.image});
//                            t.crop();
                        }
                    });
                } catch (e) {}
            },
            showPreview: function (coords,t) {
                var rx = $('.previewLayout').width() / coords.w;
                var ry = $('.previewLayout').height() / coords.h;

                $('#preview').css({
                    width: Math.round(rx * $('.jcrop-holder').width()) + 'px',
                    height: Math.round(ry * $('.jcrop-holder').height()) + 'px',
                    marginLeft: '-' + Math.round(rx * coords.x) + 'px',
                    marginTop: '-' + Math.round(ry * coords.y) + 'px'
                });
                editPostDescribeLink.coords = coords;
            },
            edit: function($elem) {
                var t = this;
                $elem.find('span').hide();
                $elem.find('input,textarea')
                    .css({display: 'block'})
                    .trigger('focus')
                    .unbind('blur')
                    .bind('blur',function(){
                        var $this = $(this);
                        $elem.find('span').text($this.val()).show();
                        $this.hide();
                        t.post();
                    });
            },
            post: function() {
                var t = this,
                    data = {
                        header: $('#post_header').val(),
                        description: $('#post_description').val(),
                        coords: t.coords,
                        link: $('.post_describe_header').find('a').attr('href')
                    };

                $('.editImagePopup .close').click();

                Events.fire('post_link_data', data, function(state){

                });
            }
        };

        var clearForm = function(){
            input.data("id", 0).val('');
            $('.qq-upload-list').html('');
            deleteLink();
        };

        var stop = function(){
            $(window).unbind("click", stop);

            if(!input.val().length && !$(".qq-upload-list li").length && !$linkInfo.is(":visible")) {
                input.data("id", 0);
                form.addClass("collapsed");
                deleteLink();
            }
        };

        var deleteLink = function(){
            $linkDescription.empty();
            $linkStatus.empty();
            $linkInfo.hide();
            foundLink = false;
            foundDomain = false;
        };

        form.delegate(".save", "click" ,function(e){
            var photos = new Array();
            $('.qq-upload-success').each(function(){
                var photo = new Object();
                photo.filename = $(this).find('input:hidden').val();
                photo.title = $(this).find('textarea').val();
                photos.push(photo);
            });
            form.addClass("spinner");
            Events.fire("post", [
                input.val(),
                photos,
                $linkStatus.find('a').attr('href'),
                input.data("id"),
                function(state){
                    if(state) {
                        clearForm();
                        stop();
                    }
                    form.removeClass("spinner");
                }
            ]);
        });
        form.delegate(".cancel", "click" ,function(e){
            clearForm();
            input.val('').blur();
            form.addClass('collapsed');
            e.preventDefault();
        });

        // Редактирование поста в левом меню
        $(".left-panel").delegate(".post .edit", "click", function(){

            var $post = $(this).closest(".post"),
                $content = $post.find('.content'),
                $buttonPanel = $post.find('.bottom.d-hide'),
                postId = $post.data("id");

            if ($post.editing) return;

            Events.fire('load_post_edit', [postId, function(state, data){
                if (state && data) {

                    (function($post, $el, data) {

                        function setSelectionRange(input, selectionStart, selectionEnd) {
                            if (input.setSelectionRange) {
                                input.focus();
                                input.setSelectionRange(selectionStart, selectionEnd);
                            }
                            else if (input.createTextRange) {
                                var range = input.createTextRange();
                                range.collapse(true);
                                range.moveEnd('character', selectionEnd);
                                range.moveStart('character', selectionStart);
                                range.select();
                            }
                        }
                        function setCaretToPos (input, pos) {
                            setSelectionRange(input, pos, pos);
                        }

                        function parseUrl(txt, callback) {
                            var matches = txt.match(pattern);
                            if (matches && matches[0] && matches[1]) {
                                var foundLink = matches[0];
                                var foundDomain = matches[2];
                                if ($.isFunction(callback)) callback(foundLink, foundDomain);
                            }
                        }
                        function addLink(link, domain, el) {
                            Events.fire("post_describe_link", [
                                link,
                                function(data) {
                                    var savePost = function(d) {
                                        d = d || {};
                                        Events.fire('post_link_data', [
                                            {
                                                link: d.link || link,
                                                header: d.title || data.title,
                                                coords: d.coords || data.coords,
                                                description: d.description || data.description
                                            }, function(data) {
                                                if (data) {
                                                    if (data.img) {
                                                        el.find('.link-img').css('background-image', 'url(' + data.img + ')');
                                                    }
                                                    popupSuccess('Изменения сохранены');
                                                }
                                            }
                                        ]);
                                    };
                                    var $del = $('<div/>', {class: 'delete-attach'}).click(function() {
                                        $links.html('');
                                    });
                                    el.html(linkTplFull);
                                    el.find('a').attr('href', link).html(domain);
                                    el.find('.link-status-content').append($del);

                                    if (data.img) {
                                        el.find('.link-img')
                                            .css('background-image', 'url(' + data.img + ')')
                                            .click(function() {
                                                var originalImage = new Image();
                                                originalImage.src = data.imgOriginal;
                                                originalImage.onload = function () {
                                                    var linkImageCoords = {};
                                                    var closePopup = function() {
                                                        $popup.remove();
                                                        $bg.remove();
                                                    };
                                                    var showPreview = function(coords)
                                                    {
                                                        linkImageCoords = coords;
                                                        var $preview = $popup.find('.preview');
                                                        var rx = $preview.width() / coords.w;
                                                        var ry = $preview.height() / coords.h;

                                                        $preview.find('> img').css({
                                                            width: Math.round(rx * $('.jcrop-holder').width()) + 'px',
                                                            height: Math.round(ry * $('.jcrop-holder').height()) + 'px',
                                                            marginLeft: '-' + Math.round(rx * coords.x) + 'px',
                                                            marginTop: '-' + Math.round(ry * coords.y) + 'px'
                                                        });
                                                    };
                                                    var $bg = $('<div/>', {class: 'popup-bg'}).appendTo('body');
                                                    var $popup = $('<div/>', {
                                                            'class': 'popup-image-edit',
                                                            'html': '<div class="title">Редактировать изображение</div>'+
                                                                '<div class="close"></div>' +
                                                                '<div class="left-column">' +
                                                                    '<div class="original"><img src="'+originalImage.src+'" /></div>' +
                                                                '</div>' +
                                                                '<div class="right-column">' +
                                                                    '<div class="preview"><img src="'+originalImage.src+'" /></div>'+
                                                                    '<div class="button spr save">Сохранить</div>'+
                                                                '</div>'
                                                        })
                                                        .appendTo('body');

                                                    $bg.click(closePopup);
                                                    $popup.css({'margin-left': -$popup.width()/2});
                                                    $popup.find('.close').click(closePopup);
                                                    $popup.find('.save').click(function() {
                                                        data.coords = linkImageCoords;
                                                        savePost({coords: linkImageCoords});
                                                        closePopup();
                                                    });
                                                    $popup.find('.original > img').Jcrop({
                                                        onChange: showPreview,
                                                        onSelect: showPreview,
                                                        aspectRatio: 2.06,
                                                        minSize: [130,63],
                                                        setSelect: [0,0,130,63]
                                                    });
                                                };
                                            });
                                    } else {
                                        el.find('.link-img').remove();
                                    }
                                    if (data.title) {
                                        el.find('div.link-description-text a')
                                            .text(data.title)
                                            .click(function() {
                                                var $title = $(this);
                                                $title.attr('contenteditable', true).focus();
                                                return false;
                                            })
                                            .blur(function() {
                                                var $title = $(this);
                                                $title.attr('contenteditable', false);
                                                data.title = $title.text();
                                                savePost({title: $title.text()});
                                            });
                                    }
                                    if (data.description) {
                                        el.find('div.link-description-text p')
                                            .text(data.description)
                                            .click(function() {
                                                var $description = $(this);
                                                $description.attr('contenteditable', true).focus();
                                                return false;
                                            })
                                            .blur(function() {
                                                var $description = $(this);
                                                $description.attr('contenteditable', false);
                                                data.description = $description.text();
                                                savePost({description: $description.text()});
                                            });
                                    }
                                }
                            ]);
                        }
                        function addPhoto(path, filename, el) {
                            var $photo = $('<span/>', {class: 'attachment'})
                                .append('<img src="' + path + '" alt="" />')
                                .append($('<div />', {class: 'delete-attach', title: 'Удалить'})
                                .click(function() {
                                        $photo.remove();
                                    })
                                )
                                .append($('<input />', {type: 'hidden', name: '', value: filename}))
                                .appendTo(el);
                        }

                        var cache = {
                            html: $el.html(),
                            scroll: $(window).scrollTop()
                        };
                        $post.find('> .content').draggable('disable');
                        $post.editing = true;
                        $buttonPanel.hide();
                        $el.html('');

                        var $edit = $('<div/>', {class: 'editing'}).appendTo($el);
                        var $content = $('<div/>').appendTo($edit);
                        var $attachments = $('<div/>', {class: 'attachments'}).appendTo($edit);
                        var $text = $('<textarea/>').appendTo($content);
                        var $links = $('<div/>', {class: 'links link-info-content'}).appendTo($attachments);
                        var $photos = $('<div/>', {class: 'photos'}).appendTo($attachments);
                        var $actions = $('<div/>', {class: 'actions'}).appendTo($edit);
                        var $saveBtn = $('<div/>', {class: 'save button spr l', html: 'Сохранить'}).click(function() {onSave()}).appendTo($actions);
                        var $cancelBtn = $('<a/>', {class: 'cancel l', html: 'Отменить'}).click(function() {onCancel()}).appendTo($actions);
                        var $uploadBtn = $('<a/>', {class: 'upload r', html: 'Прикрепить'}).appendTo($actions);

                        var uploader = new qq.FileUploader({
                            debug: true,
                            element: $uploadBtn.get(0),
                            action: root + 'int/controls/image-upload/',
                            template: '<div class="qq-uploader">' +
                                '<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>' +
                                '<div class="qq-upload-button">Прикрепить</div>' +
                                '<ul class="qq-upload-list"></ul>' +
                                '</div>',
                            onComplete: function(id, fileName, res) {
                                addPhoto(res.image, res.filename, $photos);
                            }
                        });
                        var onSave = function() {
                            var text = $text.val();
                            var link = $links.find('a').attr('href');
                            var photos = new Array();
                            $photos.children().each(function() {
                                var photo = new Object();
                                photo.filename = $(this).find('input:hidden').val();
                                photos.push(photo);
                            });
                            Events.fire("post", [
                                text,
                                photos,
                                link,
                                postId,
                                function(data) {
//                                    $post.draggable('enable').removeClass('editable');
//                                    $buttonPanel.show();
//                                    $edit.remove();
                                }
                            ]);
                        };
                        var onCancel = function() {
                            $post.find('> .content').draggable('enable');
                            $post.editing = false;
                            $buttonPanel.show();
                            $el.html(cache.html);
                            $edit.remove();
                        };

                        if (true || data.text) {
                            var text = data.text;
                            $text
                                .val(text.split('<br />').join('')) // because it's textarea
                                .appendTo($content)
                                .bind('paste', function(e) {
                                    setTimeout(function() {
                                        parseUrl($text.val(), function(link, domain) {
                                            if ($text.link && $links.html() || $text.link == link) return;
                                            $text.link = link;
                                            addLink(link, domain, $links);
                                        });
                                    }, 0);
                                })
                                .bind('keyup', function(e) {
                                    autoResize($text);

                                    if (e.ctrlKey && e.keyCode == 13) {
                                        onSave();
                                    }
                                })
                                .keyup().focus();
                            setCaretToPos($text.get(0), text.length);
                        }

                        if (data.link) {
                            var link = data.link;
                            parseUrl(data.link, function(link, domain) {
                                addLink(link, domain, $links);
                            });
                        }

                        if (data.photos) {
                            var photos = eval(data.photos);
                            $(photos).each(function() {
                                addPhoto(this.path, this.filename, $photos);
                            });
                        }
                    })($post, $content, data);
                }
            }]);
        });
    })();

    $(".left-panel").delegate(".show-cut", "click" ,function(e){
        var $content = $(this).closest('.content'),
            $shortcut = $content.find('.shortcut'),
            shortcut = $shortcut.html(),
            cut      = $content.find('.cut').html();

        $shortcut.html(shortcut + ' ' + cut);
        $(this).remove();

        e.preventDefault();
    });

    $(".right-panel").delegate(".toggle-text", "click", function(e) {
        $(this).parent().toggleClass('collapsed');
    });

    (function(w) {
        var $elem = $('#go-to-top');
        $elem.click(function() {
            $(w).scrollTop(0);
        });
        $(w).bind('scroll', function(e) {
            if (e.currentTarget.scrollY <= 0) {
                $elem.hide();
            } else if (!$elem.is(':visible')) {
                $elem.show();
            }
        });
    })(window);

    Elements.addEvents();
});

var linkTplFull = '<div class="link-status-content"><span>Ссылка: <a href="" target="_blank"></a></span></div>\
            <div class="link-description-content">\
                <div class="link-img l" />\
                <div class="link-description-text l">\
                    <a href="" target="_blank"></a>\
                    <p></p>\
                </div>\
                <div class="clear"></div>\
            </div>';

var linkTplShort = '<div class="link-status-content"><span>Ссылка: <a href="" target="_blank"></a></span></div>\
            </div>';

var Events = {
    fire : function(name, args){
        if(typeof args != "undefined") {
            if(!$.isArray(args)) args = [args];
        } else {
            args = [];
        }
        if($.isFunction(this[name])) {
            try {
                this[name].apply(window, args);
            } catch(e) {
                if(console && $.isFunction(console.log)) {
                    console.log(e);
                }
            }
        }
    }
};
$.extend(Events, Eventlist);
delete(Eventlist);

var Elements = {
    initImages: function(selector){
        $(".fancybox-thumb").fancybox({
            prevEffect		: 'none',
            nextEffect		: 'none',
            closeBtn		: false,
            fitToView       : false,
            helpers		: {
                title	: { type : 'inside' },
                buttons	: {}
            }
        });

        //логика картинок топа
        $("div.post-image-top img").bind("load", function () {
            var src = $(this).attr('src');
            var img = new Image();
            var link = $(this).closest(".post").find('.ajax-loader-ext');

            img.onload = function() {
                if (this.width < 250 && this.height < 250) {
                    //small
                    Elements.initLinkLoader(link, true);
                } else {
                    //big
                    Elements.initLinkLoader(link, false);
                }
            };

            img.src = src;
        });
    },
    addEvents: function(){
        (function(){
            $(".slot .post .content").addClass("dragged");
            var target = false;
            var dragdrop = function(post, slot, queueId, callback, failback){
                Events.fire('post_moved', [post, slot, queueId, function(state, newId){
                    if (state) {
                        callback(newId);
                    } else {
                        failback();
                    }
                }]);
            };

            var draggableParams = {
                revert: 'invalid',
                appendTo: 'body',
                cursor: 'move',
                cursorAt: {left: 100, top: 20},
                helper: function() {
                    return $('<div/>').html('Укажите, куда поместить пост...').addClass('moving dragged');
                },
                start: function() {
                    var self = $(this),
                        $post = self.closest('.post');
                    $post.addClass('moving');
                },
                stop: function() {
                    var self = $(this),
                        $post = self.closest('.post');
                    $post.removeClass('moving');
                }
            };

            $(".post > .content").draggable(draggableParams);

            $('.items .slot').droppable({
                activeClass: "ui-state-active",
                hoverClass: "ui-state-hover",

                drop: function(e, ui) {
                    var target = $(this),
                        post = $(ui.draggable).closest('.post'),
                        slot = post.closest('.slot'),
                        helper = $(ui.helper);

                    if (target.hasClass('empty')) {
                        dragdrop(post.data("id"), target.data("id"), post.data("queue-id"), function(newId){
                            if (post.hasClass('movable')) {
                                target.html(post);
                            } else {
                                var copy = post.clone();
                                copy.addClass("dragged");
                                target.html(copy);
                                copy.draggable(draggableParams);
                            }
                            slot.addClass('empty');
                            target.removeClass('empty');

                            target.find('.post').data("id", newId).data("queue-id", newId);
                        },function(){

                        });
                    }
                }
            });
        })();
    },
    leftdd: function(){
        return $("select").multiselect("getChecked").map(function(){
            return this.value;
        }).get();
    },
    rightdd:function(value){
        if(typeof value == 'undefined') {
            return $(".right-panel .drop-down").data("selected");
        } else {
            $(".right-panel .drop-down").dd_sel(value);
        }
    },
    calendar: function(value){
        if(typeof value == 'undefined') {
            var timestamp = $("#calendar").datepicker("getDate");
            return timestamp ? timestamp.getTime() / 1000 : null;
        } else {
            $("#calendar").datepicker("setDate", value).closest(".calendar").find(".caption").html("&nbsp;");
        }
    },
    initLinkLoader: function(obj, full){
        var container   = obj.parents('div.link-info-content');
        var link        = obj.attr('rel');
        $.ajax({
            url: controlsRoot + 'parse-url/',
            type: 'GET',
            dataType : "json",
            data: {
                url: link
            },
            success: function (data) {
                if (full) {
                    container.html(linkTplFull);
                } else {
                    container.html(linkTplShort);
                }

                if (data.img) {
                    container.find('.link-img').css('background-image', 'url(' + data.img + ')');
                } else {
                    container.find('.link-img').remove();
                }
                if (data.title) {
                    container.find('div.link-description-text a').text(data.title);
                }
                if (data.description) {
                    container.find('div.link-description-text p').text(data.description);
                }

                container.find('a').attr('href', link);

                var matches = link.match(pattern);

                shortLink = link;
                if (matches[2]) {
                    shortLink = matches[2];
                }
                container.find('div.link-status-content span a').text(shortLink);
            }
        });
    },
    initLinks: function(){
        $('img.ajax-loader').each(function(){
            Elements.initLinkLoader($(this), true);
        });
    }
};

$.fn.dd_sel = function(id){
    var elem = $(this);
    if(!elem.hasClass("drop-down")) return;
    if(id) {
        elem = elem.find("li[data-id=" + id + "]");
    } else {
        elem = elem.find("li:first");
    }

    $(this).find('li.active').removeClass('active');
    elem.addClass('active');

    if(elem.length) {
        $(this)
            .data("selected",elem.data("id"))
            .find(".caption")
            .text(elem.text())
            .removeClass("default");
    } else {
        $(this)
            .data("selected",0)
            .find(".caption").text('Источник').addClass("default");
    }
    $(this).trigger("change");
};