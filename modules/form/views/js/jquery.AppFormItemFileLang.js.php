// <script>
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemFileLang';



    /**
     * Metody pro tento plugin.
     */
    var methods = {

        init: function( options ) {

            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
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
                var params = $.extend( settings, options );


                /**
                 * Metoda volana pri zmene jazyka (select.onchange)
                 */
                var langChanged = function() {
                    // Prislusne Textarey nastavim spravny placeholder podle
                    // zvoleneho jazyka
                //    var selected_lang_placeholder = $(this).find('option:selected').attr('placeholder');
                //    $(this).parents('.langitem:first').find('.langinput').attr('placeholder', selected_lang_placeholder);
                    // Najdeme vsechny selecty
                    var $selects = $("table thead select", $this);
                    // Odebereme globalni warning - ten pozdeji muze kontrolovat AppForm plugin pred odeslanim
                    $this.removeClass('warning');
                    // Odebereme vsechny lokalni warningy - ty zvyraznuji potencialne chybne zvolene selecty
                    $selects.each(function(){
                        $(this).parents('th:first').removeClass('warning');
                    });
                    // Projdeme vsechny selecty a pokud je jejich hodnota v prvku vicekrat, tak je zvyraznime
                    $selects.each(function() {
                        // Najdeme vsechny option s aktualnim jazykem, ktere jsou selected
                        var $options = $("table thead select option[value='"+$(this).val()+"']:selected", $this);
                    //    alert('options length for value '+$(this).val()+' is '.$options.length);
                        // Pokud je jich vice nez 1, nastavime warning class
                        if ($options.length > 1) {
                            $options.each(function(){
                                $(this).parents('th:first').addClass('warning');
                            });
                            $this.addClass('warning'); // Vicenasobne volani by nemelo vadit
                        }
                    });

                    // Nepotrebujeme pridavat zadne inputy, ale potrebujeme je mozna prejmenovat
                    fillCellsWithInputs();
                }


                /**
                 * Odebere z tabulky jazyk ktery je vybran v selectu v zadane bunce
                 * @param $th
                 */
                var removeLang = function($th)
                {
                    // Zjistime na jake pozici je bunka v radku
                    var position = $th.parent().children().index($th);

                    // Projdeme vsechny radky a odstranime bunku na dane pozici
                    $("table.list tr", $this).each(function(){
                        $(this).children().eq(position).remove();
                    });

                    // Zavolame langChanged - aby se odstranila pripadna warning class - pokud se odebiral duplicitni jazyk
                    langChanged();

                    // Zobrazime odkaz na pridani jazyka
                    $(".langadd a", $this).show()
                        // Zobrazime span s hlaskou ze vsechny jazyky jsou pridane
                        .parent().find('span').hide();

                }


                /**
                 * Toto je volano v reakci na pridani prekladu a zaroven v reakci na change event tohoto prvku (ta je vyvolana
                 * po ajaxovem pridani radku)
                 */
                var fillCellsWithInputs = function()
                {
                    // Naklonujeme si lang view
                    var $lang_view = $('.clone_lang_view', $this).clone();

                    // Projdeme vsechny locale selecty a ulozime si seznam vsech locale
                    var $locales = $("table.list select[name*='[locales]']", $this);
                    // pocet definovanych locale
                    var locales_count = $locales.length;
                    // Pocet budek ocekavanych v kazdem radku tabulky - pocet locale + 3 (preview, zakladni nataveni, pridat jazyk)
                    var cells_count = locales_count + 3;
                    // Seznam definovanych locale
                    var locales = [];
                    $locales.each(function(){
                        locales[locales.length] = $(this).val();
                    });

                    // Projdeme vsechny radky tabulky a pridame bunku s lang_view tam kde chybi
                    var $trs = $("table.list tbody tr", $this);
                    $trs.each(function() {
                        var $tr = $(this);
                        // Dokud nemame spravny pocet sloupcu, budeme pridavat bunky
                        while ($tr.find('td').length < cells_count) {
                            $tr.find('td:last').before('<td>'+$lang_view.html()+'</td>');
                        }

                        // Zjistime type_key aktualniho souboru - *[id]* input by mel byt v kazem radku
                        var type_key = $('*[name*="[id]"]', $tr).attr('name').replace(/.*?\[(.*?)\].*/, '$1');

                        // Projdeme vsechny bunky a prejmenujeme v nich vsechny lang prvky
                        // - nastavime spravny type_key a spravne locale
                        var position = 0;
                        $('td', $tr).each(function() {
                            // Preskocime prvni dve bunky
                            if (position < 2) {
                                position++;
                                return;
                            }
                            // Pokud je bunka az za bunkami se selecty, take preskocima - a ukoncime prochazeni
                            if (position-1 > locales.length) {
                                return false;
                            }

                            // Precteme jazyk aktualniho sloupce
                            var locale = locales[position-2];

                            // Prejmenujeme vsechny lang inputy
                            $("*[name*='[_lang]']", $(this)).each(function() {
                                var name = $(this).attr('name');
                                // Vypocteme novy nazev
                                // attr[*][_lang][*]SUFFIX -> attr[type_key][_lang][locale]SUFFIX
                                var new_name = name.replace(/(.*?)\[.*?\]\[_lang\]\[.*?\](.*)/, '$1[' + type_key + '][_lang][' + locale + ']$2');
                             //   alert ('name transformation: *'+name+'* -> *'+new_name+'*');
                                $(this).attr('name', new_name);
                            });

                            position++;
                        });

                    });
                }

                var initPlaceholders = function()
                {
                    // Pokud prohlizec podporuje placeholder atribut, pak nic delat nemusime
                    var test = document.createElement('input');
                    var placeholder_supported = ('placeholder' in test);
                    if (placeholder_supported) {
                        return;
                    }

                    // Projdeme vsechny lang inputy
                    $('input[name*="[_lang]"]', $this).each(function(){
                        var $input = $(this);
                        // Pokud maji nastaven placeholder
                        if ($input.attr('placeholder') !== 'undefined' && $input.attr('placeholder')) {
                            // Odebereme predchozi event handler
                            $input.off('focus.placeholder').off('blur.placeholder');

                            // Use this explicit placeholder functionality
                            $input.on('focus.placeholder', function() {
                                if ($input.val() == $input.attr('placeholder')) {
                                    $input.val('');
                                    // Remove class (for gray text)
                                    $input.removeClass('placeholder');
                                }
                            }).on('blur.placeholder', function() {
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
                                $input.addClass('placeholder').css('backgroundColor', 'red');
                            }
                        }
                    });
                }

                // Inicializace selectu pro vyber jazyka
                $("select[name*='[locales]']", $this).on('change', langChanged);

                // inicializace odkazu pro zmenu jazyka
                $("thead th .cancel", $this).on('click', function() {
                    removeLang($(this).parents('th:first'));
                });

                // Change udalost je vyvolana po vlozeni novehou souboru do tabulky
                $this.on('fileAdded', function(event){
                    fillCellsWithInputs(event);
                    initPlaceholders();
                });

                initPlaceholders();




                // Inicializace odkazu pro pridani prekladu
                $(".langadd a", $this).on('click', function() {
                    // Zjistime, kolik prekladu uz je definovanych
                    var translates_count = $("select[name*='[locales]']", $this).length;

                    // Pokud jsou vytvoreny inputy pro vsechny preklady, nedovolime pridat dalsi
                    if (translates_count >= params.locales_count) {
                        return;
                    }

                    // Naklonujeme PRVNI bunku se selectem pro vyber jazyka
                    var $th = $("select[name*='[locales]']:first", $this).parents('th').clone();
                    var $select = $th.find('select');
                    // V selectu zvolime prvni option, ktera jeste neni zvolena
                    $select.find('option').each(function(){
                        // Podivame se zda je option zvolena v nejakem selectu
                        var $selected = $("select[name*='[locales]'] option[value='"+this.value+"']:selected", $this);
                        if ( ! $selected.length) {
                            // Pokud neni zvolena, pak tuto hodnotu zvolime v nasem selectu
                            $select.val(this.value);
                            //textarea nastavim prislusny placeholder
                        //    $input.attr('placeholder', $(this).attr('placeholder'));
                            // A ukoncime iterovani
                            return false;
                        }
                    });

                    // Pridame onChange handler na select
                    $select.on('change', langChanged);

                    // Pridame on click handler na tlacitko pro vyber jazyka
                    var $cancel = $('<span></span>').addClass('cancel').on('click', function() {
                        removeLang($(this).parents('th:first'));
                    });

                    $th.append($cancel);

                    // Vlozime th do hlavicky tabulky (pred posledni sloupec - v nem je jen odkaz "add another language"
                    $("table thead th:last").before($th);

                    // Pokud je zobrazeno tolik prekladu kolik je jazyku
                    if ($("select[name*='[locales]']", $this).length >= params.locales_count) {
                        // Skryjeme odkaz na pridani jazyka
                        $(".langadd a", $this).hide()
                            // Zobrazime span s hlaskou ze vsechny jazyky jsou pridane
                            .parent().find('span').show();
                    }

                    // tato funkce projde radky tabulky a naclonuje do patricnych sloupcu prvni bunku pro jazykove hodnoty
                    fillCellsWithInputs();

                }); // end add_link event


                // Pokud je na zacatku ve formulari tolik poli, kolik je jazyku
                if ($("select[name*='[locales]']", $this).length >= params.locales_count) {
                    // Skryjeme odkaz na pridani jazyka
                    $(".langadd a", $this).hide()
                        // Zobrazime span s hlaskou ze vsechny jazyky jsou pridane
                        .parent().find('span').show();
                }





            });

        }

    };

    $.fn.AppFormItemFileLang = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {

            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemFileLang');

        }

        return this;

    };

})( jQuery );

