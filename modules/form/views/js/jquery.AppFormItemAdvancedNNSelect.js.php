// <script>
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemAdvancedNNSelect';

   

    /**
     * Metody pro tento plugin.
     */
    var methods = {
        
        init: function( options ) {
            
            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
                data_url : "",
                preview : "",
                remove_interval: 10
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
                
                // Pole casovacu pro jednotlive nehlavni prvky
                var removeTimeouts = [];
                
                
                var onCheckboxChangeHandler = function($t) {
                    // Skryti/zobrazeni mini formulare
                    if ($t.attr('checked')) {
                        $(".form_"+$t.val()).fadeIn('fast');
                        $(".item_"+$t.val(), $this).removeClass('inactive').addClass('active');
                    } else {
                        $(".form_"+$t.val()).hide();
                        $(".item_"+$t.val(), $this).removeClass('active').addClass('inactive');
                    }
                    // Zpracovani celeho prvku, pokud je NEhlavni
                    if ($t.attr('main') == '0' && default_params.remove_interval != 0) {
                        // Pokud byl zatrzen, zrusime removeTimeout
                        var id = $t.val();
                        // Cely "prvek"
                        var $item = $(".item_"+id, $this);
                        var timer = removeTimeouts[id];
                        if ($t.attr('checked') && typeof(timer) != 'undefined' && timer) {
                            clearTimeout(timer);
                            removeTimeouts[id] = false;
                        }
                        // Pokud byl odskrtnut, pridame removeTimeout
                        if ( ! $t.attr('checked') && ! timer) {
                            removeTimeouts[id] = setTimeout(function(){
                                $item.fadeOut('medium', function() { $(this).remove() });
                            }, default_params.remove_interval*1000);
                        }
                    }
                }
                
                // Inicializace checkboxu
                $("input:checkbox[name$='[selected][]']", $this).change(function(){
                    onCheckboxChangeHandler($(this));
                    return false;
                });
                
                var blink = function(item)
                {
                    var interval = 150;
                    item.animate({
                       opacity: 0.25 
                    }, interval, function(){
                            item.animate({
                                opacity: 1
                        }, interval, function(){
                            item.animate({
                                opacity: 0.25 
                            }, interval, function(){
                                    item.animate({
                                    opacity: 1
                                }, interval)
                            });
                        })
                    });
                }
                
                
                // Pridani noveho prvku
                var addItem = function(name, id) 
                {
                    // Div celeho prvku - bud nacteme existujici nebo naklonujeme novy
                    var $item;
                    // Zda mame zablikat - to chceme pokud prvek jiz ve formulari je
                    var do_blink = false;
                    if ($(".item_"+id, $this).size()) {
                        $item = $(".item_"+id, $this);
                        // Zrusime pripadny timeout
                        var timer = removeTimeouts[id];
                        if (typeof(timer) && timer) {
                            clearTimeout(timer);
                            removeTimeouts[id] = false;
                        }
                        // Zablikame
                        do_blink = true;
                    } else {
                        // Kopie skryteho prvku
                        $item = $(".item_0", $this).clone();
                        // Prejmenovani tridy
                        $item.removeClass('item_0').addClass('item_'+id);
                        // Prejmenovani id checkboxu
                        var $pom = $("input:checkbox[id$='_']", $item);
                        $pom.val(id);
                        $pom.attr('checked', true);
                        $pom.attr('id', $pom.attr('id')+id)
                        // Pridani event handleru k checkboxu
                            //.click(onCheckboxChangeHandler)
                            .change(function(){
                                return onCheckboxChangeHandler($(this));
                            });
                        // Prejmenovani FOR atributu u labelu
                        $pom = $("label[for$='_']", $item);
                        $pom.attr('for', $pom.attr('for')+id);
                        // Nastaveni hodnoty labelu
                        $pom.html(name);
                        // Prejmenovani tridy formu
                        $pom = $(".form", $item);
                        $pom.removeClass('form_0').addClass('form_'+id);
                        // Prejmenovani vsech prvku ve formulari
                        $("*[name*='[0]']", $pom).each(function(){
                            var $t = $(this);
                            var name = $t.attr('name').replace('[0]', '[' + id + ']');
                            $t.attr('name', name);
                        });
                        // Pridame prvek do stranky
                        $(".items", $this).append($item);
                        $item.show();
                    }
                    $("input:checkbox[id$="+id+"]", $item).attr('checked', 'checked');
                    // Zobrzime formular - jeho rodic by jiz mel byt videt
                    var $form = $(".form", $item);
                    $form.show();
                    $("input,textarea,select", $form).first().focus();
                    if (do_blink) blink($item);
                }
                
                
                // Inicializace autocompletu
                
                // Najdeme si input pro NAME hodnotu (label), ktery je potomkem $this uzlu
                var $name = $('input[name$="[autocomplete]"]', $this);
                
                
                var onFocusHandler = function(){
                    if (this.value == "") {
                        $(this).autocomplete('search', '');
                    }
                };
                var onChangeHandler = function(){
                    $name.val('');
                };
                
                $name.autocomplete({
                    source: function( request, response ) {
                        
                        var pom_data = {
                                preview: settings.preview,
                                _q: request.term,
                                _ps:settings._ps
                            };
                                                
                        $.ajax({
                            // Sestaveni URL adresy na poradac prislusneho objektu
                            url: settings.data_url,
                            dataType: "json",
                            data: pom_data,
                            success: function( data ) {
                                response( $.map( data, function( item ) {
                                    return {
                                        value: item.name,
                                        id:    item.value
                                    }
                                }));
                            }
			});
                    },
                    minLength: 0,
                    select: function( event, ui ) {
                        addItem(ui.item.value, ui.item.id);
                        // Zabranime zobrazeni textu v inputu
                        ui.item.value = '';
                        ui.item.id = '';
                    },
                    open: function(){
                        $(this).unbind('focus', onFocusHandler);
                        $(this).unbind('change', onChangeHandler);
                    },
                    close: function(){
                        $(this).bind('focus', onFocusHandler);
                        $(this).bind('change', onChangeHandler);
                    }
                }).focus(onFocusHandler).change(onChangeHandler);


            });
            
        }
      
    };

    $.fn.AppFormItemAdvancedNNSelect = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemAdvancedNNSelect');
            
        }
        
        return this;

    };

})( jQuery );

