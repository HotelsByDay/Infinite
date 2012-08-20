// <script>
(function( $ ) {

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemCssSize';


    /**
     * Metody pro tento plugin.
     */
    var methods = {
        
        init: function( options ) {
            
            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
            };

            settings = $.extend(settings, options);

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

                var $input = $('input[type="text"]', $this);
                var $slider = $('.slider', $this);


                /**
                 * Volano po zmene hodnoty slideru
                 * @param value
                 */
                var sliderChanged = function(value)
                {
                    setValue(value);
                    $this.trigger('change');
                }

                // Pokud se ma zobrazovat slider, pak ho inicializujeme
                if (settings.show_slider) {
                    var value = $input.val();
                    if ( ! value) {
                        value = 0;
                    }
                    $slider.slider({
                        value: value,
                        min: settings.min,
                        max: settings.max,
                        slide: function(event, ui) {
                            sliderChanged(ui.value);
                        },
                        change: function(event, ui) {
                            sliderChanged(ui.value);
                        }
                    });
                }


                /**
                 * Doslo ke zmene hodnoty v inputu
                 * @param event
                 */
                var valueChanged = function($input)
                {
                    // Zkontrolujeme ze value je v <min, max> intervalu
                    var value = parseInt($input.val());
                    var units = false; // pouziji se vychozi jednotky
                    if (value > settings.max) {
                        value = max;
                    }
                    if (value < settings.min) {
                        value = min;
                    }

                    // Nastavime do inputu validni hodnotu
                    setValue(value, units);
                }

                // Nastavi hodnotu do value inputu
                var setValue = function(value, units)
                {
                    // Pokud nejsou uvedeny jednotky - pouzijeme ty co jsou v inputu ted
                    if (typeof units === 'undefined' || units === false) {
                        units = $input.val().replace(/^[0-9]*/, '');
                    }

                    // Zkontrolujeme ze uvedene jednotky jsou povolene
                    var re = '([1-9][0-9]+(' + settings.enabled_units.join('|') + ')|0)';
                 //   alert(re);
                    re = new RegExp(re);
                    if ( ! re.test($input.val())) {
                        if (settings.enabled_units.length) {
                            // Zvolime prvni povolene jednotky
                            units = settings.enabled_units[0];
                        }
                        else {
                            // Zadne povolene jednotky - nevalidni config
                            value = '';
                            units = '';
                        }
                    }

                    // Nastavime hodnotu do inputu
                    $input.val(value + units);
                    // Aktualizujeme slider
                    if (settings.show_slider && $slider.slider('value') != value) {
                        $slider.slider('value', value);
                    }
                }


                $input.change(function(){valueChanged($(this))});


            });
            
        }
      
    };

    $.fn.AppFormItemCssSize = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemCssSize');
            
        }
        
        return this;

    };

})( jQuery );
