var articlesLoading = false;

$(function(){
    $( "#slider-range" ).slider({
    range: true,
    min: 0,
    max: 100,
    values: [ 50, 100 ],
        slide: function( event, ui ) {
            changeRange();
        }
        , change: function(event, ui) {
            changeRange();
            loadArticles(true);
        }
    });

    changeRange();
});

function changeRange() {
    $( "#slider-value" ).text( "❤" + $( "#slider-range" ).slider( "values", 0 ) +
        " - ❤" + $( "#slider-range" ).slider( "values", 1 ) );
}

function loadArticles(clean) {
    if (articlesLoading) return;

    articlesLoading = true;

    if (clean) {
        $('div#wall').html('');
    }

    if (Elements.leftdd().length == 0) {
        articlesLoading = false;
        return;
    }

    if (Elements.leftdd().length != 1) {
        $('.newpost').hide();
    } else {
        $('.newpost').show();
    }

    $('div#wall').append('<div style="text-align: center;" id="wall-loader"><img src="' + root + 'shared/images/fe/ajax-loader.gif"></div>');

    var from = $( "#slider-range" ).slider( "values", 0 );
    var to = $( "#slider-range" ).slider( "values", 1 );

    if ($('.type-selector a.active').data('type') == 'ads') {
        from = 0;
        to = 100;
    }

    //clean and load left column
    $.ajax({
        url: controlsRoot + 'arcticles-list/',
        dataType : "html",
        data: {
            sourceFeedIds: Elements.leftdd(),
            clean: clean,
            from : from,
            to : to
        },
        success: function (data) {
            $('div#wall div#wall-loader').remove();
            $('div#wall').append(data);
            articlesLoading = false;
            Elements.addEvents();
            Elements.initImages('.post .images');
            Elements.initLinks();
        }
    });
}

function loadQueue() {
    if (!Elements.rightdd()) {
        return;
    }

    //clean and load right column
    $.ajax({
        url: controlsRoot + 'arcticles-queue-list/',
        dataType : "html",
        data: {
            targetFeedId: Elements.rightdd(),
            timestamp: Elements.calendar()
        },
        success: function (data) {
            $('div#queue').show().html(data);
            Elements.addEvents();
            Elements.initImages('.post .images');
            Elements.initLinks();

            $('.post.blocked').draggable('disable');
        }
    });
}

function reloadArticle(id) {
    $.ajax({
        url: controlsRoot + 'arcticle-item/',
        dataType : "html",
        data: {
            id: id
        },
        success: function (data) {
            elem = $("div.post[data-id=" + id + "]");
            elem.replaceWith(data);

            Elements.addEvents();
            Elements.initImages('.post .images');
            Elements.initLinks();
        }
    });
}

