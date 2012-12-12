// <script>
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemSubCategorySelect';

   

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
                preview: null,
                attr: '',
                columns_count: 1
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
                var params = $.extend(true, settings, options );

                // Najdeme si div se subkategoriemi - div s checkboxy
                var $items = $('.items', $this);

                var $category = $('select', $this);

                var $form = $this.parents('.<?= AppForm::FORM_CSS_CLASS ?>:first');

                $category.change(function() {
                    //podivam se na hodnotu, ktera byla uzivatelem vybrana
                    var current_category = $category.val();

                    //defaultni parametry, ktere se budou posilat s kazdym pozadavkem
                    var post_data = {
                        preview: params.preview,
                        _ps: 10000
                    };
                    // Filtr podle zvolene kategorie
                    post_data[params.attr] = current_category;

                    $form.block();

                    $._ajax({
                        // Sestaveni URL adresy na poradac prislusneho objektu
                        url: settings.data_url,
                        dataType: "json",
                        data: post_data,
                        success: function( data ) {
                            // Smazeme aktualni checkboxu
                            $items.html('');
                            // Celkovy pocet polozek
                            var items_count = data.length;
                            // Minimalni velikost sloupce - v poctu polozek
                            var max_column_size = parseInt(items_count / params.columns_count);
                            if (items_count % params.columns_count) {
                                max_column_size++;
                            };

                            var items_in_column = 0;
                            var items_html = '<div class="column">';
                            for (var i in data)
                            {
                                // Pokud jsme dosahli pozadovaneho poctu polozek ve sloupci - uzavreme sloupec a otevreme novy
                                if (items_in_column >= max_column_size) {
                                    // Otevreme novy sloupec
                                    items_html += '</div><div class="column">';
                                    // Je v nem zatim 0 polozek
                                    items_in_column = 0;
                                }
                                var item = data[i];
                                var value = item.value;
                                var name = item.name;

                                items_html += '<div class="item">';
                                items_html += ' <label class="check checkbox"> ' + name;
                                items_html += '<input type="checkbox" id="item_' + params.attr + '_' + value + '" value="' + value + '" name="' + params.attr + '[id][' + value + ']" /> ';
                                items_html += '</label>';
                                items_html += '</div>';

                                // Zapocitame polozku ve sloupci
                                items_in_column++;
                            }
                            items_html += '</div>';
                            // Zobrazime nove checkboxu
                            $items.html(items_html);
                            // Odblokujeme ui formulare
                            $form.unblock();
                        }
                    });
                });
            });
        }

    }

    $.fn.AppFormItemSubCategorySelect = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemSubCategorySelect');
            
        }
        
        return this;

    };

})( jQuery );

