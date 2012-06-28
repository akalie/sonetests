/**
 * Counters for album index
 */
var Counter = function() {
    this.index = -1;
}

Counter.prototype.nextIndex = function() {
    this.index ++;
    return this.index;
}

var filesCounter = new Counter();

$(document).ready(function() {
    try {
        var uploader = new qq.FileUploader({
            debug: false,
            element: $('#attach-file')[0],
            action: root + 'int/controls/image-upload/',
            template: ' <div class="qq-uploader">' +
                '<ul class="qq-upload-list"></ul>' +
                '<div class="save button spr l">Отправить</div>' +
                '<a href="javascript:;" class="cancel spr l">Отменить</a>' +
                '<a href="javascript:;" class="qq-upload-button">Прикрепить</a>' +
                '</div>',
            onComplete: function(id, fileName, responseJSON) {
                result = responseJSON;
                if( result ) {
                    if( !result.error ) {
                        result.isTemp = true;
                        uploadCallback( result );
                    }
                }
            }
        });
    } catch (e){}
});