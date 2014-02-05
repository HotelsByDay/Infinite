//<script>
/**
 *
 */
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemSimpleItemList';

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

                //v nastavenich ocekavam sablonu pro novy relacni prvek
                var new_template = options['new_template'];

                $_this.data('appformitemsimpleitemlist', {options : options});

                //inicializace polozek
                $_this.find('.list .simple_list_item').each(function() {
                    methods._initTemplate($_this, $(this));
                });

                //tlacitko pro pridani noveho zaznamu
                $_this.find(".add_new").click(function() {

                    var $new_template = $(new_template);
                    
                    $_this.appFormItemSimpleItemList('append');

                    return false;
                });

                // If there are items about to delete/add (after validation error) then highlight them
                $_this.find('input[name*="[id]"][value=""]').each(function(){
                    $(this).parents('.simple_list_item').addClass('added');
                });
                $_this.find('input[name*="[action]"][value="d"]').each(function(){
                    $(this).parents('.simple_list_item').addClass('deleted');
                });
                $_this.appFormItemSimpleItemList('updateInfoMessage');

            });
        },

        /**
         * Public metoda - prida polozku do seznamu, s tim, ze ji inicializuje hodnotami ve values
         * @param foo
         * @param values
         */
        append: function(foo, values) {
            var $_this = $(this);

            var data = $_this.data('appformitemsimpleitemlist');
            var options = data.options;

            // HTML kod sablony prvku
            var new_template = options['new_template'];
            // jQuery objekt sablony prvku
            var $new_template = $(new_template);

            $new_template.addClass('added');

            if (typeof values !== 'undefined') {
                // Inicializujeme sablonu zadanymi hodnotami
                for (var attr in values) {
                    $new_template.find('input[name*="[' + attr + ']"]').val(values[attr]);
                }
            }

            //template incializuju (obsauje tlacitko pro odstraneni
            methods._initTemplate($_this, $new_template);

            //pridam na konec seznam existujicich prvku
            $_this.find('.list').append($new_template);

            $_this.appFormItemSimpleItemList('updateInfoMessage');

            //nastavim focus na prvni viditelny input
            $new_template.find('input:visible,select:visible,textarea:visible').filter(':first').focus();
        },

        _initTemplate: function($_this, $template) {


            $template.find('.undelete').on('click', function(){
                $template.find('input[name*="[action]"]').val('s');
                $template.removeClass('deleted');
                $_this.appFormItemSimpleItemList('updateInfoMessage');
                return false;
            });

            //pri kliknuti na tlacitko odstranit
            $template.find('.delete').click(function(){

                //pridam classu, ktera prvek zvyrazni aby uzivatel videl
                //ktery soubor bude odstranen
                $template.addClass('to_be_deleted');

                if (true || confirm("<?= __('form.AppFormItemSimplteItemList.confirm_delete');?>")) {

                    //id polozky (souboru ) je ulozeno v inputu, ktery v name atributu obsahuej "[id]"
                    var $id_input = $template.find('input[name*="[id]"]');

                    //pokud $id_input ma prazdnou hodnotu, tak jeste dana polozka
                    //nebyla ulozena do DB a muzu rovnou odstranit z formulare

                    if ($id_input.length == 0 || $id_input.val() == '') {

                        //odstranim soubor ze stranky
                        $template.remove();

                        $_this.appFormItemSimpleItemList('updateInfoMessage');
                       //a ajax uz neni treba provadet
                        return false;
                    }

                    // Changed to never perform an AJAX call - items will be deleted after the form is saved
                    // #7624  [2014-01-29]
                    $template.find('input[name*="[action]"]').val('d');
                    $template.addClass('deleted');
                    $_this.appFormItemSimpleItemList('updateInfoMessage');

                    return false;
                    // End of Change


                    //zobrazim progress indicator nad polozkou souboru
                    $template.block({message: "<?= __('appformitemsimpleitemlist.delete_ptitle');?>"});

                    //vezmu si ID polozky (souboru)
                    var item_id = $id_input.val();

                    $.getJSON($(this).attr('href'), function(response_data){

                        if (typeof response_data['error'] !== 'undefined') {

                            //zrusim progress indicator
                            $template.unblock();

                            //zobrazim uzivateli text chyby
                            alert(response_data['error']);

                            //pri odstranovani zaznamu doslo k chybe - pocitam s tim
                            //ze soubor odstranen nebyl
                            $template.removeClass('to_be_deleted');
                        } else {
                            //zrusim progress indicator
                            $template.unblock();

                            //soubor byl uspesne odstranen - smazu jej ze stranky
                            $template.remove();
                        }

                    });

                } else {
                    //pokud nepotvrdil tak oznaceni odstranim
                    $template.removeClass('to_be_deleted');
                }

                return false;

            });

        },

        updateInfoMessage: function() {
            var $_this = $(this);
            if ($_this.find('.added, .deleted').length) {
                $_this.find('.save_info_message').show();
            } else {
                $_this.find('.save_info_message').hide();
            }
        },
        _log: function( text ) {
            if ( typeof console !== 'undefined') {
            //    console.log( text );
            }
        }

    };

    $.fn.appFormItemSimpleItemList = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {

            return methods[ method ].apply( this, arguments );

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.appFormItemSimpleItemList');

        }

        return this;

    };

})( jQuery );
