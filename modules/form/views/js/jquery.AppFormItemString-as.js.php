

(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemString';

    /**
     * Defaultni hodnoty pro parametry a nastaveni pluginu
     */
    var settings = {
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

            return this.each(function(){

                var $this = $(this);

                //ulozim si aktualni nastaveni pluginu
                methods._setData( $this , {
                    settings: settings
                });

                
            
            });

        },


        /**
         *
         */
        _setData: function( $this, key, value ) {

            if (typeof key === 'object' ) {

                //budu extendovat to co mam aktualne v datech ulozene
                $.extend(key, $this.data( plugin_name_space) );

                $this.data( plugin_name_space, key );

            } else {
                
                var current_data = $this.data( plugin_name_space );

                if (typeof current_data === 'undefined' ) {
                    current_data = {
                        key: value
                    };
                } else {
                    current_data[key] = value;
                }

                $this.data( plugin_name_space, current_data )
                
            }

        },

        /**
         *
         */
        _getData: function( $this, key ) {

            var current_data = $this.data( plugin_name_space );

            return current_data[ key ];

        },
        
        _log: function( text ) {
            if ( typeof console !== 'undefined') {
                console.log( text );
            }
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