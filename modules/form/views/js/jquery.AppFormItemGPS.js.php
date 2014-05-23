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

                var $form = $this.parents('.<?= AppForm::FORM_CSS_CLASS ?>');

                var $country_input = $form.find('[name="' + settings.name_country + '"]');
                var $state_input = $form.find('[name="' + settings.name_state + '"]');
                var $city_input = $form.find('[name="' + settings.name_city + '"]');
                var $zip_input = $form.find('[name="' + settings.name_zip + '"]');
                var $address_input = $form.find('[name="' + settings.name_address + '"]');

                var map = null;
                var geocoder = null;

                var lat = 50.1;
                var lng = 0;
                var lat_lng_provided = false;

                var marker = false;

                // Ulozime nastaveni
                $this.data('settings', settings);

                // inputy pro zobrazeni gps souradnic
                var $latitude_input = $this.find('input[name$="[latitude]"]');
                var $longitude_input = $this.find('input[name$="[longitude]"]');

                // Load GPS from the inputs
                if ($latitude_input.val()) {
                    lat = parseFloat($latitude_input.val());
                    if ($longitude_input.val()) {
                        lat_lng_provided = true;
                    }
                }
                if ($longitude_input.val()) {
                    lng = parseFloat($longitude_input.val());
                }

                // Load new GPS coordinates based on given address
                var loadNewGps = function()
                {
                    var address = [];
                    var inputs = [ $address_input, $zip_input, $city_input, $state_input, $country_input ];
                    for (var key in inputs) {
                        var $i = inputs[key];
                        if ($i.length == 1) {
                            address.push($i.val());
                        }
                    }
                    address = address.join(' ');
                    geocoder.geocode( { 'address': address}, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            var latlng = results[0].geometry.location;
                            updateGps(latlng);
                            updateMarker(latlng);
                        }
                    });
                }

                // Bind listener on address components changes
                var inputs = [$country_input, $state_input, $city_input, $zip_input, $address_input];
                for (var key in inputs) {
                    var $i = inputs[key];
                    if ($i.length == 1) {
                        $i.on('change', loadNewGps);
                    }
                }

                // Update marker position on manual gps editation
                $('input[name$="[latitude]"], input[name$="[longitude]"]', $this).on('change', function(e){
                    var latlng = new google.maps.LatLng($latitude_input.val(), $longitude_input.val());
                    updateMarker(latlng);
                });

                // Inicializace mapy
                var initGoogleMap = function () {
                    var zoom = 1;
                    if (lat_lng_provided) {
                        zoom = 17;
                    }
                    var mapOptions = {
                        zoom: zoom,
                        center: new google.maps.LatLng(lat, lng)
                    };
                    map = new google.maps.Map($this.find('.map-canvas').get(0),
                        mapOptions);
                    geocoder = new google.maps.Geocoder();
                };

                // Update marker position in the map (after address change detected)
                var updateMarker = function(latlng){
                    map.setCenter(latlng);
                    if ( ! marker) {
                        map.setZoom(17);
                        marker = new google.maps.Marker({
                            map: map,
                            position: latlng,
                            draggable: true
                        });
                        google.maps.event.addListener(marker, 'dragend', function(event){
                            updateGps(event.latLng);
                        });

                    } else {
                        marker.setPosition(latlng);
                    }
                }

                // Update latitude and longitude values in inputs
                var updateGps = function(latlng)
                {
                    $latitude_input.val(latlng.lat());
                    $longitude_input.val(latlng.lng());
                }

                // initialize google map
                initGoogleMap();

                // place marker to the map - if coords are provided
                if (lat_lng_provided) {
                    updateMarker(new google.maps.LatLng(lat, lng));
                }


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

