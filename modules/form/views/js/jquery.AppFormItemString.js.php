// <script>
    

(function( $ ) {

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemString';

   
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

                var $input = $('input[type="text"]', $this);

                // If current item input has a placeholder attr
                if ($input.attr('placeholder') != '') {
                    // If placeholder option is not supported by the browser
                    var test = document.createElement('input');
                    if ( ! ('placeholder' in test)) {

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

                        // If input value is currently empty - place placeholder in it
                        if ($input.val() == '') {
                            $input.val($input.attr('placeholder'));
                            // Add placeholder class (for gray text)
                            $input.addClass('placeholder');
                        }
                    }
                }

            });
            
        }
      
    };

    $.fn.AppFormItemString = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemString');
            
        }
        
        return this;

    };

})( jQuery );

