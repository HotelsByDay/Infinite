// <script>
    

(function( $ ) {

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemDateTime';

   
    /**
     * Metody pro tento plugin.
     */
    var methods = {
        
        init: function( options ) {
            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
                date_format: 'd.m.yy'
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
                
                // Najdeme si input pro date, ktery je potomkem $this uzlu
                var $date = $('input[name$="[date]"]', $this);
                // Stejne tak pro time
                var $time = $('input[name$="[time]"]', $this);

                $date.datepicker({ dateFormat: settings.date_format });
            });
            
        }
      
    };

    $.fn.AppFormItemDateTime = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemDateTime');
            
        }
        
        return this;

    };

})( jQuery );

