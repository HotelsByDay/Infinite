//<script>
$(document).ready(function(){
    function appFormItemPhoto_InitFancybox($uploader) {

        if ($uploader.find('a.fancybox').length != 0) {
            // Tohle nejak nefunguje
        //    $uploader.find('a.fancybox').fancybox.cancel();
            $uploader.find('a.fancybox').fancybox({
                hideOnOverlayClick: true,
                hideOnContentClick: true,
                speedIn: 200,
                speedOut: 10,
                titleShow: true,
                titlePosition: 'inside',
                nextEffect: 'fade',
                prevEffect: 'fade'
            });
        }
    }

    function appFormItemPhoto_InitItem($photo, $item, $form) {

        //inicializace fancyboxu

        //kliknutim na tlacitko cancel zrusim soubor - odstranim
        //vsechny prvky, ktere se ho tykaji ze stranky
        $photo.find('.cancel').click(function(){
            //pridam classu, ktera prvek zvyrazni aby uzivatel videl
            //ktery soubor bude odstranen
            $photo.addClass('removed');
            
            var confirm_msg = $(this).attr('data-confirm');
            if (typeof confirm_msg == 'undefined' || ! confirm_msg) {
                confirm_msg = "<?= __('appformitemfile.confirm_file_delete');?>";
            }
            if (confirm(confirm_msg)) {

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

                    //the layout and dmensions of this form item may have changed
                    $form.objectForm('fireEvent', 'itemLayoutChanged', $item);
                    //item has been changed (form values has been changed)
                    $form.objectForm('fireEvent', 'change', $item);


                    <?php if ($file_count != 0): ?>
                    // Hide drop area and upload btn if files limit was reached (here show if limit is not reached)
                    if ($item.find('.list .item:not(.removed)').length + $item.find('.qq_file_item:visible').length < <?= $file_count;?>) {
                        $item.find('.button').show();
                    }
                    <?php endif; ?>

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

                        //the layout and dmensions of this form item may have changed
                        $form.objectForm('fireEvent', 'itemLayoutChanged', $item);
                        //item has been changed (form values has been changed)
                        $form.objectForm('fireEvent', 'change', $item);
                    }
                    
                });

            } else {
                //pokud nepotvrdil tak oznaceni odstranim
                $photo.removeClass('removed');
            }

            <?php if ($file_count != 0): ?>
            // Hide drop area and upload btn if files limit was reached (here show if limit is not reached)
            if ($item.find('.list .item:not(.removed)').length + $item.find('.qq_file_item:visible').length < <?= $file_count;?>) {
                $item.find('.button').show();
            }
            <?php endif; ?>

            return false;
        });
        
    }

    $("#<?= $uid;?>").each(function(){

        //odkaz na aktualni obal objektu
        var $item = $(this);

        //reference na rodicovsky formular
        var $form = $item.parents(".<?= AppForm::FORM_CSS_CLASS ?>:first");


        <?php if ($file_count != 0): ?>
        // Hide drop area and upload btn if files limit was reached (here show if limit is not reached)
        if ($item.find('.list .item:not(.removed)').length + $item.find('.qq_file_item:visible').length >= <?= $file_count;?>) {
            $item.find('.button').hide();
        }
        <?php endif; ?>

        // Inicializace fancyboxu
        appFormItemPhoto_InitFancybox($(this));
        $(this).find('.list .item').each(function(){
            appFormItemPhoto_InitItem($(this), $item, $form);
        });

        //pokud se v prvku nenachazi tlacitko pro upload, tak se dale nic neprovadi
        if ($(".button", $(this)).length == 0) {
            return;
        }

    <?php if (isset($sortable) && ! empty($sortable)): ?>
        //ma fungovat serazeni prvku pomoci drag&drop ?
        //inicializace razeni prvku
        $(this).find('.list').sortable({
            placeholder: "file-sortable-placeholder",
//            handle: ".drag_handler",
            update: function (event, ui) {
                //tento atribut slouzi k ulozeni poradi daneho prvku
                var i = 0;
                $item.find('.list .file_item').each(function(){
                    $(this).find('input[name$="[<?= $sortable;?>][]"]').val(i++);
                });
                //uzivateli se zobrazi info zprava - porad prvku bude
                //zachovano jen kdyz se ulozi formular
                $.userInfoMessage("<?= __('form.AppFormItemFile.order_update.info_message');?>");
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
                        '<div class="qq-upload-button upld btn btn-info"><?=__("valumsUpload.upload_file");?></div>' +
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
                var $ref = $item.find('.message_placeholder');
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
                }, 10000);
                return false;
            },
            // events
            // you can return false to abort submit
            onSubmit: function(id, fileName){

                <?php if ($file_count != 0): ?>
                if ($item.find('.list .item:not(.removed)').length + $item.find('.qq_file_item:visible').length >= <?= $file_count;?>) {

                    if (<?= $file_count;?> == 1) {
                        this.showMessage("<?= __('appformitemfile.maximum_allowed_file_count_is_one');?>");
                    } else {
                        this.showMessage("<?= __('appformitemfile.maximum_allowed_file_count_is', array(':count' => $file_count));?>");
                    }

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



                    $item.find('.list').append($uploaded_file_preview);

                    //provede inicizalizaci zakladnich prvku na polozce
                    appFormItemPhoto_InitFancybox($item);
                    appFormItemPhoto_InitItem($uploaded_file_preview, $item, $form);

                    //change event tady je vyvolan aby se propagoval vyse -
                    //to je potreba napriklad pri pouziti na advanceditemlist
                    //kde prvek chyta change udalost aby poznal ze uzivatel uz
                    //zadal alespon neco a povoli mu pridat dalsi novou (prazdnou)
                    //polozku
                    $item.trigger('change');

                    // This is triggered only if file is added into files list
                    $item.trigger('fileAdded');
                }


                <?php if ($file_count != 0): ?>
                    // Hide drop area and upload btn if files limit was reached
                    if ($item.find('.list .item:not(.removed)').length + $item.find('.qq_file_item:visible').length >= <?= $file_count;?>) {
                        $item.find('.button').hide();
                    }
                <?php endif; ?>

                //the layout and dmensions of this form item may have changed
                $item.trigger('itemLayoutChanged', $item);
            },
            onCancel: function(id, fileName){

                //preview souboru, ktere bylo zobrazeno v prubehu uploadu zrusim
                //ze serveru se nacte specialni panel s preview fotky
                $item.find('.qq_file_item').each(function(){
                    if (this.qqFileId == id) {
                        $(this).hide();
                    }
                });


                <?php if ($file_count != 0): ?>
                // Hide drop area and upload btn if files limit was reached
                if ($item.find('.list .item:not(.removed)').length + $item.find('.qq_file_item:visible').length < <?= $file_count;?>) {
                    $item.find('.button').show();
                }
                <?php endif; ?>

            }

            //messages: {
            //    // error messages, see qq.FileUploaderBasic for content
            //},
        });

    });
});