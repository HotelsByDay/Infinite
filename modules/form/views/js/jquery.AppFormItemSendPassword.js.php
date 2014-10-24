
// <script>

(function( $ ) {

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemSendPassword';

    /**
     * Defaultni hodnoty pro parametry a nastaveni pluginu
     */
    var settings = {
        reset_pass_url: '',
        success_msg: 'Password successfully reset and sent to the user\'s e-mail.',
        error_msg: 'An error occurred during reset. '
    };

    /**
     * Metody pro tento plugin.
     */
    var methods = {

        init: function( options ) {

            //Pokud prislo nastaveni, tak mergnu s defaultnimi hodnotami
            if ( options ) {
                $.extend( settings, options );
            }

            return this.each(function() {

                var $this = $(this);

                $this.find('.reset_pass').on('click', function(){
                    var $b = $(this);
                    if ($b.hasClass('disabled')) return;
                    $b.addClass('disabled');
                    var $p = $b.parent();
                    $._ajax({
                        url: settings.reset_pass_url,
                        method: 'GET',
                        success: function (r) {
                            if (r.success) {
                                $b.remove();
                                $p.append('<div class="alert alert-info">' + settings.success_msg + '</div>');
                            } else {
                                $b.remove();
                                $p.append('<div class="alert alert-danger">' + settings.error_msg + r.error + '</div>');
                            }
                        }
                    });
                    return false;
                });

            });

        }

    };

    $.fn.AppFormItemSendPassword = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {

            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemSendPassword');

        }

        return this;

    };

})( jQuery );