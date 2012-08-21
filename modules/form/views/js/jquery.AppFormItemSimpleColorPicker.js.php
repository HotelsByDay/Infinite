// <script>
(function( $ ) {

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemSimpleColorPicker';


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

                // @todo - uncomment after getting working farbtastic version
                $('input[type="text"]', $this).miniColors({
                    change: function(hex, rgb) {
                        // @todo - refaktorizovat - prepsat na $end_input.trigger('changing');
                        $this.parents('.<?= AppForm::FORM_CSS_CLASS ?>:first').objectForm('fireEvent', 'changing');
                    },
                    open: function(hex, rgb) {
                        // @todo - refaktorizovat - prepsat na $end_input.trigger('changing');
                        $this.parents('.<?= AppForm::FORM_CSS_CLASS ?>:first').objectForm('fireEvent', 'changing');
                    }
                });

            });
        }
    };

    $.fn.AppFormItemSimpleColorPicker = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemSimpleColorPicker');
            
        }
        
        return this;

    };

})( jQuery );
