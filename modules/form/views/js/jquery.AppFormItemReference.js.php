
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemReference';

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
            
            //Pokud prislo nastaveni, tak mergnu s defaultnimi hodnotami
            $.extend( settings, options );

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
                
                // Najdeme si input pro relpreview hodnotu (label), ktery je potomkem $this uzlu
                var $relpreview = $('input[name$="[relpreview]"]', $this);
                // Stejne tak pro value hodnotu - id
                var $relid = $('input[name$="[relid]"]', $this);

                // Ulozime si aktualni jmeno, abychom mohli detekovat jeho zmenu v inputu
                $relpreview.data('selected', $relpreview.val());

                $relpreview.change(function(){
                    //podivam se na hodnotu, ktera byla uzivatelem vybrana
                    var selected_val = $(this).data('selected');
                    //pokud se lisi od te aktualni co je ted v inputu, tak to uzivatel
                    //prepsal a odstranim i hodnotu ID
                    if (selected_val != $(this).val()) {
                        $relid.val('');
                        $(this).val('');
                    }
                });

                //prvek pro vyber hodnoty 'reltype'
                var $reltype = $('select[name$="[reltype]"]', $this);

                //pri zmene hodnoty si ulozim aktualni stav poli relid a relpreview
                //abych byl schopen tam vratit puvodni hodnoty, kdyz uzivatel vybere
                //hodnotu pro kterou mel vazbu uz nastavenou
                $reltype.change(function(){

                    //najdu si vybrany option
                    $selected_option = $(this).find('option:selected');

                    //muze mit v datech ulozene hodnoty, ktere definuji vazbu na
                    //relacni prvek
                    $relid.val($selected_option.data('relid'));
                    $relpreview.val($selected_option.data('relpreview'));
                    $relpreview.data('selected', $selected_option.data('relpreview'));
                });

                //do option elementu selectboxu s reltype vlozim aktualni
                //vybrane hodnoty - to je proto abych byl schopen tyto
                //hodnoty nastavit kdyz uzivatel zacne vybirat ruzne moznosti
                //reltype a vrati se na nejakou pro kterou mel vazbu uz nastavenou
                $reltype_option = $reltype.find('option:selected');

                $reltype_option.data('relpreview', $relpreview.val());
                $reltype_option.data('relid', $relid.val());

                $relpreview.autocomplete({
                    source: function( request, response ) {
                        
                        var pom_data = {
                                _q: request.term
                            };

                        //podle hodnoty prvku 'relid' vyberu prislusnou URL adresu
                        //pro cteni dat
                        var reltype = $reltype.val();

                        //url pro cteni dat
                        var url = settings.data_url[reltype];

                        //pokud je explicitne definovane preview pro dany relacni objekt
                        //tak poslu jako jeden z argumentu
                        var preview = settings.preview[reltype];

                        if (typeof preview !== 'undefined') {
                            pom_data['preview'] = preview;
                        }

                        $._ajax({
                            // Sestaveni URL adresy na poradac prislusneho objektu
                            url: url,
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
                    //automaticky se vybere prvni polozka
                    autoFocus: true,
                    minLength: 0,
                    // Pri vyberu polozky uzivatelem
                    select: function( event, ui ) {
                        // Zaroven zmenime ID v hidden inputu
                        $relid.val(ui.item.id);
                        // A poznacime do jeho atributu jmeno maklere
                        $relpreview.data('selected', ui.item.value);

                        //do option elementu selectboxu s reltype vlozim aktualni
                        //vybrane hodnoty - to je proto abych byl schopen tyto
                        //hodnoty nastavit kdyz uzivatel zacne vybirat ruzne moznosti
                        //reltype a vrati se na nejakou pro kterou mel vazbu uz nastavenou
                        $reltype_option = $reltype.find('option:selected');

                        $reltype_option.data('relpreview', ui.item.value);
                        $reltype_option.data('relid', ui.item.id);
                    }
                    
                }).focus(function(){
                    if ($(this).hasClass('watermark')) {
                        this.value = '';
                        $(this).removeClass('watermark');
                    }
                    if (this.value == "") {
                        $(this).autocomplete('search', '');
                    }
                });
    
            });
            
        }
      
    };

    $.fn.AppFormItemReference = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemReference');
            
        }
        
        return this;

    };

})( jQuery );

