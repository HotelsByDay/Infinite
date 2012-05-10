/**
 *
 */
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'agendaFilterForm';

    /**
     * Defaultni hodnoty pro parametry a nastaveni pluginu
     */
    var default_settings = {
    };

    /**
     * Metody pro tento plugin.
     */
    var methods = {

        init: function( options ) {

            this.each(function(){

                var $_this = $(this);

                //tlacitka pro prepina mezi jednotlivymi tydny
                $(".prev_week").click(function(){
                    var current_week = $("#agenda_filter-w").val();
                    current_week = parseInt(current_week) - 1;
                    $("#agenda_filter-w").val(current_week);
                    //vyvolam nove nacteni dat
                    $_this.find('.submit_filter').trigger('click');
                    return false;
                });
                $(".next_week").click(function(){
                    var current_week = $("#agenda_filter-w").val();
                    current_week = parseInt(current_week) + 1;
                    $("#agenda_filter-w").val(current_week);
                     //vyvolam nove nacteni dat
                    $_this.find('.submit_filter').trigger('click');
                    return false;
                });
                //tlacitko pro prepnuti na 'tento tyden'
                $(".this_week").click(function(){
                    //hodnota 'this' bude interpretovana na serveru jako tento tyden
                    $("#agenda_filter-w").val(options['this_week']);
                     //vyvolam nove nacteni dat
                    $_this.find('.submit_filter').trigger('click');
                    return false;
                });
                
                //prvky pro vyber pozadovaneho typu budou jQuery.button a
                //budou fungovat jak checkboxy
                $(".type_select", $_this).buttonset();

                //vyber datumu od,do
                $("#agenda_filter-date_to,#agenda_filter-date_from").datepicker({ dateFormat: 'd.m.yy' });

                //tlacitka pro navigaci mezi tydny
                $("#agenda_filter-prev_week,#agenda_filter-this_week,#agenda_filter-next_week").button();

            });
        },

        _log: function( text ) {
            if ( typeof console !== 'undefined') {
                console.log( text );
            }
        }

    };

    $.fn.agendaFilterForm = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {

            return methods[ method ].apply( this, arguments );

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.agendaFilterForm');

        }

        return this;

    };

})( jQuery );
