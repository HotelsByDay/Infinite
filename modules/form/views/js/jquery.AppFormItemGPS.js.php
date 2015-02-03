//<script>

(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemGPS';

    /**
     * Metody pro tento plugin.
     */
    var methods = {
        
        init: function( options ) {
            
            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
                zoom : 15,
                readonly : false
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

                var map = null;

                var marker = false;

                var gmarker = false;
                var amarker = false;

                // Ulozime nastaveni
                $this.data('settings', settings);

                // inputy pro zobrazeni gps souradnic
                var $latitude_input = $this.find('input[name$="[latitude]"]');

                var $longitude_input = $this.find('input[name$="[longitude]"]');


                //pred zmenou pozice si originalni souradnice ulozim a zobrazim
                //tlacitko, ktere slouzi pro jejich obnoveni
                var $show_original_position_button = $this.find('.show_original_position');


                // Update marker position on manual gps editation
                $('input[name$="[latitude]"], input[name$="[longitude]"]', $this).on('change', function(e){
                    displayMarkerAtCurrentAddress();
                });

                var initGoogleMap = function () {
                    //jen pokud jsme v kompatibilnim prohlizeci
                    if (typeof GBrowserIsCompatible == 'function' && GBrowserIsCompatible()) {
                        map = new GMap2($this.find('.map .canvas').get(0));
                        //nastavim typ mapy
                        map.setMapType(G_NORMAL_MAP);
                        //custom nastaveni ovladacich prvku
                        var customUI = map.getDefaultUI();
                        customUI.maptypes.satellite = false;
                        customUI.maptypes.hybrid = true;
                        customUI.maptypes.physical = false;
                        customUI.controls.maptypecontrol = true;
                        customUI.controls.menumaptypecontrol = true;
                        map.setUI(customUI);

                        //vypnuti skrolovani mapy pomoci kolecka mysi
                        map.disableScrollWheelZoom();

                        // Zde potrebujeme mapu celeho sveta
                        map.setCenter(new GLatLng(50.1, 0), 0);
                    }
                };


                /**
                 * Metoda ocekava objekt obsahujici komponenty adresy a ty vklada
                 * do formulare do readonly poli.
                 *
                 */
                var applyAddress = function (components) {

                    //projdu komponenty a umistim do input, ktere odpovidaji nazvum
                    //jednotlivych polozek - jen latitude a longitude
                    for (attr in components) {
                        if ($this.find('input[name$="['+attr+']"]').length != 0 && $this.find('input[name$="['+attr+']"]').val() == '') {
                            $this.find('input[name$="['+attr+']"]').val(components[attr]);
                        }
                    }
                };


                /**
                 * Z formulare zobrazujiciho adresu si vytahne souradnice a zobrazi marker
                 * na dane pozici.
                 */
                var displayMarkerAtCurrentAddress = function (change_zoom) {

                    if (typeof change_zoom == 'undefined') {
                        change_zoom = false;
                    }
                    //prectu souradnice z formularovich poli
                    var lat = $.trim($latitude_input.val());
                    var lon = $.trim($longitude_input.val());

                    var importanceOrder = function(marker, b) {
                        return 1000000; //  * marker.importance;
                    };

                    //pokud nejsou obe vyplnene, tak se nebude pozice na mapce nastavovat
                    if (lat != '' && lon != '' && typeof map != 'undefined') {
                        if ( ! marker) {
                            var params = {
                                draggable: true
                                ,zIndexProcess: importanceOrder
                            };
                            marker = new GMarker(new GLatLng(lat, lon), params);
                            gmarker.importance = 3;
                            //do mapy vlozim marker
                            map.addOverlay(marker);

                        } else {
                            //nova pozice hlavniho draggable markeru
                            marker.setLatLng(new GLatLng(lat, lon));
                            marker.show();
                        }
                    } else {
                        //zrusim aktualni marker a dale nebudu nic provadet
                        if (marker) {
                            marker.hide();
                        }
                    }
                    if ( ! marker) {
                        return;
                    }

                    // Set map center and zoom level
                    if (change_zoom) {
                        map.setCenter(marker.getPoint(), settings.zoom);
                    } else {
                        map.setCenter(marker.getPoint());
                    }

                    //pridam listener - kdyz skonci dragovani tak si chci ulozit nove souradnice
                    //timto uzivateli dovolim explicitne definovat souradnice
                    GEvent.addListener(marker, "dragend", function() {

                        var point = marker.getPoint();

                        //mapu vycentruju na nove souradnice bodu
                        map.panTo(point);



                        //pred zmenou pozice balonku ulozim puvodni souradnice, ktere pujdou
                        //pomoci tlacitka obnovit
                        if (typeof $show_original_position_button.data('lat') === 'undefined'
                            && typeof $show_original_position_button.data('lon') === 'undefined') {

                            $show_original_position_button.data('lat', $latitude_input.val());
                            $show_original_position_button.data('lon', $longitude_input.val());

                            // Do not show this feature - it's not considering manual gps changes in inputs
                         //   $show_original_position_button.show();
                        }

                        //nove souradnice zapisu do inputu na formulari
                        $latitude_input.val(point.lat());
                        $longitude_input.val(point.lng());

                    });

                    //tlacitko pro zobrazeni puvodni souradnice pripravim pro dalsi pouziti
                    $show_original_position_button.removeData('lat');
                    $show_original_position_button.removeData('lon');
                    $show_original_position_button.hide();
                };


                // pro kliknuti na tlacitko pro obnoveni "puvodnich souradnic" se
                // puvodni souradnice vytahnou z jeho dat, vlozi se na formular a
                // zavola se metoda pro aktualizaci markeru na mape
                $(".show_original_position").click(function(){
                    if (typeof $(this).data('lat') !== 'undefined' && typeof $(this).data('lon') !== 'undefined') {
                        $this.find('input[name$="[latitude]"]').val($(this).data('lat'));
                        $this.find('input[name$="[longitude]"]').val($(this).data('lon'));

                        displayMarkerAtCurrentAddress();

                        return false;
                    }
                });

                //inicializace google mapky
                initGoogleMap();

                //na mape oznaci aktualne vybranou adresu podle GPS souradnic
                //pokud nejsou GPS souradnice definovany, tak neprovede nic
                displayMarkerAtCurrentAddress(true);

            });
            
        }

    };

    $.fn.AppFormItemGPS = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemGPS');
            
        }
        
        return this;

    };

})( jQuery );

