// <script>
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemLangWysiwygPanelSlave';

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

                // Input ve kterem se zobrazuje aktivni preklad
                var $visible_input = $('textarea.langinput', $this);

                // Zjistime zda prohlizes podporuje html5 placeholder
                var test = document.createElement('input');
                var placeholder_supported = ('placeholder' in test);

                // Jake je prave aktivni lokale
                var active_locale = false;


                /**
                 * Inicializuje wysiwyg editor
                 * @param $item
                 */
                var initWysiwyg = function($item) {
                    var redactor_settings = {
                        path: '<?= url::base();?>redactor/',
                        autoresize: false,
                        resize: false,
                        // See http://redactorjs.com/docs/toolbar/
                        buttons: ['formatting', '|', 'bold', 'italic', '|','fontcolor','|',
                            'unorderedlist', 'orderedlist', 'outdent', 'indent', '|', 'link'],
                        focus: false,
                        callback: function() {
                            // Fire an event - the layout of the form has changed
                            $this.trigger('itemLayoutChanged');
                        }
                    };
                    if (params.images_upload) {
                        redactor_settings.imageUpload = params.images_upload;
                        redactor_settings.buttons.push('image');
                    }

                    //options to align text
                    redactor_settings.buttons.push('|');
                    redactor_settings.buttons.push('alignleft');
                    redactor_settings.buttons.push('aligncenter');
                    redactor_settings.buttons.push('alignright');

                    $visible_input.redactor(redactor_settings);
                }


                /**
                 * activeLocaleChanged event handler
                 * @param event
                 * @param locale
                 */
                var onActiveLocaleChanged = function(event, locale) {
                    // Bez tohoto hazi redactor chybu, prestoze doslo k volani initWysiwyg funkces
                 //   $visible_input.redactor();
                    if (active_locale) {
                        // Ulozime aktualni text do hidden inputu aktualniho locale
                        var translation = $visible_input.getCode();
                        // Store it in related hidden input
                        $('textarea[data-locale="' + active_locale + '"]', $this).val(translation).attr('name', $visible_input.attr('name'));
                    }

                    // Precteme text z hidden inputu prave aktivovaneho locale
                    var $hidden_input = $('textarea[data-locale="' + locale +'"]', $this);
                    translation = $hidden_input.val();
                    // Zapiseme do editacniho inputu
                    $visible_input.setCode(translation);

                    // Nastavime viditelnemu inputu name toho skryteho - aby se po ulozeni poslal na server
                    $visible_input.attr('name', $hidden_input.attr('name'));
                    // Skrytemu inputu name odebereme a pridame mu "active" class
                    $hidden_input.removeAttr('name');

                    // Ulozime si aktivni locale
                    active_locale = locale;
                };

                // Budeme odchytavat udalost languagesChanged (zmena jazyku master prvkem)
                $form.unbind('languageChanged.wysiwygPanelSlave');
                $form.bind('languageChanged.wysiwygPanelSlave', function(event, enabled_languages) {
                    // Vazne to funguje?
                    enabled_languages = enabled_languages || {};
                    // Projdeme vsechny skryte inputy
                    $this.find('textarea.hidden').each(function() {
                        var $input = $(this);
                        var input_lang = $input.attr('data-locale');
                        // Pokud dany jazyk jiz neni povoleny - vyhodime input
                        if ( ! input_lang in enabled_languages) {
                            $input.remove();
                        } else {
                            // Input lang is enabled - remove it from enabled languages list
                            delete enabled_languages[input_lang];
                        }
                    });

                    // Projdeme zbyvajici jazyky a vytvorime pro ne prazdne skryte inputy
                    for (var locale in enabled_languages) {
                        var $input = $(document.createElement('textarea'))
                            .attr('name', params.attr + '[' + locale + ']')
                            .attr('data-locale', locale)
                            .addClass('hidden')
                            .val('');
                        $this.append($input);
                    }
                });

                // Inicializujeme wysiwyg editor
                initWysiwyg($visible_input);

                // Na udalost activeLocaleChanged budeme menit hodnotu v inputu - hodime tam preklad pro dane locale
                // - nejprve volame unbind, protoze po ulozeni formulare dochazi k opetovne inicializaci prvku, zatimco form
                //   je stale stejny
                $form.unbind('activeLocaleChanged.wysiwygPanelSlave');
                $form.bind('activeLocaleChanged.wysiwygPanelSlave', onActiveLocaleChanged);

                onActiveLocaleChanged(null, $form.objectForm('getDefaultLocale'));

            }); // end each
            
        } // end init
      
    };


    $.fn.AppFormItemLangWysiwygPanelSlave = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemLangWysiwygPanelSlave');
            
        }
        
        return this;

    };

})( jQuery );

