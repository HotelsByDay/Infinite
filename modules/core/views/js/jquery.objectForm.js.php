//<script>
(function( $ ) {

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'objectForm';

    /**
     * Defaultni hodnoty pro parametry a nastaveni pluginu
     */
    var settings = {
        enabled_languages: {}
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

            this.each(function() {

                var $_this = $(this);

                var autosaveTimer = false;

                // Pridam formulari jeho css tridu
                methods._log('objectForm init - adding form className');
                $_this.addClass('<?= AppForm::FORM_CSS_CLASS ?>');

                settings = options || {};

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

                //inicializace funkce autosave - pri detekci 'change' na urovni formulare
                //dojde automaticky k ulozeni
                if (typeof settings['autosave'] !== 'undefined' && settings['autosave'] !== false) {
                    $_this.change(function() {
                        //prectu aktualni formularova data
                        var form_data = $_this.find('form').serialize();

                        //pripojim identifikaci stisknuteho formularoveho tlacitka
                        form_data += '&<?= Core_AppForm::ACTION_KEY;?>=<?= Core_AppForm::ACTION_SAVE;?>';

                        //odeslani formulare
                        methods._submitForm($_this, form_data, (typeof settings['autosave'] === 'string' ? settings['autosave'] : "<?= __('form_action_button.update_ptitle');?>"));
                    });
                }

                //inicializace funkce autosave - pri detekci 'change' na urovni formulare
                //dojde automaticky k ulozeni
                if (typeof settings['autosave_delay'] !== 'undefined' && settings['autosave_delay'] !== false) {
                    var processAutosave = function() {
                        //prectu aktualni formularova data
                        var form_data = $_this.find('form').serialize();

                        //pripojim identifikaci stisknuteho formularoveho tlacitka
                        form_data += '&<?= Core_AppForm::ACTION_KEY;?>=<?= Core_AppForm::ACTION_SAVE;?>';

                        // Pokud je timeout jiz nastaven - zrusieme ho
                        if (autosaveTimer !== false) {
                            clearTimeout(autosaveTimer);
                            autosaveTimer = false;
                        }
                        // nastavime novy timeout
                        autosaveTimer = setTimeout(function(){
                            methods._submitForm($_this, form_data, (typeof settings['autosave'] === 'string' ? settings['autosave'] : "<?= __('form_action_button.update_ptitle');?>"));
                        }, settings['autosave_delay']);
                    };

                    // Autosave timer se musi spustit/zrusit na change i changing udalost
                    $_this.change(processAutosave);
                    $_this.objectForm('subscribeEvent', 'changing', processAutosave);

                }

            });
        },

        /**
         * Zapina v vypina stickyness panelu s tlacitky formulare
         * @param $_this
         * @param toggle
         * @private
         */
        _toggleControlPanelSticky: function( $_this , toggle ) {
            if (toggle) {
                var current_left  = $_this.find(".form_control_panel_wrapper").offset().left;
                var current_width = $_this.find(".form_control_panel_wrapper").width();

                $_this.find(".form_control_panel_wrapper").addClass('sticky')
                                                          .find('.form_control_panel_content')
                                                          .css('left', current_left)
                                                          .css('width', current_width);
            } else {
                $_this.find(".form_control_panel_wrapper").removeClass('sticky')
                                                          .find('.form_control_panel_content')
                                                          .css('left', '')
                                                          .css('width', '');
            }
        },

        /**
         * Provadi inicializaci formulare.
         * @param $_this
         * @private
         */
        _initForm: function( $_this ) {

            //nactu si URL na kterou budu posilat pozadavek
            var settings = methods._getData( $_this, 'settings' );

            // Inicializace event listeneru
            $('input[type="text"], input[type="password"], textarea', $_this).bind('keyup', function(event){
                $_this.objectForm('fireEvent', 'changing', event);
            });
            $_this.bind('change', function(event){
                $_this.objectForm('fireEvent', 'change', event);
            });


            if (typeof $.waypoint !== 'undefined') {
                $_this.find(".form_control_panel").waypoint('destroy');
            }
            //inicializace floating panelu s tlacitky formulare
            if (typeof settings['float_control'] !== 'undefined' && settings['float_control'] === true) {

                //interval between checking the scroll position
                $.waypoints.settings.scrollThrottle = 30;
                $_this.find(".form_control_panel").waypoint(function(event, direction) {
                    methods._toggleControlPanelSticky($_this, direction == 'up');

                    event.stopPropagation();

                    },{offset: 'bottom-in-view'});

                //pokud neni panel s tlacitky po inicializaci formulare viditelny, tak bude prepnut na sticky
                var below_window_bottom = ! (($_this.find('.form_control_panel').parent().offset().top) < ($(window).scrollTop() + $(window).height()));

                if (below_window_bottom) {
                    methods._toggleControlPanelSticky($_this, true);
                }

                //na window resize se musim pozice a sirka floating panleu spocitat znovu
                $(window).resize(function() {
                    if ($_this.find(".form_control_panel_wrapper").hasClass('sticky')) {
                        //toggle vypnu , tim ziska svoji originalni pozici a sirku
                        //a potom znovu inicializuju - floating panel dostane
                        //levou pozici a sirku podle aktualniho stavu formulare
                        methods._toggleControlPanelSticky($_this, false);
                        methods._toggleControlPanelSticky($_this, true);
                    }
                });
            }

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

    // ================================= Lang Switching panels =======================================


            var active_language = null;

            var getDefaultActiveLanguage = function()
            {
                // @todo - load value from cookie or return first enabled language code
                return 'en';
            }


            /**
             * EventHandler called after lang switch button is clicked
             * @param event
             */
            var switchButtonClicked = function(event)
            {
                // clicked button
                var $button = $(this);
                // Trigger activeLocaleChanged event
                // - this can be detected in special lang items
                // - this is detected here to switch language in all form panels
                $button.trigger('activeLocaleChanged', $button.attr('data-locale'));
            }

            /**
             * (Re)populate languages switching panel
             * @param $switch
             */
            var setSwitchLanguages = function($switch, enabled_languages)
            {
                var $list = $switch.find('ul');
                $list.html('');
                for (var locale in enabled_languages) {
                    var label = enabled_languages[locale];
                    var $item = $(document.createElement('li'));
                    $item
                        .html('<a href="javascript: ;"><span>' + label + '</span></a>')
                        .addClass('locale_' + locale)
                        .attr('data-locale', locale)
                        .bind('click', switchButtonClicked)
                        ;
                    $list.append($item);
                }
            }


            var setSwitchActiveLanguage = function($switch, active_locale)
            {
                $switch.find('li').removeClass('active');
                $switch.find('li.locale_'+active_locale).addClass('active');
            }



            // Get all alng switching panel from the form
            var $lang_switch_panels = $_this.find('.lang_switch');

            active_language = getDefaultActiveLanguage();

            // Init panels with languages from settings
            $lang_switch_panels.each(function(){
                var $switch = $(this);
                setSwitchLanguages($switch, settings.enabled_languages);
                setSwitchActiveLanguage($switch, active_language);
            });


            // If active locale is changed via any of lang switch panels this event is triggered
            $_this.bind('activeLocaleChanged', function(event, active_locale) {
                $lang_switch_panels.each(function() {
                    setSwitchActiveLanguage($(this), active_locale);
                });
            });

            // If enabled locales are changed - we need to regenerate all switches
            $_this.bind('languagesChanged', function(event, enabled_languages) {
                $lang_switch_panels.each(function() {
                    setSwitchLanguages($(this), enabled_languages);
                });
            });



    // ================================= END of Lang Switching panels ================================
        },

        _submitForm: function( $_this, form_data, progress_indicator_message) {

            // Vypalime beforeSave event
            $_this.objectForm('fireEvent', 'beforeSave');

            //zablokuju UI
            $_this.block({message:progress_indicator_message});

            //pokud nebyl jeste zpracovan minuly pozadavek, tak dalsi nebude odeslan
            var previous_request = methods._getData( $_this, 'request' );
            
            if ( typeof previous_request !== 'undefined' && previous_request.readyState != 4) {
                return;
            }

            //nactu si URL na kterou budu posilat pozadavek
            var settings = methods._getData( $_this, 'settings' );

            var jqXHR = $._ajax({
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

            var jqXHR = $._ajax({
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

        fireEvent: function($_this, eventName, params)
        {
            var $this = $(this);
            methods._log('fireEvent - Appform id is: '+$this.attr('id'));
            methods._log('fireEvent called with enventname: '+eventName);
            // Get all events subscriptions
            var data = methods._getData($this, 'subscriptions') || {};
            methods._log('typeof data['+eventName+']: ' + typeof data[eventName]);
            // Call all stored callbacks
            if (typeof data[eventName] !== 'undefined') {
                methods._log('data[eventName] is set');
                for (var i in data[eventName]) {
                    methods._log('processing callback');
                    // Call stored callback with given params
                    var callback = data[eventName][i];
                    if (typeof callback == 'function') {
                        methods._log('callback is a function - calling it');
                        callback(params);
                        methods._log('callback has been called');
                    }
                }
            }

            if (eventName == 'itemLayoutChanged') {
                //if layout of any item has changed, then the layout and dimensions of the entire form
                //may have changed and therefore we need to recalculate all waypoints - the waypoint
                //are for example used for the float_control option
                if (typeof $.waypoints !== 'undefined') {
                    $.waypoints("refresh");
                }
            }
        },

        subscribeEvent: function(foo, eventName, callback)
        {
            var $this = $(this);
            methods._log('subscribeEvent - Appform id is: '+$this.attr('id'));
            methods._log('subscribeEvent called with enventname: '+eventName);
            // Get all events subscriptions
            var data = methods._getData($this, 'subscriptions');
            if (typeof data == 'undefined') {
                data = {};
            }
            if (typeof data[eventName] == 'undefined') {
                methods._log('subscribeEvent preparing array for event: '+eventName);
                data[eventName] = [];
            }
            // Add callback into event name subscribers list
            data[eventName][data[eventName].length] = callback;
            methods._setData($this, 'subscriptions', data);
            methods._log('subscribeEvent callback has been added');
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

