//<script>
/**
 * Tento plugin predstavuje abstrakci nad formularem pro vlozeni filtrovacich 
 * parametru.
 *
 * Plugin poskytuje metody setValues, getValues a clear pro jednotnou praci
 * s formulare.
 *
 * Tato abstrakce je potreba kvuli tomu ze napriklad pole s naseptavacem
 * obsahuji dva inputy a je potreba specificky nastavit jejoch hodnoty, dale
 * pak jsou casto na filtrech kombinace selectu (kategorie-podkategorie), ktere
 * take vyzaduji specificky zpusob nastaveni hodnoty.
 *
 * S timto pluginem spolupracuje jquery.objectFilter. Ten nesaha primo na
 * formularove prvky, ale pouze skrze metody tohoto pluginu.
 *
 */
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'objectFilterForm';

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

            });
        },

        /**
         * Tato metoda slouzi k ziskani aktualnich hodnot formulare.
         *
         * Vraci objekt, ktery obsahuje hodnoty jednotlivych polozek.
         */
        getValues: function($_this) {

            var values = new Object();

            this.find('input,textarea,select').each(function(){
                if (typeof $(this).attr('name') !== 'undefined') {
                    //vyjimka pro radio a checkbox
                    if ($(this).is(':checkbox')) {
                        if ( ! $(this).is(':checked')) {
                            values[$(this).attr('name')] = '';
                        } else {
                            values[$(this).attr('name')] = $(this).val();
                        }
                    } else if ($(this).is(':radio')) {
                        if ( ! $(this).is(':checked')) {
                            return;
                        }
                        values[$(this).attr('name')] = $(this).val();
                    } else {
                        values[$(this).attr('name')] = $(this).val();
                    }
                }
            });
            return values;
        },

        clear: function($_this) {
            return this.objectFilterForm('_clear');
        },

        /**
         * Tato metoda slouzi k "vycisteni" obsahu formulare. Do vsech vstupnich
         * poli vklada prazdne hodnoty.
         *
         */
        _clear: function($_this) {

            this.find('input[type="text"],input[type="hidden"],select,textarea').val('');

        },

        setValues: function($_this, values) {
            return this.objectFilterForm('_setValues', values);
        },


        /**
         * Tato metoda slouzi k nastaven hodnot na formulari.
         * Jako parametr ocekava pole s jednotlivymi polozkamy, ktere do
         * formulare jednoduse pomoci jquery.val() vlozi.
         */
        _setValues: function($_this, values) {

            for (attr in values) {
                var $item = this.find("[name=\""+attr+"\"]");
                if ($item.length != 0) {
                    if ($item.is(':checkbox')) {
                        if (values[attr] != '') {
                            $item.attr('checked', true);
                        } else {
                            $item.attr('checked', false);
                        }
                    } else if ($item.is(':radio')) {
                        var value = values[attr];
                        // Find radio with given value
                        $item.filter('[value="' + value + '"]').attr('checked', true);
                    } else {
                        //hodnoty vlozim do formulare
                        $item.val(values[attr]);
                    }
                }
            }

        }
    };

    $.fn.objectFilterForm = function( method ) {

        if ( methods[ method ] ) {

            return methods[ method ].apply( this, arguments );

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.objectFilterForm');

        }

        return this;

    };

    $.fn.objectFilterForm.methods = methods;

})( jQuery );

$(document).ready(function(){
    $("#main_data_filter").objectFilterForm('init');
});