var Eventlist = {
    leftcolumn_deletepost: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-delete/',
            data: {
                id: post_id
            },
            success: function (data) {
                callback(1);
            }
        });
    },
    leftcolumn_clear_post_text: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-clear-text/',
            data: {
                id: post_id
            },
            success: function (data) {
                callback(1);
            }
        });
    },
    leftcolumn_recoverpost: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-restore/',
            data: {
                id: post_id
            },
            success: function (data) {
                callback(1);
            }
        });
    },
    rightcolumn_deletepost: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-queue-delete/',
            data: {
                id: post_id
            },
            success: function (data) {
                callback(1);
            }
        });
    },
    leftcolumn_dropdown_change: function(){
        loadArticles(true);
    },
    rightcolumn_dropdown_change: function(){
        selectedSources = Elements.leftdd();
        sourceType = $(".type-selector a.active").data('type');

        $('#source-select option').remove();
        $('#source-select').multiselect("refresh");

        loadQueue();

        //грузим источники для этого паблика
        $.ajax({
            url: controlsRoot + 'source-feeds-list/',
            dataType : "json",
            data: {
                targetFeedId: Elements.rightdd(),
                type: sourceType
            },
            success: function (data) {
                for (i in data) {
                    item = data[i];
                    $('#source-select').append('<option value="' + item.sourceFeedId + '">' + item.title + '</option>');
                }

                if (selectedSources) {
                    $options = $('#source-select option');
                    for (i in selectedSources) {
                        $options.filter('[value="'+selectedSources[i]+'"]').prop('selected', true);
                    }
                }

                $('#source-select').multiselect("refresh");

                if (Elements.leftdd().length == 0) {
                    $('#source-select').multiselect("checkAll");
                    $('#source-select').multiselect("refresh");
                }

                Events.fire('leftcolumn_dropdown_change', []);
            }
        });
    },
    calendar_change: function(){
        loadQueue();
    },
    wall_load_more: function(callback){
        if (!$("#wallloadmore").hasClass('hidden')) {
            $("#wallloadmore").addClass('hidden');
            loadArticles(false);
        }
        callback(true);
    },
    post_moved: function(post_id, slot_id, queueId, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-add-to-queue/',
            dataType : "json",
            data: {
                articleId: post_id,
                timestamp: slot_id,
                targetFeedId: Elements.rightdd(),
                queueId: queueId
            },
            success: function (data) {
                if(data.success) {
                    callback(1, data.id);
                    loadQueue();
                } else {
                    if (data.message) {
                        popupError(Lang[data.message]);
                    }
                    callback(0);
                }
            }
        });
    },

    /* после выполнения запроса к сервису. Вызвать callback(state) state = {}|false */
    leftcolumn_source_edited: function(val,id, callback){callback({value: val});},
    leftcolumn_source_deleted: function(id, callback){callback(true)},
    leftcolumn_source_added: function(val, callback){callback({value: val, id: parseInt(Math.random()*100)})},

    rightcolumn_source_edited: function(val,id, callback){callback({value: val});},
    rightcolumn_source_deleted: function(id, callback){callback(true)},
    rightcolumn_source_added: function(val, callback){callback({value: val, id: parseInt(Math.random()*100)})},

    load_post_edit: function(id, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-get/',
            dataType : "json",
            data: {
                articleId: id
            },
            success: function (data) {
                if(data && data.id) {
                    callback(true, data);
                } else {
                    callback(false, null);
                }
            }
        });
    },

    post_describe_link: function(link, callback) {
//        $('div.link-description').html('<img src="' + root + 'shared/images/fe/ajax-loader.gif">');
//        $('div.link-info').show();
        $.ajax({
            url: controlsRoot + 'parse-url/',
            type: 'GET',
            dataType : "json",
            data: {
                url: link
            },
            success: function (data) {
//                $('div.link-description').html('');
//                $('div.link-info').hide();
                callback(data);
            }
        });
    },

    post: function(text, photos, link, id, callback){
        $sourceFeedIds = Elements.leftdd();
        if ($sourceFeedIds.length != 1) {
            $sourceFeedId = null;
        } else {
            $sourceFeedId = $sourceFeedIds[0];
        }

        $.ajax({
            url: controlsRoot + 'arcticle-save/',
            type: 'POST',
            dataType : "json",
            data: {
                articleId: id,
                text: text,
                photos: photos,
                link: link,
                sourceFeedId: $sourceFeedId
            },
            success: function (data) {
                if(data.success) {
                    if (id) {
                        //перезагружаем тело поста
                        reloadArticle(id);
                    } else {
                        //перезагружаем весь левый блок
                        loadArticles(true);
                    }

                    callback(true);
                } else {
                    if (data.message) {
                        popupError(Lang[data.message]);
                    }
                    callback(false);
                }
            }
        });
    },

    post_link_data: function(data, callback) {
        $('div.link-description').html('<img src="' + root + 'shared/images/fe/ajax-loader.gif">');
        $.ajax({
            url: controlsRoot + 'link-info-upload/',
            type: 'GET',
            dataType : "json",
            data: {
                data: data
            },
            success: function (data) {
                if (data) {
                    $('.reload-link').click();
                    callback(data);
                } else {
                    popupError('Ошибка сохренения информации о ссылке');
                    callback(false);
                }
            }
        });
    },

    eof: null
}

function popupSuccess( message ) {
    $.blockUI({
        message: message,
        fadeIn: 600,
        fadeOut: 1000,
        timeout: 2500,
        showOverlay: false,
        centerY: false,
        css: {
            width: 'auto',
            'max-width': '200px',
            top: '15px',
            left: 'auto',
            right: '15px',
            border: 'none',
            padding: '25px 30px 25px 60px',
            'font-size': '13px',
            'text-align': 'left',
            color: '#333',
            'background': '#EBF0DA url('  + root +  'shared/images/vt/ui/icon_v.png) no-repeat 25px 50%',
            'border-radius': '5px',
            opacity: 1,
            'box-shadow': '0 0 6px #000'
        }
    });
}

function popupError( message ) {
    $.blockUI({
        message: message,
        fadeIn: 600,
        fadeOut: 1000,
        timeout: 2500,
        showOverlay: false,
        centerY: false,
        css: {
            width: 'auto',
            'max-width': '200px',
            top: '15px',
            left: 'auto',
            right: '15px',
            border: 'none',
            padding: '25px 30px 25px 60px',
            'font-size': '13px',
            'text-align': 'left',
            color: '#333',
            'background': '#FEDADA url('  + root +  'shared/images/vt/ui/icon_x.png) no-repeat 25px 50%',
            'border-radius': '5px',
            opacity: 1,
            'box-shadow': '0 0 6px #000'
        }
    });
}

function popupNotice( message ) {
    $.blockUI({
        message: message,
        fadeIn: 600,
        fadeOut: 1000,
        timeout: 2500,
        showOverlay: false,
        centerY: false,
        css: {
            width: 'auto',
            'max-width': '200px',
            top: '15px',
            left: 'auto',
            right: '15px',
            border: 'none',
            padding: '25px 30px 25px 60px',
            'font-size': '13px',
            'text-align': 'left',
            color: '#333',
            'background': '#FBFFBF url('  + root +  'shared/images/vt/ui/icon_i.png) no-repeat 25px 50%',
            'border-radius': '5px',
            opacity: 1,
            'box-shadow': '0 0 6px #000'
        }
    });
}