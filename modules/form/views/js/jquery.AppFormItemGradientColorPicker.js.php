// <script>
(function( $ ) {

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemGradientColorPicker';


    /**
     * Metody pro tento plugin.
     */
    var methods = {
        
        init: function( options ) {
            
            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
                uid: ''
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

                var $color_input = $('input[name*="[color]"]', $this);
                var $slider_input = $('input[name*="[slider]"]', $this);
                var $start_input = $('input[name*="[start]"]', $this);
                var $end_input = $('input[name*="[end]"]', $this);
                var $slider = $('.slider', $this);


                // From jquery mobile ThemeRoller
                var computeGradient = function( color, slider_value ) {
                    var color_arr = color.split( "" );

                    var red = parseInt( (color_arr[1] + color_arr[2]), 16 );
                    var green = parseInt( (color_arr[3] + color_arr[4]), 16 );
                    var blue = parseInt( (color_arr[5] + color_arr[6]), 16 );

                    var convex, red_start, green_start, blue_start, percent;

                    if( slider_value >= 40 ) {
                        convex = 1;
                        percent = 1 + ( slider_value - 40 ) / 100;
                    } else {
                        convex = 0;
                        percent = 1 + ( 40 - slider_value ) / 100;
                    }
                    if( percent * red > 255 ) {
                        red_start = "FF";
                    } else {
                        red_start = padNumber( Math.floor(percent * red).toString( 16 ), 2 );
                    }
                    if( percent * green > 255 ) {
                        green_start = "FF";
                    } else {
                        green_start = padNumber( Math.floor(percent * green).toString( 16 ), 2 );
                    }
                    if( percent * blue > 255 ) {
                        blue_start = "FF";
                    } else {
                        blue_start = padNumber( Math.floor(percent * blue).toString( 16 ), 2 );
                    }

                    if( convex ) {
                        percent = ( 100 - (slider_value - 40) ) / 100;
                    } else {
                        percent = ( 100 - (40 - slider_value) ) / 100;
                    }

                    var red_end = padNumber( Math.floor(percent * red).toString( 16 ), 2 );
                    var green_end = padNumber( Math.floor(percent * green).toString( 16 ), 2 );
                    var blue_end = padNumber( Math.floor(percent * blue).toString( 16 ), 2 );

                    var start, end;
                    if( convex ) {
                        start = "#" + red_start + "" + green_start + "" + blue_start + "";
                        end = "#" + red_end + "" + green_end + "" + blue_end + "";
                    } else {
                        start = "#" + red_end + "" + green_end + "" + blue_end + "";
                        end = "#" + red_start + "" + green_start + "" + blue_start + "";
                    }
                    return [start, end];
                }
                var grayValue = function( color ) {
                    var color_arr = color.split( "" );

                    var red = parseInt( ( color_arr[1] + color_arr[2] ), 16 );
                    var green = parseInt( ( color_arr[3] + color_arr[4] ), 16 );
                    var blue = parseInt( ( color_arr[5] + color_arr[6] ), 16 );

                    return ( red + green + blue ) / 3;
                }
                var padNumber = function( n, len ) {
                    var str = '' + n;
                    while (str.length < len) {
                        str = '0' + str;
                    }
                    return str;
                }


                /**
                 * Volano po zmene hodnoty slideru
                 * @param value
                 */
                var sliderChanged  = function(value)
                {
                    // Spocteme novy gradient
                    var gradient = computeGradient($color_input.val(), value);
                    // Ulozime hodnotu slideru do hindden inputu
                    $slider_input.val(value);
                    // Nastavime start/end barvy
                    $start_input.val(gradient[0]).trigger('change');
                    $end_input.val(gradient[1]).trigger('change');
                }

                // Inicializujeme color-pickery
                $color_input.miniColors();
                $start_input.miniColors();
                $end_input.miniColors();

                var beforeFormSave = function(params)
                {
                        $color_input.miniColors('destroy');
                        $start_input.miniColors('destroy');
                        $end_input.miniColors('destroy');
                }
                // A zajistime jejich odebrani pred odeslanim formulare
                console.log('subscribing appForm event beforeSave');
                $this.parents('.<?= AppForm::FORM_CSS_CLASS ?>:first').objectForm('subscribeEvent', 'beforeSave', beforeFormSave);




                $slider.slider({
                    value: $slider_input.val(),
                    slide: function(event, ui) {
                        sliderChanged(ui.value);
                    },
                    change: function(event, ui) {
                        sliderChanged(ui.value);
                    }
                });


                /**
                 * Doslo ke zmene barvy v color-pick inputu
                 * @param event
                 */
                var colorChanged = function($input)
                {
                    var color = $input.val();
                    // Nastavime barvu pozadi
                    $input.css('backgroundColor', color);
                    // A barvu pisma - aby bylo kontrastni s pozadim
                    if (grayValue(color) > 127) {
                        $input.css('color', 'black');
                    } else {
                        $input.css('color', 'white');
                    }
                }

                $color_input.change(function(){
                    colorChanged($(this));
                    var $handle = $slider.find('.ui-slider-handle');
                    $handle.css('backgroundImage', 'none').css('backgroundColor', $color_input.val());

                });
                $start_input.change(function(){colorChanged($(this))});
                $end_input.change(function(){colorChanged($(this))});
                colorChanged($color_input);
                colorChanged($start_input);
                colorChanged($end_input);
                $slider.find('.ui-slider-handle').css('backgroundImage', 'none').css('backgroundColor', $color_input.val());

            });
            
        }
      
    };

    $.fn.AppFormItemGradientColorPicker = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemGradientColorPicker');
            
        }
        
        return this;

    };

})( jQuery );
