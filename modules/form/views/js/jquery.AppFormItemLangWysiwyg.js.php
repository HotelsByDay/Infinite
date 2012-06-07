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
                    // Naklonujeme vzorovy prvek
                    var $translate = $(".langitem_source .langitem", $this).clone();

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

                    $select.find('option').each(function(){
                        // Podivame se zda je option zvolena v nejakem selectu
                        var $selected = $("select option[value='"+this.value+"']:selected", $this);
                        if ( ! $selected.length) {
                            // Pokud neni zvolena, pak tuto hodnotu zvolime v nasem selectu
                            $select.val(this.value);
                            // A ukoncime iterovani
                            return false;
                        }
                    });

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

                }); // end add_link event

                //initializace wysiwyg editoru pro jiz definovane lang varianty
                $this.find('.langitems .langitem').each(function(){
                    methods._initWysiwyg($(this));
                });

                // Pokud je na zacatku ve formulari tolik poli, kolik je jazyku
                if ($(".langitem", $this).length >= params.locales_count) {
                    // Skryjeme odkaz
                    $(".langadd a", $this).hide()
                        // Zobrazime span
                        .parent().find('span').show();
                }
                
            
            }); // end each
            
        }, // end init

        _initWysiwyg: function($item) {

            $item.find('textarea').redactor({
                path: '<?= url::base();?>redactor/',
                autoresize: true,
                toolbar: 'mini',
                focus: false
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

