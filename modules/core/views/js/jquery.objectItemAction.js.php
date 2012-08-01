(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'objectItemAction';

    /**
     * Defaultni hodnoty pro parametry a nastaveni pluginu
     */
    var default_settings = {
    };

    /**
     * Zde bude ulozena reference na vytvoreny dialog, ktery se bude pouzivat
     * pro ruzne akce - nebude se vytvaret vice instanci.
     */
    var $dialog = null;

    /**
     * Metody pro tento plugin.
     */
    var methods = {

        init: function( options ) {

            this.each(function(){

                var $_this = $(this);

                //provedu inicializaci tlacitek pro vyvolani akci
                $_this.find('.action_button').click(function(e){

                    //nazev akce, kterou budu vyvolavat
                    var action = $(this).attr('action');

                    var selected = methods._getSelectedItems();

                    methods._requestAction($_this, options['action_url'], action, selected);

                    e.preventDefault();
                    return false;
                });
            });
        },

        _requestAction: function($_this, action_url, action, selected){

            var params = {};

            //parametry pro POST pozadavek (bude obsahovat nazev akce,
            //a ID prvku nad kterymi ma byt provedena)
            if (typeof action !== 'undefined' && typeof selected !== 'undefined') {
                params = {
                    a:action,
                    i:selected
                };
            }

            var jqXHR = $._ajax({
                type: 'POST',
                url: action_url,
                data: params,
                success: function(response){

                    //vyvolam nove nacteni dat na tabulkovem vypisu
                    $("#main_data_filter").objectFilter('refresh');

                    //na klici 'html' ocekavam HTML kod, ktery uzivateli prezentuje
                    //vysledek provedene akce. V danem html kodu hledam tlacitko ".undo"
                    //ktere slouzi k vraceni akce zpet - po kliknuti vyvolavam dalsi ajax pozadavek

                    if (typeof response['html'] !== 'undefined') {
                        $_this.find('.result_placeholder').html(response['html']);

                        //inicializace funkce tlacitka "zpet"
                        $_this.find('.result_placeholder .undo').click(function(){
                        
                            methods._requestAction( $_this , $(this).attr('href') , undefined, undefined );

                            $_this.find('.result_placeholder').empty();

                            return false;

                        });
                    }

                },
                error: function(response){
                    //funkce isValidXHRError slouzi predevsim k detekci
                    //chyby AJAXu zpusobene opustenim stranky
                    if ($.isValidXHRError(jqXHR)) {
                        //uzivateli bude zobrazena obecna chybova hlaska
                        $.userUnexpectedErrorDialogMessage();
                    }
                },
                dataType:'json'
            });

        },

        _getSelectedItems: function(){

            var list = '';

            $("input.item:checkbox:checked").each(function(){
                list += $(this).attr('item_id') + ',';
            });

            return list;
        },
        

        /**
         *
         */
        _setData: function( $this, key, value ) {

            if (typeof key === 'object' ) {
                var data = $this.data( plugin_name_space )
                if ( typeof data === 'undefined' ) {
                    data = {};
                }
                //budu extendovat to co mam aktualne v datech ulozene
                $.extend( data, key );

                $this.data( plugin_name_space, data );
            } else {

                var current_data = $this.data( plugin_name_space );

                if (typeof current_data === 'undefined' ) {
                    current_data = {
                        key: value
                    };
                } else {
                    current_data[key] = value;
                }

                $this.data( plugin_name_space, current_data )

            }

        },

        /**
         *
         */
        _getData: function( $this, key ) {

            var current_data = $this.data( plugin_name_space );

            if (typeof current_data === 'undefined') {
                return undefined;
            }

            if (typeof key === 'undefined') {
                return current_data;
            }

            return current_data[ key ];

        },

        _log: function( text ) {
            if ( typeof console !== 'undefined') {
                console.log( text );
            }
        },

        // VEREJNE ROZHRANI //
        refresh: function() {

            methods._updateState( $(this) );

        }


    };

    $.fn.objectItemAction = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {

            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.objectItemAction');

        }

        return this;

    };

})( jQuery );
