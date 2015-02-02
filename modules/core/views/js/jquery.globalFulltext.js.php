
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'globalFulltext';



    /**
     * Metody pro tento plugin.
     */
    var methods = {

        init: function( options ) {

            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
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

                //nabinduji udalost hash change aby GUI reagovalo na stisk tlacitka zpet
                $(window).bind( 'hashchange', function(e) {
                    //pokud neni nastaven zadny hash,
                    if (window.location.hash.length == 0) {
                        methods._restoreState($this, settings['params']);
                    } else {
                        methods._restoreState($this);
                    }
                 });
                 $(window).trigger('hashchange');
            });

        },

        _sendQuery: function( $this, parameters) {

                //pokud bezi nejaky predchozi pozadavek, tak ho zrusim
                var previous_request = methods._getData( $this, 'request' );
                if ( typeof previous_request !== 'undefined' ) {
                    previous_request.abort();
                    delete previous_request;
                }

                $data_content = $this.find('.data');
                //provedu zablokovani panelu s vysledky vyhledavani
                $data_content.block({
                    message: "<?= __('general.loading_table_data');?>"
                });

                var jqXHR = $._ajax({
                                url: "<?= appurl::fulltext_search_data();?>",
                                data: parameters,
                                success: function(data){
                                    //odblokuju UI
                                    $data_content.unblock();
                                    $this.html(data);
                                    methods._initContent($this);
                                },
                                error: function(jqXHR, textStatus){
                                    //odblokuju UI
                                    $data_content.unblock();
                                    //funkce isValidXHRError slouzi predevsim k detekci
                                    //chyby AJAXu zpusobene opustenim stranky
                                    if ($.isValidXHRError()) {
                                        //uzivateli bude zobrazena obecna chybova hlaska
                                        $.userUnexpectedErrorDialogMessage();
                                    }
                                }
                });

                //jqXHR objekt si ulozim abych mohl pozadavek zrusit pokud uzivatel
                //vyvola dalsi pred ukoncenim toho predchoziho
                methods._setData( $this, {
                    request: jqXHR
                });

        },

        _initContent: function( $this ) {

            $this.find('.object_button').click(function(){

                var params = $(this).attr('parameters');

                eval('params=' + params);

                methods._setState( $this, params );

                return false;
            });

        },

        _setState: function ( $_this , parameters ) {

            $.bbq.pushState( parameters );

        },

        _restoreState: function ( $_this , parameters ) {

            if (typeof parameters === 'undefined') {
                parameters = $.bbq.getState() || {};
            }

            methods._sendQuery( $_this, parameters);

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

    };

    $.fn.globalFulltext = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {

            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.globalFulltext');

        }

        return this;

    };

})( jQuery );

