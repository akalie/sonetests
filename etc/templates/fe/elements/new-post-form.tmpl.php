<div class="newpost collapsed bb">
    <textarea placeholder="Есть чем поделиться?"></textarea>
    <div class="link-info" style="display: none;">
        <div class="link-description"><!-- --></div>
        <div class="link-status"><!-- --></div>
    </div>
    <div id="attach-file" class="buttons attach-file">
        <div class="save button spr l">Отправить</div>
        <a href="#" class="cancel spr l">Отменить</a>
        <!-- Штука для загрузки файла -->
    </div>
    <div class="clear"></div>
</div>
<script type="text/javascript">
    function uploadCallback( data ) {
        t = $("#fileTemplate").tmpl( {title: '', filename: data.filename, isTemp: data.isTemp, path: data.image}, { counter: filesCounter } );
        $('.qq-upload-list').append(t);
        $(".qq-upload-success a.delete-attach").click(function(e){
            $(this).closest('li').remove();
            e.preventDefault();
        });
        $(".qq-upload-list li").each(function(){
            if (!$(this).attr('id')) {
                $(this).hide();
            }
        });
    }
</script>
<script id="fileTemplate" type="text/x-jquery-tmpl">
    <li class="qq-upload-success" id="file-${ $item.counter.nextIndex() }">
        <input type="hidden" name="files[${ $item.counter.index }][filename]" value="${filename}">
        <a href="javascript:;" class="delete-attach">удалить</a><img src="${path}" alt="" />
    </li>
</script><script>''</script>