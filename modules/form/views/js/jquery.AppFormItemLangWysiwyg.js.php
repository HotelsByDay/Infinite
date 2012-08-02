// <script>
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemLangWysiwyg';

    /**
     * Metody pro tento plugin.
     */
    var methods = {
        
        init: function( options ) {
            
            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
                //      locales : {},
                locales_count : 0,
                // Mode prvku - Master / Slave / null
                mode: null,
                // Pokud je prvek master, pak po zmene nastaveni jazyku posle ajaxovy pozadavek na tuto url
                languages_syncer_url: ''
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
                
                /**
                 * Metoda volana pri zmene jazyka (select.onchange)
                 */
                var langChanged = function() {
                    // Najdeme vsechny selecty
                    var $selects = $(".langitems select", $this);
                    // Odebereme globalni warning - ten pozdeji muze kontrolovat AppForm plugin pred odeslanim
                    $this.removeClass('warning');
                    // Odebereme vsechny lokalni warningy - ty zvyraznuji potencialne chybne zvolene selecty
                    $selects.each(function(){
                        $(this).parents('.langitem:first').removeClass('warning');
                    });
                    // Projdeme vsechny selecty a pokud je jejich hodnota v prvku vicekrat, tak je zvyraznime
                    $selects.each(function(){
                        // Najdeme vsechny option s aktualnim jazykem, ktere jsou selected
                        var $options = $(".langitems option[value='"+$(this).val()+"']:selected", $this);
                        // Pokud je jich vice nez 1, nastavime warning class
                        if ($options.length > 1) {
                            $options.each(function(){
                                $(this).parents('.langitem:first').addClass('warning');
                            });
                            $this.addClass('warning'); // Vicenasobne volani by nemelo vadit
                        }
                    });
                    languagesChanged();
                }



                /**
                 * SLAVE - Tato funkce je zavolana v pripade ze prvek je slave a master prvek
                 * zmenil seznam povolenych jazyku
                 */
                var onEnabledLanguagesChanged = function(enabled_languages)
                {
                    // Vytvorime si lokalni kopii pole jazyku
                    var languages = enabled_languages.slice();
                    if (typeof languages == 'undefined' || ! languages) {
                        return;
                    }
                    // Pocitadlo indexu jazyku
                    var lang_index = 0;

                    _log('SLAVE.onEnabledLanguagesChanged called with languages: '+languages.join(', '));
                    // Projdeme vsechny lang item tohoto prvku
                    $('.langitems .langitem', $this).each(function() {
                        // Aktualni langitem select
                        var $s = $('select', $(this));
                        // Aktualne zpracovavany povoleny jazyk
                        var lang = languages[lang_index];
                        // Pokud neni aktualni jazyk povoleny, pak ho odebereme
                        if ($.inArray($s.val(), languages) == -1) {

                            // Pokud povoleny jazyk na aktualnim indexu neni v prvku pritomen
                            // pak doslo k prepnuti jazyka - prepneme aktualni select
                            // - zkusime najit select ve kterem je zvolen aktualne zpracovavany enabled lang

                            var enabled_lang_defined = $('.langitems select option[value="'+lang+'"]:selected', $this).length;

                            if (enabled_lang_defined) {
                                // Doslo k odebrani jazyka aktualniho prekladu
                                // - aktualni preklad neni povolen ale aktualni povoleny jazyk je definovan
                                // Odebereme jazyk (resi pripad odebrani prostredniho jazyka)
                                $(this).remove();
                                return;
                            } else {
                                // Aktualni preklad nema povoleny jazyk a aktualni povoleny jazyk nema definovany preklad
                                // - nejspis doslo k prepnuti jazyka - prepneme select
                                // - coz je zajisteno dale v if vetvi
                            }
                        }

                        // Zjistime zda je na indexu nejaky jazyk
                        if (typeof languages[lang_index] != 'undefined') {
                            // Pokud ano, pak ho nastavime do aktualniho prvku jako zvoleny
                            $s.val(lang);
                            // Zaroven ho nastavime do hidden inputu (disabled select se totiz nativne neposila v postu)
                            $('input.hidden_locale', $(this)).val(lang);
                        }
                        else {
                            // Jazyk jiz neni povolen - odebereme ho
                            $(this).remove();
                        }
                        lang_index++;
                    });
                    // Pokud jsme zatim nevycerpali vsechny povolene jazyky, projdeme zbytek a pridame lang items
                    while (typeof languages[lang_index] != 'undefined') {
                        var lang = languages[lang_index];
                        addLanguage(lang);
                        lang_index++;
                    }
                }


                var _log = function(msg) {
                    if (typeof console != 'undefined' && console.log) {
                        console.log(msg);
                    }
                }

                /**
                 * MASTER - Tohle je lokalne zavolano pokud dojde ke zmene jazyka nebo jeho pridani
                 */
                var languagesChanged = function()
                {
                    // Na tuto udalost reagujeme jen pokud jsme master (muze nastat ikdyz jsme v unset mode)
                    if (params.mode == '<?= AppForm::LANG_MASTER ?>') {
                        // Posbirame seznam vsech zvolenych jazyku
                        var $selects = $('.langitems select', $this);
                        var languages = [];
                        $selects.each(function(){
                            // Pridame jazyk do seznam
                            languages[languages.length] = $(this).val();
                        });

                        // Vyvolame na formulari udalost "zmena jazyku"
                        $form.objectForm('fireEvent', 'languagesChanged', languages);

                        // Provedeme ajaxovou synchronizaci DB na serveru
                        _log('ajax request url: '+params.languages_syncer_url);
                        $._ajax({
                            url: params.languages_syncer_url,
                            type: 'POST',
                            data: {'<?= AppForm::ENABLED_LANGUAGES_POST_KEY ?>' : languages},
                            success: function(r) {
                                _log('DB has been synchronized...');
                            }
                        })
                    }
                }

                // Inicializace odkazu pro pridani prekladu
                var initAddLink = function()
                {
                    $(".langadd a", $this).on('click', function(){
                        addLanguage() ;
                        // Focus do prave pridaneho inputu
                        $('.langitem:last', $this).find('textarea:first').focus();
                    }); // end add_link event
                }

               var addLanguage = function(locale) {
                    // Zjistime, kolik prekladu uz je definovanych
                    var translates_count = $(".langitem", $this).length;
                    _log('add language called');
                    // Pokud jsou vytvoreny inputy pro vsechny preklady, nedovolime pridat dalsi
                    if (translates_count >= params.locales_count) {
                        return;
                    }
                    // Naklonujeme vzorovy prvek
                    var $translate = $(".langitem_source .langitem", $this).clone();

                   _log('clonned translate html: \n'+$translate.html());
                    // Spocteme jeho pozici
                    var position = translates_count+1;
                    // Upravime jeho label
                    var $label = $translate.find('label');
                    if ($label.length != 0) {
                        // Pridame mu poradove cislo do labelu
                        $label.html($label.html() + ' ' + position);
                        // Upravime jeho "for" atribut
                        $label.attr('for', $label.attr('for').replace(/_\d$/, '_'+position));
                    }
                    // Inputu upravime jeho "id" (to muze byt bud input nebo textarea)
                    var $textarea = $translate.find('textarea');
                    $textarea.val('');
                    //langitem_source ma misto 'name' atribut '_name' tak aby se jeho prazdna hodnota
                    //neodesilala na server
                    $textarea.attr('name', $textarea.attr('_name')).removeAttr('_name');

                    // V selectu zvolime prvni option, ktera jeste neni zvolena
                    var $select = $translate.find('select:first');

                    //langitem_source ma misto 'name' atribut '_name' tak aby se jeho prazdna hodnota
                    //neodesilala na server
                    $select.attr('name', $select.attr('_name')).removeAttr('_name');

                   // U slave prvku musime disablovat select
                   if (params.mode == '<?= AppForm::LANG_SLAVE ?>') {
                       // Create hidden input
                       var $input = $('<input />').attr('name', $select.attr('name')).val($select.val()).attr('type', 'hidden').addClass('hidden_locale');
                       $select.after($input).attr('disabled', true);
                   }


                   // Pokud je zadano locale, tak ho selectu nastavime
                   if (typeof locale !== 'undefined' && locale) {
                       //    _log('locale is defined and its value is:'+locale);
                       $select.val(locale);
                       $select.siblings('input.hidden_locale').val(locale);
                   } else {
                       // Jinak v selectu zvolime prvni nepouzity jazyk
                       selectFirstUnusedLanguage($select);
                   }

                    // Pripojime na select event listener
                    $select.on('change', langChanged);

                    // Vlozime prvek pro zadani dalsiho prekladu
                    $(".langitems", $this).append($translate);

                    //inicializace wysiwyg editoru
                    methods._initWysiwyg($translate);

                    // Pokud je posledni, skryjeme odkaz a zobrazime span s informaci
                    if (translates_count+1 >= params.locales_count) {
                        $(this).hide()
                            .parent().find('span').show();
                    }

                   // Pokud je v prvku vice nez jeden jazyk - coz by asi melo byt vzdy po pridani jazyka
                   if ($('.langitem', $this).length > 1) {
                       // pak zobrazime odkazy na odebrani
                       $('.remove_lang', $this).show();
                   }
                   languagesChanged();
                }


                /**
                 * Odebere jazyk - parametrem je jQuery objekt tlacitka na ktere bylo kliknuto
                 * @param $btn
                 */
                var removeLanguage = function($btn)
                {
                    // Zkontrolujeme, ze je v prvku vice nez jeden jazyk - alespon jeden tam totiz musi zustat
                    if ($('.langitem', $this).length <= 1) {
                        $('.remove_lang', $this).hide();
                        return;
                    }
                    // Najdeme item ve kterem je btn a odebereme ho
                    var $item = $btn.parents('.langitem:first');
                    // Zjistime jaky jazyk se odebira
                    var removed_lang = $item.find('select').val();
                    _log('removing lang: '+removed_lang);
                    // Pokud jsme master prvek
                    if (params.mode == '<?= AppForm::LANG_MASTER; ?>') {
                        // vyzadame o potvrzeni akce
                        $item.addClass('to_be_deleted');
                        if (confirm('<?= __('appformitemlang.remove_lang_from_master.confirm'); ?>')) {
                            // Vyvolame ajax pozadavek, ktery zajisti odstraneni prekladu pro tento jazyk z DB
                            // Vlastni odebrani jazyka
                            $item.remove();
                            // Zajistime propagovani do slave prvku a na server
                            languagesChanged();
                        } else {
                            $item.removeClass('to_be_deleted');
                        }
                    } else {
                        // Vlastni odebrani jazyka - nejsme master, takze neni potreba potvrzovat
                        $item.remove();
                    }

                    // Zobrazime odkaz pro pridani jazyka - pokud nejsou vsechny jazyky zobrazene
                    // Pokud je na zacatku ve formulari tolik poli, kolik je jazyku
                    if ($(".langitem", $this).length < params.locales_count) {
                        // Skryjeme odkaz
                        $(".langadd a", $this).show()
                            // Zobrazime span
                            .parent().find('span').hide();
                    }
                    // Pokud je ve prvku jen jeden preklad pak skryjeme odkaz pro jeho odebrani
                    if ($('.langitem', $this).length <= 1) {
                        $('.remove_lang', $this).hide();
                    }
                }


                /**
                 * Zvoli v zadanem selectu prvni jazyk ktery v danem lang prvku zatim neni zvolen
                 * @param $select
                 * @return {Boolean}
                 */
                var selectFirstUnusedLanguage = function($select)
                {
                    _log('select first unusd locale called');
                    $select.find('option').each(function(){
                        // Podivame se zda je option zvolena v nejakem selectu
                        var $selected = $("select option[value='"+this.value+"']:selected", $this);
                        if ( ! $selected.length) {
                            // Pokud neni zvolena, pak tuto hodnotu zvolime v nasem selectu
                            $select.val(this.value);
                            // Slave ma hodnotu v hidden inputu
                            if (params.mode == '<?= AppForm::LANG_SLAVE; ?>') {
                                $select.siblings('input.hidden_locale').val(this.value);
                            } else if (params.mode == '<?= AppForm::LANG_MASTER; ?>') {
                                // Masterovi musime select enablovat a nastavit mu name
                                var $hidden = $select.siblings('input.hidden_locale');
                                $select.attr('name', $hidden.attr('name'));
                                $select.attr('disabled', false);
                                $hidden.remove();
                            }
                            _log('before $input id in langString');

                            // A ukoncime iterovani
                            return false;
                        }
                    });
                    return true;
                }



                // initializace wysiwyg editoru pro jiz definovane lang varianty
                $this.find('.langitems .langitem').each(function(){
                    methods._initWysiwyg($(this));
                });


                // Pokud je ve prvku jen jeden preklad pak skryjeme odkaz pro jeho odebrani
                if ($('.langitem', $this).length <= 1) {
                    $('.remove_lang', $this).hide();
                }

                /**
                 * Pokud prvek je master nebo mod neni nastaven
                 */
                if (params.mode != '<?= AppForm::LANG_SLAVE ?>') {
                    // Inicializace selectu
                    $("select", $this).on('change', langChanged);

                    // Inicializace add odkazu
                    initAddLink();
                }

                // LANG_SLAVE prvek musi pridavat jazyky prostrednictvim lang_master prvku
                // coz je reseno pres system callbacku spravovanych formularem
                if (params.mode == '<?= AppForm::LANG_SLAVE ?>') {
                    // Zavolame metodu jeho objectForm pluginu
                    $form.objectForm('subscribeEvent', 'languagesChanged', onEnabledLanguagesChanged);
                }



                if (params.mode == '<?= AppForm::LANG_SLAVE ?>' || params.mode == '<?= AppForm::LANG_MASTER ?>') {
                    // V techto rezimu uzivatel nemuze rucne menit nastaveni jazyku ktere jsou jiz ulozeny v db
                    $(".langitems select", $this).each(function(){
                        // Current select
                        var $s = $(this);
                        // Create hidden input
                        var $input = $('<input />').attr('name', $s.attr('name')).val($s.val()).attr('type', 'hidden').addClass('hidden_locale');
                        $s.after($input).attr('disabled', true);
                    });
                }

                // LANG_MASTER prevek umoznuje odebirani jazyku
                if (params.mode == '<?= AppForm::LANG_MASTER ?>') {
                    // Inicializace odebrani jazyka
                    $('.remove_lang', $this).on('click', function() {
                        removeLanguage($(this));
                    });
                }
            
            }); // end each
            
        }, // end init

        _initWysiwyg: function($item) {
            var $this = $(this);
            $item.find('textarea').redactor({
                path: '<?= url::base();?>redactor/',
                autoresize: false,
                resize: false,
                // See http://redactorjs.com/docs/toolbar/
                buttons: ['formatting', '|', 'bold', 'italic', '|',
                    'unorderedlist', 'orderedlist', 'outdent', 'indent', '|', 'link'],
                focus: false,
                callback: function() {
                    // Find parent form
                    var $form = $this.parents(".<?= AppForm::FORM_CSS_CLASS ?>:first");

                    // Fire a form event - the layout of the form has changed
                    $form.objectForm('fireEvent', 'itemLayoutChanged', $this);
                }
            });
        }

    };

    $.fn.AppFormItemLangWysiwyg = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemLangWysiwyg');
            
        }
        
        return this;

    };

})( jQuery );

