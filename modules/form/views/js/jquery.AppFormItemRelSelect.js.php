// <script>
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemRelSelect';


    /**
     * Metody pro tento plugin.
     */
    var methods = {
        
        init: function( options ) {
            
            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
                data_url : ""
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
                var default_params = $.extend(true, settings, options );
                
                // Najdeme si input pro NAME hodnotu (label), ktery je potomkem $this uzlu
                var $name = $('input[name$="[name]"]', $this);
                // Stejne tak pro value hodnotu - id
                var $value = $('input[name$="[value]"]', $this);

                // Seznam names prvku ktere byly automaticky vyplneny po zvoleni objektu
                var filled_input_names = [];

                // Ulozime si aktualni jmeno, abychom mohli detekovat jeho zmenu v inputu
                $value.data('selected', $name.val());

                $name.change(function() {
                    //podivam se na hodnotu, ktera byla uzivatelem vybrana
                    var current_ui_item = $value.data('selected');

                    //pokud se lisi od te aktualni co je ted v inputu, tak to uzivatel
                    //prepsal a odstranim i hodnotu ID
                    if (current_ui_item != $(this).val() || $(this).val() == '') {
                        // Odstranime ID hodnotu a vyvolame change event
                        $value.val('').trigger('change');
                        $(this).val('');

                        //pokud je definovany dceriny prvek - tak jeho hodnotu vymazu
                        //pri zmene hodnoty tohoto prvku
                        if (typeof default_params.filter_child_attr !== 'undefined') {
                            for (k in default_params.filter_child_attr) {
                                var attr_name = default_params.filter_child_attr[k];

                                //vezmu si hodnotu atributu 'name' tohoto prvku
                                var name = $name.attr('name');
                                //vlastni nazev atributu replacnu za ten cilovy - ten podle ktereho se ma filtrovat
                                //tento zpusob je kvuli pouziti na appformitemadvanceditemlist, protoze
                                //potrebuju sahat na formular modelu nad kterym je tato instance prvku
                                //a ne na ostatni formulare - v name atributu je i ID daneho formulare/modelu
                                name = name.replace(default_params['attr'], attr_name)

                                var input_name = name;
                                var input_value = name.replace('[name]', '[value]');

                                $('input[name="'+input_name+'"]').val('');
                                $('input[name="'+input_value+'"]').val('');
                            }
                        }

                        // Pokud nektere prvky byly auto-filled tak je take smazu
                        for (i in filled_input_names) {
                            var name = filled_input_names[i];
                            // 5.7.2012 - Dajc
                            // - pridan change trigger nad danym prvkem po zapsani hodnoty do nej - spoleha na to
                            //   prvek ObjectImageSelect
                            $('input[name="'+name+'"],textarea[name="'+name+'"],select[name="'+name+'"]').each(function(){
                                $(this).val('');
                                $(this).trigger('change');
                            });
                        }
                        filled_input_names = [];
                    }
                });
                

                var onFocusHandler = function(){
                    if (this.value == "") {
                        $(this).autocomplete('search', '');
                    }
                };
                
                //defaultni parametry, ktere se budou posilat s kazdym pozadavkem
                var pom_data = {
                    preview: settings.preview,
                    _ps: settings._ps
                };


                $name.autocomplete({
                    source: function( request, response ) {

                        //pokud je definovan v konfiguracei 'filter_parent_attr' tak
                        //zkusim na formulari najit aktualni hodnotu (ocekavam
                        //ze to bdue prvek relselect
                        if (typeof default_params['filter_parent_attr'] !== 'undefined') {

                            for (k in default_params['filter_parent_attr']) {
                                //nazev atributu
                                var attr_name = default_params['filter_parent_attr'][k];
                                //selector pro dany prvek

                                //vezmu si hodnotu atributu 'name' tohoto prvku
                                var name = $name.attr('name');
                                //vlastni nazev atributu replacnu za ten cilovy - ten podle ktereho se ma filtrovat
                                //tento zpusob je kvuli pouziti na appformitemadvanceditemlist, protoze
                                //potrebuju sahat na forular modelu nad kterym je tato instance prvku
                                //a ne na ostatni formulare - v name atributu je i ID daneho formulare/modelu
                                name = name.replace(default_params['attr'], attr_name)
                                           .replace('[name]', '[value]');

                                console.log('looking for name: ' + name);

                                var $item_selector = $('input[name="'+name +'"]');
                                if ($item_selector.length != 0) {
                                    pom_data[attr_name] = $item_selector.val();
                                }
                            }
                        }

                        pom_data['_q'] = request.term;
                                                
                        $._ajax({
                            // Sestaveni URL adresy na poradac prislusneho objektu
                            url: settings.data_url,
                            dataType: "json",
                            data: pom_data,
                            success: function( data ) {
                                response( $.map( data, function( data_item ) {
                                    var item = {
                                        value: data_item.name,
                                        id:    data_item.value,
                                        //hodnoty, ktere maji byt 'dovyplneny' na formulari
                                        //pri vyberu teto polozky
                                        fill:  data_item.fill
                                    }

                                    return item;
                                }));
                            }
                        });
                    },
                    autoFocus: true,
                    minLength: 0,
                    select: function( event, ui ) {
                        //change event tady je vyvolan aby se propagoval vyse -
                        //to je potreba napriklad pri pouziti na advanceditemlist
                        //kde prvek chyta change udalost aby poznal ze uzivatel uz
                        //zadal alespon neco a povoli mu pridat dalsi novou (prazdnou)
                        //polozku
                        $value.val(ui.item.id);


                        //polozka muze obsahovat hodnoty, ktere maji byt
                        //dovyplneny na formulari
                        // 16.4. dajc presunul pred volani trigger change, protoze na tu udalost
                        // v prvku propertyAddress zpracovavam fill hodnotu (potrebuji aby tam jiz byla zapsana
                        // a myslim ze je logicke aby prvek vyvolal svou change udalost az dokonci vsechny zmeny v DOM
                        // mazani hodnot dcerinych prvku jsem vsak nepresunul, abych nezpusobil nechtene vedlejsi efekty
                        if (typeof ui.item.fill !== 'undefined') {
                            for (var k in ui.item.fill) {
                                // 5.7.2012 - Dajc
                                // - pridan change trigger nad danym prvkem po zapsani hodnoty do nej - spoleha na to
                                //   prvek ObjectImageSelect
                                $('input[type="text"][name="'+k+'"],input[type="hidden"][name="'+k+'"],textarea[name="'+k+'"],select[name="'+k+'"]').each(function(){
                                    // Ulozime si info o tom ze prvek byl automaticky vyplnen
                                    filled_input_names[filled_input_names.length] = k;
                                    $(this).val(ui.item.fill[k]);
                                    $(this).trigger('change');
                                });
                                // Pokud je prvek radio, tak zvolime to s danou hodnotou
                                $('input:radio[name="'+ k +'"]').each(function(){
                                    // Pokud je value radia shodna s nastavovanou hodnotou
                                    // - neni to v selectoru, protoze hodnota muze obsahovat nepovolene znaky - apostrofy a uvozovky
                                    //   kvuli kterym pak selector havaruje -> exception
                                    if ($(this).val() == ui.item.fill[k]) {
                                        $(this).attr('checked', true);
                                        $(this).trigger('change');
                                    }
                                });
                            }
                        }


                        //new value is being selected - trigge change event
                        if ($value.data('selected') != ui.item.value)
                        {
                            $value.trigger('change');
                        }

                        // Pokud byl v configu zadan onSelectTrigger, tak ho ted spustime
                        // a predame mu zvolenou polozku
                        // (to muze nastat treba pri pouziti jquery pluginu nejakym komplexnejsim prvkem,
                        //  jehoz je RelSelect pouhou soucasti)
                        if (typeof settings.onSelectTrigger === 'function')
                        {
                            settings.onSelectTrigger(ui.item);
                        }

                        //ulozi novou hodnotu, ktera bude pouzita k detekci zmen
                        $value.data('selected', ui.item.value);

                        //pokud je definovany dceriny prvek - tak jeho hodnotu vymazu
                        //pri vyberu tohoto prvku
                        if (typeof default_params.filter_child_attr !== 'undefined') {
                            for (k in default_params.filter_child_attr) {
                                var attr = default_params.filter_child_attr[k];
                                $('input[name="'+attr+'[name]"]').val('');
                                $('input[name="'+attr+'[value]"]').val('');
                            }
                        }

                        $value.trigger('change');
                    },
                    open: function(){
                        $(this).unbind('focus', onFocusHandler);
                        //$(this).unbind('change', onChangeHandler);
                    },
                    close: function(){
                        $(this).bind('focus', onFocusHandler);
                        //$(this).bind('change', onChangeHandler);
                    }
                }).focus(onFocusHandler);//.change(onChangeHandler);



                // Addresses jQuery 1.8.16 bug 7555: http://bugs.jqueryui.com/ticket/7555
                $('.ui-autocomplete-input', $this).each(function (idx, elem) {
                    var autocomplete = $(elem).data('autocomplete');
                    if ('undefined' !== typeof autocomplete) {
                        var blur = autocomplete.menu.options.blur;
                        autocomplete.menu.options.blur = function (evt, ui) {
                            if (autocomplete.pending === 0) {
                                blur.apply(this,  arguments);
                            }
                        };
                    }
                });




                //pokud je v nastaveni povoleno pridani nove relacni polozky
                //v ramci tohoto prvku, tak tuto funkci inicializuji
                if (typeof settings.add_new_url === 'string') {

                    var $dialog = $( document.createElement('div') ).hide()
                                                                .appendTo($this);

                    //defaultni parametry dialogu
                    var dialog_options = {
                        modal:true,
                        draggable:false,
                        autoOpen: false
                    };

                    //vezmu defaultni nastaveni dialogu z argumentu pluginu
                    var dialog_options = $.extend(dialog_options, default_params['dialog']);

                    //inicializace dialogu
                    $dialog._dialog(dialog_options);

                    //pri kliknuti na tlacitko pridat se do dialogu nacte editacni
                    //formular relacniho objektu
                    $(".add_new", $this).click(function(){

                        //objekt, ktery obsahuje dodatecne parametry pozadavku
                        var params = {};

                        if (typeof settings.preview !== 'undefined') {
                            params.__preview = settings.preview;
                        }

                        $dialog._dialog('loadForm', settings.add_new_url, params, function(response){
                            if (response['action_status'] == '<?= AppForm::ACTION_RESULT_SUCCESS;?>') {
                                //dialog zavru
                                $dialog._dialog('close');
                                
                                //"vyberu" prave vytvoreny zaznam
                                $value.val(response['id']);
                                $value.data('selected', response['preview']);
                                $name.val(response['preview']);

                                //polozka muze obsahovat hodnoty, ktere maji byt
                                //dovyplneny na formulari
                                if (typeof response.fill !== 'undefined') {
                                    for (k in response.fill) {
                                        $('input[name="'+k+'"],textarea[name="'+k+'"],select[name="'+k+'"]').val(response.fill[k]);
                                    }
                                }
                            }
                        });

                        //zamezeni defaultni akce anchoru
                        return false;
                    });
                }
            
            });
            
        }
      
    };

    $.fn.AppFormItemRelSelect = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemRelSelect');
            
        }
        
        return this;

    };

})( jQuery );

