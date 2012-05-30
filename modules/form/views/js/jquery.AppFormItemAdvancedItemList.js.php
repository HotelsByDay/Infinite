//<script>
/**
 *
 */
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemAdvancedItemList';

    /**
     * Defaultni hodnoty pro parametry a nastaveni pluginu
     */
    var default_settings = {
    };

    /**
     * Metody pro tento plugin.
     */
    var methods = {

        init: function( options ) {

            this.each(function(){

                var $_this = $(this);

                //tlacitko pro pridani noveho zaznamu
                $_this.find(".add").click(function(){

                    //pokud naposledy pridana polozka jeste nebyla editovana a
                    //je tedy prazdna, tak uzivateli hodim focus na prvni input
                    //a nenecham ho pridavat dalsi prazdne polozky
                    if ($(this).data('new')) {

                        $.userInfoMessage("<?= __('appformitemadvanceditemlist.cannot_add_new_there_is_empty_item');?>");

                        var $item_container_ref = $(this).data('new');
                        $item_container_ref.find('input,textarea,select').filter(':first').focus();
                        return false;
                    }

                    //zpristupneni uvnitr callbacku ve funkci getJSON
                    var $add_button = $(this);
                    var $add_loader = $_this.find(".add_loader").show();

                    //tlacitka pro pridani schovam a zobrazim progress indicator
                    $add_button.hide();
                    $add_loader.show();

                    //do obalovaciho divu si nactu ajaxem formular
                    $.ajax({
                        type:'POST',
                        url:options['new_item_url'],
                        success: function(response){

                            //na klici 'content' ocekavam HTML kod, ktery predstavuje formulare
                            if (typeof response['content'] !== 'undefined')
                            {
                                //vytvori se novy obalovaci prvek pro novy formular
                                var $item_container = $( document.createElement('div') ).addClass('item');

                                //pridam na konec seznam existujicich prvku itemlistu
                                $_this.find('.list:first').prepend($item_container);

                                //aktualizuje se poradi prvku
                                if (options['sortable']) {
                                    var sortable_sequence_field = options['sortable'];
                                    var i = 0;
                                    $_this.find('.list .item').each(function(){
                                        $(this).find('input[name$="['+sortable_sequence_field+']"]').val(i++);
                                    });
                                }

                                //po pridani noveho prvku do stranky si poznacim ze
                                //na novem prvku jeste nedoslo k zadne zmene - change
                                //udalosti. V pripade ze uzivatel znovu klikne na
                                //pridat polozku tak hodim focus na prvni input
                                //v naposledy pridane polozce, protoze tu nechal
                                //prazdnou a nemelo by dojit k pridani nove jen tak
                                //"zbytecne".
                                $add_button.data('new', $item_container);

                                //v pripade change udalosti referenci na posledne
                                //pridany prvek zrusim a pri dalsim kliknuti na
                                //pridat dojde k pridani nove polozky do stranky
                                $item_container.change(function(){
                                    $add_button.data('new', false);
                                });

                                $item_container.html(response['content']);
                            }

                            //skryju progress indicator a zobrazim tlacitko pro
                            //pridani noveho prvku
                            $add_loader.hide();
                            $add_button.show();

                        },
                        error: function(){

                            //skryju progress indicator a zobrazim tlacitko pro
                            //pridani noveho prvku
                            $add_loader.hide();
                            $add_button.show();

                            //uzivatel bude zobrazeno chybove hlaseni a potom bude dialogove okno zavreno
                            $.userUnexpectedErrorDialogMessage();
                        },
                        dataType: 'json'
                    });

                    return false;
                });


                //inicializace sablony kazdeho prvku
                $(this).find('.list .item').each(function(){
                    methods._initTemplate($_this, $(this));
                });

                //ma fungovat serazeni prvku pomoci drag&drop ?
                if (options['sortable']) {
                    //inicializace razeni prvku
                    $(this).find('.list').sortable({
                        placeholder: "ui-state-highlight",
                        handle: ".drag_handler",
                        update: function (event, ui) {
                            //tento atribut slouzi k ulozeni poradi daneho prvku
                            var sortable_sequence_field = options['sortable'];
                            var i = 0;
                            $_this.find('.list .item').each(function(){
                                $(this).find('input[name$="['+sortable_sequence_field+']"]').val(i++);
                            });
                            //uzivateli se zobrazi info zprava - porad prvku bude
                            //zachovano jen kdyz se ulozi formular
                            $.userInfoMessage("<?= __('form.AppFormItemAdvancedItemlist.order_update.info_message');?>");
                        }
                    })
                    .disableSelection();
                }
            });
        },

        _initTemplate: function($_this, $item_container) {

            //pri kliknuti na tlacitko odstranit
            $item_container.find('.delete').click(function(){

                //trida, ktera oznaci prvek k odstraneni
                $item_container.addClass('to_be_deleted');
                
                if ($.confirm("<?= __('form.AppFormItemSimplteItemList.confirm_delete');?>")) {


                    if ($(this).attr('href') == '') {

                        $item_container.remove();

                    } else {

                        //ID prvku ktery ma byt odstranen
                        var item_id = $(this).attr('item_id');

                        //zablokuju dany prvek (formular)
                        $item_container.block({message: "<?= __('form.AppFormItemSimplteItemList.delete_ptitle');?>"});

                        $.getJSON($(this).attr('href'), {id:item_id}, function(response_data){

                            if (typeof response_data['error'] !== 'undefined') {

                                //zrusim progress indicator
                                $item_container.unblock();

                                //zobrazim uzivateli text chyby
                                alert(response_data['error']);

                                //pri odstranovani zaznamu doslo k chybe - pocitam s tim
                                //ze soubor odstranen nebyl
                                $item_container.removeClass('removed');
                            } else {
                                //zrusim progress indicator
                                $item_container.unblock();

                                //soubor byl uspesne odstranen - smazu jej ze stranky
                                $item_container.remove();
                            }

                        });
                    }


                    //timto bude prvek oznacen k odstraneni
//                    $template.find('input.type').val('d');

                    //inputy nastavim na readonly
//                    $template.find('input,select,textarea').attr('readonly', 'readonly');
                } else {
                    //pokud nepotvrdil tak oznaceni odstranim
                    $item_container.removeClass('to_be_deleted');
                }

                return false;
            });

        },

        _log: function( text ) {
            if ( typeof console !== 'undefined') {
                console.log( text );
            }
        }

    };

    $.fn.appFormItemAdvancedItemList = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {

            return methods[ method ].apply( this, arguments );

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.appFormItemAdvancedItemList');

        }

        return this;

    };

})( jQuery );
