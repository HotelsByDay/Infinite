// <script>
(function( $ ) {

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemDateInterval';

   

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

                var $from_input = $("input[name$='[from]']");
                var $to_input = $("input[name$='[to]']");

                // Pokud prislo nastaveni, tak mergnu s defaultnimi hodnotami
                var params = $.extend(true, settings, options );

                $(function() {
                    $from_input.datepicker({
                        changeMonth: true,
                        numberOfMonths: params.months_count,
                        dateFormat: params.date_format,
                        onSelect: function( selectedDate ) {
                            $to_input.datepicker( "option", "minDate", selectedDate );
                        }
                    });
                    $to_input.datepicker({
                        changeMonth: true,
                        numberOfMonths: params.months_count,
                        dateFormat: params.date_format,
                        onSelect: function( selectedDate ) {
                            $from_input.datepicker( "option", "maxDate", selectedDate );
                        }
                    });
                });

            });
            
        }
      
    };

    $.fn.AppFormItemDateInterval = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemDateInterval');
            
        }
        
        return this;

    };

})( jQuery );
