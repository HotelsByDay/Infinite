// <script>
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemRelNNSelect';

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

                //Pokud prislo nastaveni, tak mergnu s defaultnimi hodnotami
                var params = $.extend(true, settings, options);

                // Tlacitka check/uncheck all
                var $check_all = $('.check_all', $this);
                var $uncheck_all = $('.uncheck_all', $this);


                $check_all.bind('click', function(){
                    $this.find(':checkbox').each(function(){
                        $(this).attr('checked', true);
                    });
                    return false;
                });
                $uncheck_all.bind('click', function(){
                    $this.find(':checkbox').each(function(){
                        $(this).removeAttr('checked');
                    });
                    return false;
                });

                $(":checkbox", $this).each(function(){
                    var $c = $(this);
                    if ($c.is(':checked')) {
                        // Zobrazime a aktivujeme input
                        $c.parents('.item:first').find('.note_outer').show().find('input').removeAttr('disabled');
                    } else {
                        // Skryjeme, smazeme a deaktivujeme input
                        var $no = $c.parents('.item:first').find('.note_outer').hide().find('input').val('').attr('disabled', true);
                    }
                });

                // Pripojime event handler na checkbox.change event
                $(":checkbox", $this).on('change', function(){
                    var $c = $(this);
                    if ($c.is(':checked')) {
                        // Zobrazime a aktivujeme input
                        $c.parents('.item:first').find('.note_outer').show().find('input').removeAttr('disabled');
                    } else {
                        // Skryjeme, smazeme a deaktivujeme input
                        var $no = $c.parents('.item:first').find('.note_outer').hide().find('input').val('').attr('disabled', true);
                    }
                });
                
            
            }); // end each
            
        } // end init
      
    };

    $.fn.AppFormItemRelNNSelect = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemRelNNSelect');
            
        }
        
        return this;

    };

})( jQuery );

