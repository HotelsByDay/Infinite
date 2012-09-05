// <script>
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemLangStringPanelSlave';

    /**
     * Metody pro tento plugin.
     */
    var methods = {
        
        init: function( options ) {
            
            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
                placeholders: {},
                attr: ''
            };
            
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

                // Najdeme parent formular
                var $form = $this.parents(".<?= AppForm::FORM_CSS_CLASS ?>:first");

                //Pokud prislo nastaveni, tak mergnu s defaultnimi hodnotami
                var params = $.extend(true, settings, options);

                /*
                var dump = function(obj, prefix) {
                    var s = '';
                    for (var i in obj) {
                        var value = obj[i];
                        s += prefix + i + ': ' + value + typeof value + "\n";
                        if (typeof value == 'object') {
                            s += dump(value, prefix + '  ');
                        }
                    }
                    return s;
                }

                $("body").prepend("<pre>" + dump(params, '') + "</pre>");
                */

                // Input ve kterem se zobrazuje aktivni preklad
                var $visible_input = $('input.langinput', $this);

                // Zjistime zda prohlizes podporuje html5 placeholder
                var test = document.createElement('input');
                var placeholder_supported = ('placeholder' in test);

                // Jake je prave aktivni lokale
                var active_locale = false;

                /**
                 * Nastavi zadanemu inputu zadany placeholder
                 */
                var setPlaceholder = function($input, placeholder)
                {
                    var old_placeholder = $input.attr('placeholder');
                    $input.attr('placeholder', placeholder);
                    // Pokud prohlizec nepodporuje html 5 placeholder atribut
                    if ( ! placeholder_supported) {
                        // Pokud hodnota prvku odpovida staremu placeholderu nebo je prazdna
                        if ($input.val() == old_placeholder || $input.val() == '') {
                            // Zobrazime novy placeholder
                            $input.val(placeholder);
                            $input.addClass('placeholder');
                        }
                    }
                }


                /**
                 * Inicializuje custom placeholder funkcionalitu pokud neni placeholder podporovan prohlizecem
                 */
                var initPlaceholder = function($input)
                {
                    if ( ! placeholder_supported) {
                        // Use this explicit placeholder functionality
                        $input.on('focus', function() {
                            if ($input.val() == $input.attr('placeholder')) {
                                $input.val('');
                                // Remove class (for gray text)
                                $input.removeClass('placeholder');
                            }
                        }).on('blur', function() {
                                if ($input.val() == '') {
                                    $input.val($input.attr('placeholder'));
                                    // Add placeholder class (for gray text)
                                    $input.addClass('placeholder');
                                }
                            });

                        // Pokud je hodnota prvku prazdna, zobrazime placeholder
                        // - (!) pokud je hodnotou placeholder pak muze byt potreba pridat placeholder class (po reloadu)
                        if ($input.val() == '' || $input.val() == $input.attr('placeholder')) {
                            $input.val($input.attr('placeholder'));
                            $input.addClass('placeholder');
                        }
                    }
                }



                var onActiveLocaleChanged = function(event, locale) {
                    if (active_locale) {
                        // Ulozime aktualni text do hidden inputu aktualniho locale
                        var translation = $visible_input.val();
                        // Store it in related hidden input
                        $('input[type="hidden"][data-locale="' + active_locale + '"]', $this).val(translation).attr('name', $visible_input.attr('name'));
                    }

                    // Precteme text z hidden inputu prave aktivovaneho locale
                    var $hidden_input = $('input[data-locale="' + locale +'"]', $this);
                    translation = $hidden_input.val();
                    // Zapiseme do editacniho inputu
                    $visible_input.val(translation);

                    // Nastavime viditelnemu inputu name toho skryteho - aby se po ulozeni poslal na server
                    $visible_input.attr('name', $hidden_input.attr('name'));
                    // Skrytemu inputu name odebereme a pridame mu "active" class
                    $hidden_input.removeAttr('name');

                    // Nastavime placeholder
                    var placeholder = '';
                    if (locale in params.placeholders) {
                        placeholder = params.placeholders[locale];
                    }
                    setPlaceholder($visible_input, placeholder);

                    // Ulozime si aktivni locale
                    active_locale = locale;
                };


                // Budeme odchytavat udalost languagesChanged (zmena jazyku master prvkem)
                $form.unbind('languagesChanged.langStringPanelSlave');
                $form.bind('languagesChanged.langStringPanelSlave', function(event, languages) {
                    // Create local copy of enabled languages object
                    var enabled_languages = $.extend(true, {}, languages);
                    // Projdeme vsechny skryte inputy
                    $this.find('input[type="hidden"]').each(function() {
                        var $input = $(this);
                        var input_lang = $input.attr('data-locale');
                        // Pokud dany jazyk jiz neni povoleny - vyhodime input
                        if ( ! (input_lang in enabled_languages)) {
                            $input.remove();
                        } else {
                            // Input lang is enabled - remove it from enabled languages list
                            delete enabled_languages[input_lang];
                        }
                    });

                    // Projdeme zbyvajici jazyky a vytvorime pro ne prazdne skryte inputy
                    for (var locale in enabled_languages) {
                        var $input = $(document.createElement('input'))
                            .attr('type', 'hidden')
                            .attr('name', params.attr + '[' + locale + ']')
                            .attr('data-locale', locale)
                            .val('');
                        $this.append($input);
                    }
                });

                // Inicializujeme placeholder ve viditelnem inputu
                initPlaceholder($visible_input);

                // Na udalost activeLocaleChanged budeme menit hodnotu v inputu - hodime tam preklad pro dane locale
                $form.unbind('activeLocaleChanged.langStringPanelSlave');
                $form.bind('activeLocaleChanged', onActiveLocaleChanged);

                onActiveLocaleChanged(null, $form.objectForm('getDefaultLocale'));

            }); // end each
            
        } // end init
      
    };

    $.fn.AppFormItemLangStringPanelSlave = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemLangStringPanelSlave');
            
        }
        
        return this;

    };

})( jQuery );

