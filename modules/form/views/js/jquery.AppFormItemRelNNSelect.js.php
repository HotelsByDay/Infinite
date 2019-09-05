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
                var $check_all = $('a.check_all', $this);
                var $uncheck_all = $('a.uncheck_all', $this);

                var $search = $('input.fast_search', $this);

                $search.on('keyup', function() {
                    var query = $search.val().trim();
                    var patt = new RegExp(query, 'gi');
                    console.log();
                    $this.find(':checkbox').each(function(){
                        var $ch = $(this);
                        if (patt.test($ch.parents('label:first').text()) && query !== '') {
                            $ch.parents('.item:first').show();
                        } else {
                            if (query === '' && $ch.is(':checked')) {
                                $ch.parents('.item:first').show();
                            } else {
                                // console.log('*' + $ch.parents('label:first').text() + '*');
                                $ch.parents('.item:first').hide();
                            }
                        }
                    });
                });


                $check_all.bind('click', function() {
                    var changed = false;
                    $this.find(':checkbox').not(':checked').each(function(){
                        $(this).attr('checked', true);
                        changed = true;
                    });
                    if (changed) $this.trigger('change');
                    return false;
                });
                $uncheck_all.bind('click', function(){
                    var changed = false;
                    $this.find(':checkbox:checked').each(function(){
                        $(this).removeAttr('checked');
                        changed = true;
                    });
                    if (changed) $this.trigger('change');
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

