//<script>
$(document).ready(function(){

    function appFormItemPhoto_InitItem($photo) {

        //inicializace fancyboxu
        if ($photo.find('a.fancybox').length != 0) {
            $photo.find('a.fancybox').fancybox({
                hideOnOverlayClick: true,
                hideOnContentClick: true,
                speedIn: 200,
                speedOut: 10,
                titleShow: true,
                titlePosition: 'inside'

            });
        }

        //kliknutim na tlacitko cancel zrusim soubor - odstranim
        //vsechny prvky, ktere se ho tykaji ze stranky
        $photo.find('.cancel').click(function(){

            //pridam classu, ktera prvek zvyrazni aby uzivatel videl
            //ktery soubor bude odstranen
            $photo.addClass('removed');
            
            if (confirm("<?= __('appformitemfile.confirm_file_delete');?>")) {

                //id polozky (souboru ) je ulozeno v inputu, ktery v name atributu obsahuej "[id]"
                var $id_input = $photo.find('input[name*="\[id\]"]');

                //pokud nebyl input nalezen, tak nelze se souborem pracovat
                if ($id_input.length == 0) {
                    alert("<?= __('appformitemfile.cannot_delete');?>");
                    return false;
                }

                //pokud $id_input neobsahuje v nazvu "[l]", tak jeste nebyl ulozen
                //a soubor muzu tedy bez ajaxu rovnou odstrnanit ze stranky
                if ($id_input.attr('name').match(/\[l\]/) == null) {

                    //odstranim soubor ze stranky
                    $photo.remove();

                    //a ajax uz neni treba provadet
                    return false;
                }

                //zobrazim progress indicator nad polozkou souboru
                $photo.block({message: "<?= __('appformitemfile.delete_ptitle');?>"});

                //vezmu si ID polozky (souboru)
                var item_id = $id_input.val();

                $.getJSON("<?= $delete_url;?>", {id:item_id}, function(response_data){

                    if (typeof response_data['error'] !== 'undefined') {
                        
                        //zrusim progress indicator
                        $photo.unblock();

                        //zobrazim uzivateli text chyby
                        alert(response_data['error']);
                        
                        //pri odstranovani zaznamu doslo k chybe - pocitam s tim
                        //ze soubor odstranen nebyl
                        $photo.removeClass('removed');
                    } else {
                        //zrusim progress indicator
                        $photo.unblock();

                        //soubor byl uspesne odstranen - smazu jej ze stranky
                        $photo.remove();
                    }
                    
                });

            } else {
                //pokud nepotvrdil tak oznaceni odstranim
                $photo.removeClass('removed');
            }

            return false;
        });
        
    }

    $("#<?= $uid;?>").each(function(){

        $(this).find('.list .item').each(function(){
            appFormItemPhoto_InitItem($(this));
        });

        //pokud se v prvku nenachazi tlacitko pro upload, tak se dale nic neprovadi
        if ($(".button", $(this)).length == 0) {
            return;
        }

        //odkaz na aktualni obal objektu
        var $item = $(this);

    <?php if (isset($sortable) && ! empty($sortable)): ?>
        //ma fungovat serazeni prvku pomoci drag&drop ?
        //inicializace razeni prvku
        $(this).find('.list').sortable({
            placeholder: "ui-state-highlight",
            handle: ".drag_handler",
            update: function (event, ui) {
                //tento atribut slouzi k ulozeni poradi daneho prvku
                var i = 0;
                $item.find('.list .list_item').each(function(){
                    console.log('input[name$="[<?= $sortable;?>][]"]');
                    $(this).find('input[name$="[<?= $sortable;?>][]"]').val(i++);
                });
                //uzivateli se zobrazi info zprava - porad prvku bude
                //zachovano jen kdyz se ulozi formular
                $.userInfoMessage("<?= __('form.AppFormItemAdvancedItemlist.order_update.info_message');?>");
            }
        }).disableSelection();
        <?php endif ?>

        var uploader = new qq.FileUploader({
            // pass the dom node (ex. $(selector)[0] for jQuery users)
            element: $(this).find(".button").get(0),
            // path to server-side upload script
            action: '<?= $action_url;?>',

            // povolim upload vice souboru najednou
            multiple: <?= (int)$multiple_files;?>,

            // additional data to send, name-value pairs
            params: <?= json_encode($params);?>,

            <?php if ( ! empty($allowed_extensions)): ?>
            // validation
            // ex. ['jpg', 'jpeg', 'png', 'gif'] or []
            allowedExtensions: ['<?=implode("', '", $allowed_extensions);?>'],
            <?php else: ?>
            allowedExtensions: [],
            <?php endif ?>
            // each file size limit in bytes
            // this option isn't supported in all browsers
            sizeLimit: <?=(int)$max_size;?>, // max size
            minSizeLimit: 1, // min size

            // set to true to output server response to console
            debug: false,

            template: '<div class="qq-uploader">' +
                        '<div class="qq-upload-drop-area"><span><?=__("valumsUpload.drop_files_here_to_upload");?></span></div>' +
                        '<div class="qq-upload-button"><?=__("valumsUpload.upload_file");?></div>' +
                        '<ul class="qq-upload-list"></ul>' +
                      '</div>',

            //vizualizaci nahranych souboru si budu resit sam
            fileTemplate:'<li  class="qq_file_item" >' +
                            '<span class="qq-upload-label"><?= __('valumsUpload.qq_upload_label');?></span>' +
                            '<span class="qq-upload-file"></span>' +
                            '<span class="qq-upload-spinner"></span>' +
                            '<span class="qq-upload-size"></span>' +
                            '<a class="qq-upload-cancel" href="#"><?=__("valumsUpload.cancel_upload");?></a>' +
                        '</li>',
            showMessage: function(message) {

                //resetuju timeout, ktery muze stale byt aktivni (pokud nekdo zbesile klika)
                clearTimeout(this.timer);

                //zobrazim zpravu uzivateli
                $ref = $item.find('.message_placeholder');
                $ref.find('div').html(message).parent().show();

                //nastavim timer, ktery zpravu za definovany interval schova
                this.timer = window.setTimeout(function(){
                    $ref.animate({
                        opacity: 0.0
                    }, 500, function(){
                        //div vyprazdnim a skryju, opacitu vratim na puvodni hodnotu
                        //aby to bylo pripraveno pro dalsi zobrazeni
                        $(this).hide().css({opacity:1.0});
                    });
                }, 30000);
                return false;
            },
            // events
            // you can return false to abort submit
            onSubmit: function(id, fileName){

                <?php if ($file_count != 0): ?>
                if ($item.find('.list .item:not(.removed)').length >= <?= $file_count;?>) {
                    this.showMessage("<?= __('appformitemfile.maximum_allowed_file_count_is', array(':count' => $file_count));?>");
                    return false;
                }
                <?php endif ?>
            },
            onProgress: function(id, fileName, loaded, total){},
            onComplete: function(id, fileName, responseJSON){

                //preview souboru, ktere bylo zobrazeno v prubehu uploadu zrusim
                //ze serveru se nacte specialni panel s preview fotky
                $item.find('.qq_file_item').each(function(){
                    if (this.qqFileId == id) {
                        $(this).hide();
                    }
                });

                //pokud prislo preview souboru tak pridam na konec seznamu uploadovanych
                //souboru
                if (typeof responseJSON['file_preview'] != 'undefined') {

                    <?php if ($attr != $itemlist_attr):?>

                    var string = responseJSON['file_preview'];
                    string = string.replace(/<?= $attr;?>\[/, '<?=$itemlist_attr;?>[');
                    var $uploaded_file_preview = $(string);

                    <?php else: ?>
                    
                    var $uploaded_file_preview = $(responseJSON['file_preview']);

                    <?php endif ?>



                    //provede inicizalizaci zakladnich prvku na polozce
                    appFormItemPhoto_InitItem($uploaded_file_preview);

                    $item.find('.list').append($uploaded_file_preview);

                    //change event tady je vyvolan aby se propagoval vyse -
                    //to je potreba napriklad pri pouziti na advanceditemlist
                    //kde prvek chyta change udalost aby poznal ze uzivatel uz
                    //zadal alespon neco a povoli mu pridat dalsi novou (prazdnou)
                    //polozku
                    $item.trigger('change');

                    return false;
                }
            },
            onCancel: function(id, fileName){

                //preview souboru, ktere bylo zobrazeno v prubehu uploadu zrusim
                //ze serveru se nacte specialni panel s preview fotky
                $item.find('.qq_file_item').each(function(){
                    if (this.qqFileId == id) {
                        $(this).hide();
                    }
                });

            }

            //messages: {
            //    // error messages, see qq.FileUploaderBasic for content
            //},
        });

    });
});