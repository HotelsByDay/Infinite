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

                //inicializace polozek
                $_this.find('.list .item').each(function(){
                    methods._initTemplate($_this, $(this));
                });

                //tlacitko pro pridani noveho zaznamu
                $_this.find(".add_new").click(function(){

                    $new_template = $(new_template);
                    
                    //template incializuju (obsauje tlacitko pro odstraneni
                    methods._initTemplate($_this, $new_template);

                    //pridam na konec seznam existujicich prvku
                    $_this.find('.list').append($new_template);

                    //nastavim focus na prvni viditelny input
                    $new_template.find('input:visible,select:visible,textarea:visible').filter(':first').focus();

                    return false;
                });

            });
        },

        _initTemplate: function($_this, $template) {
            
            //pri kliknuti na tlacitko odstranit
            $template.find('.delete').click(function(){

                //pridam classu, ktera prvek zvyrazni aby uzivatel videl
                //ktery soubor bude odstranen
                $template.addClass('to_be_deleted');

                if (confirm("<?= __('form.AppFormItemSimplteItemList.confirm_delete');?>")) {

                    //id polozky (souboru ) je ulozeno v inputu, ktery v name atributu obsahuej "[id]"
                    var $id_input = $template.find('input[name*="\[id\]"]');

                    //pokud nebyl input nalezen, tak nelze se souborem pracovat
                    if ($id_input.length == 0) {
                        alert("<?= __('appformitemfile.cannot_delete');?>");
                        return false;
                    }

                    //pokud $id_input ma prazdnou hodnotu, tak jeste dana polozka
                    //nebyla ulozena do DB a muzu rovnou odstranit z formulare
                    if ($id_input.val() == '') {

                        //odstranim soubor ze stranky
                        $template.remove();

                       //a ajax uz neni treba provadet
                        return false;
                    }

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
