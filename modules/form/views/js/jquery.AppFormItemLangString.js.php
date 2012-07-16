// <script>
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemLangString';

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
                locales_count : 0
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

                //Pokud prislo nastaveni, tak mergnu s defaultnimi hodnotami
                var params = $.extend(true, settings, options);

                // Zjistime zda prohlizes podporuje html5 placeholder
                var test = document.createElement('input');
                var placeholder_supported = ('placeholder' in test);


                /**
                 * Metoda volana pri zmene jazyka (select.onchange)
                 */
                var langChanged = function() {
                    // Prislusne Textarey nastavim spravny placeholder podle
                    // zvoleneho jazyka
                    var selected_lang_placeholder = $(this).find('option:selected').attr('placeholder');
                    var $input = $(this).parents('.langitem:first').find('.langinput');
                    // Nastavime inputu novy placeholder
                    setPlaceholder($input, selected_lang_placeholder);
                    // Najdeme vsechny selecty
                    var $selects = $("select", $this);
                    // Odebereme globalni warning - ten pozdeji muze kontrolovat AppForm plugin pred odeslanim
                    $this.removeClass('warning');
                    // Odebereme vsechny lokalni warningy - ty zvyraznuji potencialne chybne zvolene selecty
                    $selects.each(function(){
                        $(this).parents('.langitem:first').removeClass('warning');
                    });
                    // Projdeme vsechny selecty a pokud je jejich hodnota v prvku vicekrat, tak je zvyraznime
                    $selects.each(function(){
                        // Najdeme vsechny option s aktualnim jazykem, ktere jsou selected
                        var $options = $("option[value='"+$(this).val()+"']:selected", $this);
                        // Pokud je jich vice nez 1, nastavime warning class
                        if ($options.length > 1) {
                            $options.each(function(){
                                $(this).parents('.langitem:first').addClass('warning');
                            });
                            $this.addClass('warning'); // Vicenasobne volani by nemelo vadit
                        }
                    });
                }
                
                
                // Inicializace selectu
                $("select", $this).on('change', langChanged);
                
                
                // Inicializace odkazu pro pridani prekladu
                $(".langadd a", $this).on('click', function() {
                    // Zjistime, kolik prekladu uz je definovanych
                    var translates_count = $(".langitem", $this).length;
                    
                    // Pokud jsou vytvoreny inputy pro vsechny preklady, nedovolime pridat dalsi
                    if (translates_count >= params.locales_count) {
                        return;
                    }
                    // Naklonujeme PRVNI preklad
                    var $translate = $(".langitem:first", $this).clone();
                    
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
                    var $input = $translate.find('.langinput');
                    $input.attr('id', $input.attr('id').replace(/_\d$/, '_'+position));
                    $input.val('');
                    // V selectu zvolime prvni option, ktera jeste neni zvolena
                    var $select = $translate.find('select:first');
                    $select.find('option').each(function(){
                        // Podivame se zda je option zvolena v nejakem selectu
                        var $selected = $("select option[value='"+this.value+"']:selected", $this);
                        if ( ! $selected.length) {
                            // Pokud neni zvolena, pak tuto hodnotu zvolime v nasem selectu
                            $select.val(this.value);
                            // Textarei nastavim prislusny placeholder
                            setPlaceholder($input, $(this).attr('placeholder'));
                            // A inicializujeme ho - pokud neni podporovan html5 placeholder
                            initPlaceholder($input);
                            // A ukoncime iterovani
                            return false;
                        }
                    });
                    // Pripojime na select event listener
                    $select.on('change', langChanged);
                    
                    // Vlozime prvek pro zadani dalsiho prekladu
                    $(".langitems", $this).append($translate);

                    // Pokud je posledni, skryjeme odkaz a zobrazime span s informaci
                    if (translates_count+1 >= params.locales_count) {
                        $(this).hide()
                            .parent().find('span').show();
                    }

                }); // end add_link event


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
                 * Inicializuje explicitni placeholder funkcionalitu pokud neni placeholder podporovan prohlizecem
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

                // Pokud prohlizec nepodporuje placeholder atribut, inicializujeme nas placeholder js
                if ( ! placeholder_supported) {
                    $(".langitem .langinput", $this).each(function() {
                        initPlaceholder($(this));
                    });
                }


                // Pokud je na zacatku ve formulari tolik poli, kolik je jazyku
                if ($(".langitem", $this).length >= params.locales_count) {
                    // Skryjeme odkaz
                    $(".langadd a", $this).hide()
                        // Zobrazime span
                        .parent().find('span').show();
                }
                
            
            }); // end each
            
        } // end init
      
    };

    $.fn.AppFormItemLangString = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemLangString');
            
        }
        
        return this;

    };

})( jQuery );

