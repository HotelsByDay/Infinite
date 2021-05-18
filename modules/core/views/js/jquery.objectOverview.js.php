//<script>
/**
 * Tento plugin zajistuje funkci filtru pro nacitani dat na poradaci.
 *
 * Na konci pluginu je kod, ktery provadi jeho inicializaci, takze staci do stranky
 * tento plugin pouze vlozit.
 *
 * Ocekava ze bude spusteno na prvku typu div, ktery bude obsahovat
 * prvek typu "form" s atributem 'action' kde musi byt URL, na kterou budou
 * odesilany pozadavky pro nacteni dat.
 * Uvnitr elementu nad kterym je plugin spusten vyhledava prvek(ky) s css tridou
 * 'submit_filter', ktery/e vyvolaji nacteni dat.
 * Prvek s css tridou 'reset_filter' provede vyresetovani filtru do jeho defaultniho
 * stavu.
 * Dale plugin ocekava ze ve strance je prvek, ktery vyhovuje tomuto selectoru:
 * "#data_table_container", ktery obsahuje data odpovidajici nastavenemu filtru.
 * V pripade spusteni noveho filtrovani plugin nad timto prvkem aktivuje progress
 * indicator a po obdrzeni korektni odpovedi provede replacnuti obsahu prvku.
 *
 *
 */
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'objectOverview';

    /**
     * Defaultni hodnoty pro parametry a nastaveni pluginu
     */
    var settings = {
        submenu_item_selector: 'ul .submenu_item',
        subcontent_selector: '.overview_subcontent',
        use_hash: true,
        overview_header_refresh_url: ''
    };

    /**
     * Metody pro tento plugin.
     */
    var methods = {

        init: function( options ) {

            this.each(function() {

                var $_this = $(this);

                $.extend(settings, options);

                //ulozim si aktualni nastaveni pluginu
                methods._setData( $_this , {
                    settings: settings
                });

                //budu odchytavat kliknuti na polozky submenu
                $(settings.submenu_item_selector, $_this).click(function(e) {
                    methods._activateSubmenuItem( $_this, $(this) );

                    return false;
                });

                // On overview_header_refresh event reload overview header
                $_this.on('overview_header_refresh', function() {
                    // Keep active item active
                    var active_item_id = $_this.find('ul>li.active a.submenu_item').attr('id');
                    $_this.find('.overview_header').load(settings.overview_header_refresh_url, function(){
                        $_this.find('#' + active_item_id).parents('li:first').addClass('active');
                    });

                });

                //na overview strance ocekavam nektere standardni prvky:

//                //prvek pro zmenu statusu zaznamu
//                $(".attr_edit_bar", $_this).each(function(){
//
//                    //inicializace buttonsetu
//                    $buttonset = $(this).buttonset();
//
//                    //nazev atribut, jehoz hodnota ma byt upravena
//                    var attr_name = $(this).attr('name');
//
//                    //odchyceni udalosti zmeny hodnoty
//                    $('input[type=radio]', $(this)).change(function(){
//                        //disablovani buttonsetu
//                        $buttonset.buttonset('disable');
//
//                        //uzivatelem zvolena hodnota
//                        var value = this.value;
//
//                        //odeslani hodnoty na servery
//                        $.getJSON(settings.status_change_url, {value:value, attr:attr_name}, function(){
//
//                            //enablovani buttonsetu
//                            $buttonset.buttonset('enable');
//                        });
//                    });
//                });

                if ( ! settings.use_hash) {

                    //vybere defaultni polozku submenu a nacte jeji obsah
                    var $default_item = $(settings.submenu_item_selector + '.default:first', $_this);

                    //pokud neni defaultni ani jedna, tak vezme proste prvni
                    if ($default_item.length == 0) {
                        $default_item = $(settings.submenu_item_selector + ':first', $_this);
                    }

                    //pokud je definovana defaultni polozka, tak ji aktivuji - a tedy
                    //nactu prislusny obsah do subcontent casti

                    if ( $default_item.length != 0 ) {
                        methods._activateSubmenuItem($_this, $default_item);
                    }

                } else {
                    //nabinduji udalost hash change aby GUI reagovalo na stisk tlacitka zpet
                    $(window).bind( 'hashchange', function(e) {
                        //pokud neni nastaven zadny hash,
                        if (window.location.hash.length == 0) {

                            //vybere defaultni polozku submenu a nacte jeji obsah
                            var $default_item = $(settings.submenu_item_selector + '.default:first', $_this);

                            //pokud neni defaultni ani jedna, tak vezme proste prvni
                            if ($default_item.length == 0) {
                                $default_item = $(settings.submenu_item_selector + ':first', $_this);
                            }

                            //pokud je definovana defaultni polozka, tak ji aktivuji - a tedy
                            //nactu prislusny obsah do subcontent casti

                            if ( $default_item.length != 0 ) {
                                methods._restoreState($_this, $default_item);
                            }

                        } else {
                            methods._restoreState($_this);
                        }
                    });

                    $(window).trigger('hashchange');
                }

            });
        },

        _activateSubmenuItem: function( $_this, $item ) {
            
            var url = $item.attr('action');

            //nazev - ten bude zapsan do window.location.hash
            var name = $item.attr('id').substr(8);

            //pokud je atriut href nedefinovan nebo prazdny tak
            //provedu zapis do logu a nic se neprovede
            if (typeof url === 'undefined' || url == '') {
                methods._log('Undefined href on submenu item.');
            }

            //nactu si URL na kterou budu posilat pozadavek
            var settings = methods._getData( $_this, 'settings' );



            if ( ! settings.use_hash) {
                methods._loadSubcontent($_this, url);
            } else {
                methods._setState( $_this, name );
            }

            //oznaceni aktivni polozky
            $(settings.submenu_item_selector, $_this).parent('li').removeClass('active');
            $item.parent('li:first').addClass('active');

        },

         _loadSubcontent: function( $_this, url ) {

            //nactu si URL na kterou budu posilat pozadavek
            var settings = methods._getData( $_this, 'settings' );

            //pokud existuje predchozi pozadavek, tak ho zrusim a potom
            //vytvorim novy
            var previous_request = methods._getData( $_this, 'request' );
            if ( typeof previous_request !== 'undefined' ) {
                previous_request.abort();
                delete previous_request;
            }

            //nactu si jQuery objekt pro subcontent - cast do ktere nahravam novy obsah
            var $subcontent = $( settings.subcontent_selector , $_this );

            //zobrazim progress indicator a odesilam pozadavek
            $subcontent.block({
                message: "<?= __('general.blockUI_loading_overview_subcontent');?>"
            });

            //udelam si ajax object pro poslani post pozadavku
            jqXHR = $._ajax( url, {
                                        type: 'POST',
                                        data: {},
                                        success: function( response_data , textStatus, jqXHR){
                                            if ( typeof response_data['html'] !== 'undefined' ) {
                                                //do data containeru vlozim data - navic si udelam referenci
                                                $subcontent.html(response_data['html']);
                                                //skryju progress indicator, odblokuje UI
                                                $subcontent.unblock();
                                            }
                                        },
                                        error: function( jqXHR, textStatus, errorThrown ) {
                                            //funkce isValidXHRError slouzi predevsim k detekci
                                            //chyby AJAXu zpusobene opustenim stranky
                                            if ($.isValidXHRError(jqXHR)) {
                                                //uzivateli bude zobrazena obecna chybova hlaska
                                                $.userUnexpectedErrorDialogMessage();
                                            }
                                            //odblokovani UI
                                            $subcontent.unblock();
                                            //smazu nepotrebny jqXHR objekt
                                            methods._setData( $_this, {request: undefined});
                                            methods._log('Request failed with error "' + textStatus + '".');
                                        },
                                        dataType: 'json'
            });

            //objekt reprezentujici pozadavek si ulozim, kdyby uzivatel kliknul
            //na jinou polozku submenu pred dokoncenim aktualniho pozadavku,
            //tak ho stopnu (metodou abort()) pred spusenim noveho pozadavku
            methods._setData( $_this, {
                request: jqXHR
            });
            
        },

        /**
         * Uklada aktualni stav filtru do window.location.hash.
         *
         * Z aktualniho stavu filtru uklada kompletni obsah polozky 'request_params'
         * ktera je ulozena v datech teto instance.
         *
         */
        _setState: function ( $_this , active_submenu_item_name ) {

            $.bbq.pushState( '#' + active_submenu_item_name );
        },

        /**
         * Returns  current overview state (current tab name)
         * @param $_this
         * @return {*}
         */
        getState: function( $_this ) {
            var state = $.bbq.getState();
            for (var i in state)
            {
                return i;
            }
        },
        _restoreState: function ( $_this , $item) {
            // If called from the outside
            if ($_this == '_restoreState') {
                $_this = $(this);
            }

            if (typeof $item === 'undefined') {
                var params = $.bbq.getState() || {};

                //i kdyz pushState volam s retezcem tak getState vraci objekt, kde
                //dany retezec je prvnim klicem, tak si ho takto jednoduse vytahnu
                //(asi to neni nejefektivnejsi zpusob)

                for (key in params) {
                    active_submenu_item_name = key;
                    break;
                }

                //tato polozka menu ma byt aktivovana
                $item = $('#submenu_' + active_submenu_item_name);
            }

            //nactu si URL na kterou budu posilat pozadavek
            var settings = methods._getData( $_this, 'settings' );

            //oznaceni aktivni polozky
            $(settings.submenu_item_selector, $_this).parent('li').removeClass('active');
            $item.parent('li:first').addClass('active');

            //nacte obsah subcontent casti
            methods._loadSubcontent( $_this, $item.attr('action') );

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

            return current_data[ key ];

        },

        _log: function( text ) {
            if ( typeof console !== 'undefined') {
                console.log( text );
            }
        }

    };

    $.fn.objectOverview = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {

            return methods[ method ].apply( $(this), arguments );

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.objectFilter');

        }

        return this;

    };

})( jQuery );

