// <script>
    

(function( $ ) {

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemObjectImageSelector';

   
    /**
     * Metody pro tento plugin.
     */
    var methods = {
        
        init: function( options ) {
            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
                image_edit_url: false
            };
            //Pokud prislo nastaveni, tak mergnu s defaultnimi hodnotami
            $.extend( settings, options );

            /**
             * this je nyni neco jako jQuery iterator - to co vratil selector
             * volanim each zajistime provedeni definovane funkce 
             * postupne v kontextu kazdeho z vybranych elementu */
            return this.each(function() {
                
                /**
                 * nyni je this pouze js objekt, z nej vytvorime jQuery objekt 
                 * s nazvem $this a dale pracujeme s nim - jedna se o uzel divu 
                 * okolo form item */
                var $this = $(this);


                // Div ve kterem jsou jednotlive obrazky
                var $images_placeholder = $(".images_preview", $this);

                // Skryty input pro json seznam obrazku - po jeho zmene dojde k pregenerovani obrazku
                var $images_list = $("input[name*='[images_list]']", $this);

                // Skryty div s ID aktualne zvoleneho obrazku
                var $selected_image = $("input[name*='[id]']");

                var $manage_link = $this.find('a.manage_images');

                var $rel_id = $this.find('input[name*="[rel_id]"]');


                /**
                 * Inicializace nahledu obrazku
                 */
                var initImagesPreviews = function()
                {
                    // Inicializujeme click event - fancybox
                    $(".image_preview a.zoom", $images_placeholder).fancybox({
                        hideOnOverlayClick: true,
                        hideOnContentClick: true,
                        speedIn: 200,
                        speedOut: 10,
                        titleShow: true,
                        titlePosition: 'inside'
                    });

                    $(".select a").on('click', function() {
                        // Odebereme selected class aktualne zvolenemu obrazku
                        $(".image_preview", $images_placeholder).removeClass('selected');
                        // Ulozime id nove zvoleneho obrazku
                        $selected_image.val($(this).parents('.image_preview').attr('image_id'));
                        // Pridame selected class aktulanimu divu - aby uzivatel videl ze je zvoleny
                        $(this).parents('.image_preview').addClass('selected');
                    });

                    // Pokud zadny preview neni pritomen, zobrazime hlasku
                    if ( ! $(".image_preview", $images_placeholder).length) {
                        $(".no_images", $this).show();
                    } else {
                        // Jinak hlasku skryjeme
                        $(".no_images", $this).hide();
                    }
                }


                var loadImagesFromInput = function()
                {
                    // Odstranime aktualne zobrazovane obrazky
                    $images_placeholder.children().remove();

                    // Smazeme id aktualne zvoleneho obrazku
                    $selected_image.val('');

                    // Ziskame pole obrazku pro nove zvoleny objekt
                    var images = $.parseJSON($images_list.val());

                    // Projdeme obrazky
                    for (var i in images) {
                        var image = images[i];
                        // Naklonujeme si preview element
                        var $preview = $(".image_preview_template", $this).clone();
                        // Zmenime jeho class
                        $preview.removeClass('image_preview_template').addClass('image_preview');
                        // Doplnime adresu obrazku
                        $('img', $preview).attr('src', image.url);
                        // Doplnime adresu zoomed obrazku a jeho title
                        $('a.zoom', $preview).attr('href', image.zoomed_url).attr('title', image.preview);
                        // Jeho popisek
                        $('.preview', $preview).html(image.preview);
                        // Nastavime jeho id do atributu
                        $preview.attr('image_id', image.id);
                        // Pridame obrazek do placeholderu
                        $images_placeholder.append($preview);
                        $preview.show();
                    }

                    initImagesPreviews();
                    // Vyprazdnime images_list input - JSON jiz nebudeme potrebovat
                    // a je zbytecne aby byl tento dlouhy string odesilan s daty formulare
                    // - tohle dela problemy pri reloadu stranky - autocomplete zustane vyplneny ale obrazky zmizi
                    // $images_list.val('');
                }


                $manage_link.click(function(){
                    var url = $(this).attr('href');
                    url = url.replace('_ID', $this.find('input[name*="[rel_id]"]').val());
                    var $dialog = $( document.createElement('div') ).addClass('obd-dialog_object')
                        .appendTo($('body'));

                    //defaultni parametry dialogu
                    var dialog_options = {
                        modal:true,
                        draggable:true,
                        autoOpen: false
                    };

                    //vezmu defaultni nastaveni dialogu z argumentu metody
                    var dialog_options = $.extend(dialog_options, settings['dialog']);

                    //inicializace jQuery dialogu
                    $dialog._dialog(dialog_options);

                    $dialog._dialog('loadForm', url, {}, function(response) {
                        if (response['action_status'] == '<?= AppForm::ACTION_RESULT_SUCCESS;?>') {
                            if (typeof (response.extra) != 'undefined' && typeof (response.extra.images_list) != 'undefined') {
                                $images_list.val(response.extra.images_list);
                                loadImagesFromInput();
                            }
                            $dialog._dialog('close');
                        }
                    });
                    return false;
                });


                // po zmene hodnoty v hidden inputu se seznamem obrazku se obrazky nactou podle nej
                $images_list.on('change', loadImagesFromInput);


                // pokud po nacteni stranky je input neprazdny (reload stranky)
                // pak take nacteme obrazky podle inputu
                if ($images_list.val() != '') {
                    loadImagesFromInput();
                } else {
                    // Jinak pouze inicializujeme inputy (vygenerovane na zaklade hodnot z db)
                    initImagesPreviews();
                }

                // Pokud je v inputu id zvoleneho obrazku - aktivujeme ho
                $("div[image_id='" + $selected_image.val() + "']", $images_placeholder).addClass('selected');

                var relObjectChanged = function(){
                    if ($rel_id.val()) {
                        $manage_link.show();
                    } else {
                        $manage_link.hide();
                    }
                }
                // Show/hide manage link if rel object is / is not selected
                $rel_id.change(relObjectChanged);
                relObjectChanged();


            });
        }
    };


    $.fn.AppFormItemObjectImageSelector = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemObjectImageSelector');
            
        }
        
        return this;

    };

})( jQuery );

