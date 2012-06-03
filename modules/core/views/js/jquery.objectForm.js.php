//<script>
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'objectForm';

    /**
     * Defaultni hodnoty pro parametry a nastaveni pluginu
     */
    var settings = {
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

                settings = options;

                //ulozim si aktualni nastaveni pluginu
                methods._setData( $_this , {
                    settings: settings
                });

                //vytvorim si kopii defaultnich parametru - ta se pouzije v pripade
                //ze uzivatel se vrati na stav url kdy je prazdny hash (defaultni stav
                //po nacteni stranky)
                //var default_params = $.extend(true, {}, options['defaults']);

                //inicializace funkci formulare
                methods._initForm($_this);
                
            });
        },

        _initForm: function( $_this ) {

            //nactu si URL na kterou budu posilat pozadavek
            var settings = methods._getData( $_this, 'settings' );

            //inicializace close_banner tlacitka v banneru
            $_this.find('.close_banner').click(function(){
                //jako prvni odstranim cely banner
                $(this).parents('.banner:first').remove();
                //hned na to bude zobrazen vlastni formular
                $_this.find('form').show();
                
                return false;
            });

            //nabinduju akci na jednotlive tlacitka formulare
            $_this.find(".form_button").each(function(){

                $(this).click(function(e){

                    //obsluha 'confirm' atributu
                    if (typeof $(this).attr('confirm') !== 'undefined') {
                        if ( ! confirm($(this).attr('confirm'))) {
                            return false;
                        }
                    }

                    //prectu aktualni formularova data
                    var form_data = $_this.find('form').serialize();

                    //pripojim identifikaci stisknuteho formularoveho tlacitka
                    form_data += '&'+$(this).attr('name')+'='+$(this).val();

                    //odeslani formulare
                    methods._submitForm($_this, form_data, $(this).attr('ptitle'));

                    //prevents default action
                    e.preventDefault();
                    return false;
                });
            
            });

            //tlacitko pro zavreni formulare pouze stiskne tlacitko zpet
            $_this.find('.<?= AppForm::FORM_BUTTON_CLOSE_CSS_CLASS;?>').click(function(){
                //pokud je definovany handler explicitne pres parametry pluginu,
                //tak jej vyvolam, jinak se provede defaultni akce
                if (typeof settings !== 'undefined' && typeof settings['onCloseButtonClick'] === 'function') {
                    return settings['onCloseButtonClick']();
                } else {
                    window.history.back();
                    return false;
                }
            });

            //pokud je na formulari definovana custom inicializacni funkce,
            //tak to muze byt pomoci input[type=hidden][name=_init_function],
            //jehoz hodnota je povazovana za funkci, ktera ma byt vyvolana
            //po inicializaci formulare

            if ($_this.find('input[name="_init_function"]').length != 0)
            {
                var fname = $_this.find('input[name="_init_function"]').val();

                //pokud dojde k chybe, tak je v tichosti zachycena
                try
                {
                    eval(fname+'();');
                }
                catch (e)
                {

                }
            }

            //inicializace tooltip napovedy u prvku
            $_this.find(".tooltip").each(function(){

                //aby se nemuselo opakovane volat $(this)
                $tooltip_widget = $(this);

                $tooltip_widget.qtip({
                    content: {
                        text: $tooltip_widget.find('.content').html()
                    },
                    position: {
                        my: $tooltip_widget.attr('position_my'),
                        at: $tooltip_widget.attr('position_at')
                    },
                    show: {
                        //vzdy pouze jeden qtip ve strance
                        solo: true
                    },
                    hide: {
                        //pri mouseleave udalosti na cilovem prvku skryt qtip
                        target: $(this),
                        event: 'mouseleave'
                    }
                });
            });
        },

        _submitForm: function( $_this, form_data, progress_indicator_message) {

            //zablokuju UI
            $_this.block({message:progress_indicator_message});

            //pokud nebyl jeste zpracovan minuly pozadavek, tak dalsi nebude odeslan
            var previous_request = methods._getData( $_this, 'request' );
            
            if ( typeof previous_request !== 'undefined' && previous_request.readyState != 4) {
                return;
            }

            //nactu si URL na kterou budu posilat pozadavek
            var settings = methods._getData( $_this, 'settings' );

            var jqXHR = $.ajax({
                type:'POST',
                url:$_this.find('form').attr('action'),
                data: form_data,
                success: function(response){

                    //pokud ze serveru prisel novy obsah, tak jej vlozim do formulare
                    if (typeof response['content'] !== 'undefined') {

                        //aktualizuji hlavni nadpis formulare
                        if (typeof response['headline'] !== 'undefined') {
                            $_this.find('.content-title h1').html(response['headline']);
                        }

                        $_this.find('.form_content').html(response['content']);

                        methods._initForm($_this);
                    }

                    //pri uspechu zobrazim pouze informacni hlaseni, ktere nevyzaduje reakci uzivatele
                    if (typeof response['action_status'] !== 'undefined' ) {

                        if (response['action_status'] == '<?= AppForm::ACTION_RESULT_SUCCESS;?>') {
                            //zobrazi zpravu pro uzivatele
                            $.userInfoMessage(response['action_result']);

                            //pokud je definovan callback pro pripad uspesneho ulozeni,
                            //tak jej zavolam

                            if (typeof settings !== 'undefined' && typeof settings['onActionResultSuccess'] === 'function') {

                                settings['onActionResultSuccess'](response);

                            }

                        //pokud doslo k validacni chybe, tak uzivatele posunu
                        } else if (response['action_status'] == '<?= AppForm::ACTION_RESULT_FAILED;?>') {

                            //schovam zpravu informujici o uspesnem ulozeni - ta muze
                            //byt v tuto chvili zobrazena
                            $.userInfoMessage(false);

                            //pokud je okno nascrollovano tak ze neni plne videt blok s hlaskou
                            //o neuspesnem ulozeni formulare (je tedy nascrollovano nize)
                            //tak posunu okno nahoru

                            //vyska na ktere je umisten blok informujici uzivatele
                            //o neuspesnem ulozeni
                            if ($(".form_action_result_failed:first").length != 0) {
                                var wanted_top = $(".form_action_result_failed:first").offset().top;

                                if ($(window).scrollTop() > wanted_top) {
                                    $(window).scrollTop(wanted_top);
                                }
                            }
                        }

                    }

                    //pokud je definovana URL na kterou ma byt uzivatel presmerovan
                    //tak dojde k presmerovani
                    if (typeof response['redir'] !== 'undefined') {
                        window.location = response['redir'];
                    }

                    //odblokuju UI
                    $_this.unblock();
                    
                },
                error: function(){
                    //funkce isValidXHRError slouzi predevsim k detekci
                    //chyby AJAXu zpusobene opustenim stranky
                    if ($.isValidXHRError(jqXHR)) {
                        //uzivateli bude zobrazena obecna chybova hlaska
                        $.userUnexpectedErrorDialogMessage();
                    }

                    //odblokuju UI
                    $_this.unblock();

                },
                dataType: 'json'
            });

            //objekt reprezentujici pozadavek si ulozim - kdyby uzivatel odeslal
            //formular znovu pred prijetim odpovedi, tak novy pozadavek zrusim
            methods._setData( $_this, {
                request: jqXHR
            });

        },

        loadEditation: function( $this, item_id ) {

            $_this = this;

            //zablokuju UI
            $_this.block();

            //pokud nebyl jeste zpracovan minuly pozadavek, tak dalsi nebude odeslan
            var previous_request = methods._getData( this, 'request' );

            if ( typeof previous_request !== 'undefined' && previous_request.readyState != 4) {
                return;
            }

            var jqXHR = $.ajax({
                type:'POST',
                url:$_this.find('form').attr('action'),
                data: {_id: item_id},
                success: function(response){

                    //pokud ze serveru prisel novy obsah, tak jej vlozim do formulare
                    if (typeof response['content'] !== 'undefined') {

                        //aktualizuji hlavni nadpis formulare
                        if (typeof response['headline'] !== 'undefined') {
                            $_this.find('.content-title h1').html(response['headline']);
                        }

                        $_this.find('.form_content').html(response['content']);

                        methods._initForm($_this);
                    }

                    //odblokuju UI
                    $_this.unblock();

                },
                error: function(){
                    //funkce isValidXHRError slouzi predevsim k detekci
                    //chyby AJAXu zpusobene opustenim stranky
                    if ($.isValidXHRError(jqXHR)) {
                        //uzivateli bude zobrazena obecna chybova hlaska
                        $.userUnexpectedErrorDialogMessage();
                    }

                    //odblokuju UI
                    $_this.unblock();

                },
                dataType: 'json'
            });

            //objekt reprezentujici pozadavek si ulozim - kdyby uzivatel odeslal
            //formular znovu pred prijetim odpovedi, tak novy pozadavek zrusim
            methods._setData( $_this, {
                request: jqXHR
            });
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
        }

    };

    $.fn.objectForm = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {

            return methods[ method ].apply( this, arguments );

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.objectForm');

        }

        return this;

    };

})( jQuery );

