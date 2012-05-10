//<script>


(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemPropertyAddress';

    /**
     * Metody pro tento plugin.
     */
    var methods = {
        
        init: function( options ) {
            
            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
                property_location_url : '',
                property_address_url : '',
                location_zoom : 15,
                address_zoom : 17,
                readonly : true
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

                // Ulozime nastaveni
                $this.data('settings', settings);

                //vstupy podle kterych se vyhledavaji gps souradnice
                var $address_input = $this.find('input[name$="[address]"]');

                var $postalcode_input = $this.find('input[name$="[postal_code]"]');

                //inputy pro zobrazeni gps souradnic
                var $latitude_input = $this.find('input[name$="[latitude]"]');

                var $longitude_input = $this.find('input[name$="[longitude]"]');

                var $indexed_csc_input = $("input[name$='[property_location_indexed_csc]']", $this);

                // pri stisknuti klavesy enter v inputu dojde k vyhledani
                $address_input.keypress(function(e){
                    if (e.which == 13) {
                        $address_input.trigger('change');
                        return false;
                    }
                });

                /**
                 * Tohle se zavola pri zmene adresy/mesta/postalcode
                 */
                var search_gps = function()
                {
                    //predchozi pozadavek jeste nemusel dobehnout - zrusim jej
                    //i presto ze se aktualni pozadavek nemusi odeslat kvuli
                    //tomu ze uzivatel chce vyhledavat prazdnou hodnotu
                    var previous_request = $(this).data('request');
                    if (typeof previous_request !== 'undefined') {
                        previous_request.abort();
                        delete previous_request;
                        $this.removeClass('busy');
                    }

                    //pridam css tridu indikujici ze probiha pozadavek
                    $this.addClass('busy');

                    // Sestavim data, ktera se poslou v requestu
                    var data = {
                        address : $address_input.val(),
                        city : $indexed_csc_input.val(),
                        postalcode : $postalcode_input.val()
                    }

                    //odeslu na server a ocekavam odpoved ve formatu JSON
                    var jqXHR = $.getJSON(settings['property_address_url'], data, function(response) {

                        //tlaciku odeberu css tridu indikujici ze probiha cekani na pozadavek
                        // $submit_fulltext_search.removeClass('busy')

                        //pokud je v nase[tavaci 0 polozek, tak uzivateli zobrazim
                        //napovedu aby vice specifikoval lokalitu
                        if ( ! response || ! response.success) {
                            // alert('not success');
                            // $response_placeholder.html('<?= __('appformitempropertyaddress.hint_specify_query');?>');

                        } else {
                            //zobrazi adresu v GUI pro uzivatele
                            methods._applyAddress( $this, response );

                            //zobrazi na mape marker pro oznaceni aktualni adresy
                            methods._displayMarkerAtCurrentAddress( $this );

                            //schovam panel s vysledky vyhledavani
                            //$response_placeholder.hide();

                        }

                    });

                    //do dat si ulozim referenci na XHR objekt abych mohl
                    //aktualni pozadavek zrusit (abortovat) pokud uzivatel
                    //vyvola dalsi hledani pred tim nez toto dobhne
                    $(this).data('request', jqXHR);

                    return false;
                }


                // Inicializace autocomompletu pro vyber property_locationid (pokud neni readonly)
                if ( ! settings.readonly)
                {
                    $(".property_locationid", $this).AppFormItemRelSelect({
                        data_url : settings.property_location_url
                    });
                }



                // Po zmene value hodnoty (id rel zaznamu obstaravaneho RelSelectem)
                // @todo - mozna by tato funkcionalita mohla byt primo v RelSelect pluginu
                //         ale nejsem si jist zda je toto chovani vzdy zadouci (smazat filled hodnoty
                //         pri zaniku rel vazby)
                $("input[name$='[value]']", $this).on('change', function(){
                    // Pokud doslo ke smazani relacniho zaznamu, smazeme indexed csc
                    if ($(this).val() == '') $indexed_csc_input.val('');
                });

                // Po zmene adresy uzivatelem dojde ke zjisteni novych gps souradnic
                $("input[name$='[value]'], input[name$='[postal_code]'], input[name$='[address]']", $this).on('change', search_gps);




                // pro kliknuti na tlacitko pro obnoveni "puvodnich souradnic" se
                // puvodni souradnice vytahnou z jeho dat, vlozi se na formular a
                // zavola se metoda pro aktualizaci markeru na mape
                $(".show_original_position").click(function(){
                    if (typeof $(this).data('lat') !== 'undefined' && typeof $(this).data('lon') !== 'undefined') {
                        $this.find('input[name$="[latitude]"]').val($(this).data('lat'));
                        $this.find('input[name$="[longitude]"]').val($(this).data('lon'));

                        methods._displayMarkerAtCurrentAddress($this);

                        return false;
                    }
                });

                //inicializace google mapky
                methods._initGoogleMap($this);

                //na mape oznaci aktualne vybranou adresu podle GPS souradnic
                //pokud nejsou GPS souradnice definovany, tak neprovede nic
                methods._displayMarkerAtCurrentAddress($this);

            });
            
        },

        /**
         * Metoda ocekava objekt obsahujici komponenty adresy a ty vklada
         * do formulare do readonly poli.
         *
         */
        _applyAddress: function ($this, components) {

            //projdu komponenty a umistim do input, ktere odpovidaji nazvum
            //jednotlivych polozek - jen latitude a longitude
            for (attr in components) {
                if ($this.find('input[name$="['+attr+']"]').length != 0) {
                    $this.find('input[name$="['+attr+']"]').val(components[attr]);
                }
            }
        },

        /**
         * Z formulare zobrazujiciho adresu si vytahne souradnice a zobrazi marker
         * na dane pozici.
         */
        _displayMarkerAtCurrentAddress: function ($this) {
            var settings = $this.data('settings');

            //k inputum budu pristupovet vicekrat, tak je "naselectuju" jen jednou
            var $lat_input = $this.find('input[name$="[latitude]"]');
            var $lon_input = $this.find('input[name$="[longitude]"]');

            //prectu souradnice z formularovich poli
            var lat = $.trim($lat_input.val());
            var lon = $.trim($lon_input.val());

            //reference na objekt mapy
            var map = $this.data('map');

            //vytahnu referenci na existujici marker
            var marker = $this.data('marker');

            if (typeof marker === 'undefined') {
                marker = new GMarker(new GLatLng(0,0), { draggable : true /*! $this.data('settings').readonly */});
                //pro upresneni lokality povolim pretahovani
            }

            //pokud nejsou obe vyplnene, tak se nebude pozice na mapce nastavovat
            if (lat == '' || lon == '' || typeof map === 'undefined') {

                //zrusim aktualni marker a dale nebudu nic provadet
                if (typeof marker !== 'undefined') {
                    marker.hide();
                }

                return;
            }

            //nova pozice markeru
            var new_marker_pos = new GLatLng(lat, lon);

            //TODO: pridat kontrolu pomoci parseFloat

            marker.setLatLng(new_marker_pos);

            //ulozim referenci na marker do dat prvku
            $this.data('marker', marker);

            //do mapy vlozim marker
            map.addOverlay(marker);


            // Priblizeni mapy - tady jdou rozeznat mesta
            var zoom = settings.location_zoom;
            // Pokud je neprazdna adresa, pak priblizime vice
            var address = $this.find('input[name$="[address]"]').val().trim();
            if (address != '') zoom = settings.address_zoom;
            //mapu vycentruju na nove souradnice bodu
            //map.panTo(new_marker_pos);

            map.setCenter(new_marker_pos, zoom);

            //pred zmenou pozice si originalni souradnice ulozim a zobrazim
            //tlacitko, ktere slouzi pro jejich obnoveni
            $show_original_position_button = $this.find('.show_original_position');

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

                    $show_original_position_button.data('lat', $lat_input.val());
                    $show_original_position_button.data('lon', $lon_input.val());

                    $show_original_position_button.show();
                }

                //nove souradnice zapisu do inputu na formulari
                $lat_input.val(point.lat());
                $lon_input.val(point.lng());
                
            });

            //tlacitko pro zobrazeni puvodni souradnice pripravim pro dalsi pouziti
            $show_original_position_button.removeData('lat');
            $show_original_position_button.removeData('lon');
            $show_original_position_button.hide();
        },
        

        /**
         * Provadi inicializaci Google Mapy.
         * Defaultni pozic nastavuje na CR.
         */
        _initGoogleMap: function ($this) {

            //jen pokud jsme v kompatibilnim prohlizeci
            if (typeof GBrowserIsCompatible == 'function' && GBrowserIsCompatible()) {

                var map = new GMap2($this.find('.map .canvas').get(0));

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

                //defaultni souradnice 50.1, 15.5 zhruba odpovidaji stredu
                //republiky tak aby byla cela republika na mape videt pekne uprostred
                //map.setCenter(new GLatLng(50.1, 15.5), 6);

                // Zde potrebujeme mapu celeho sveta
                map.setCenter(new GLatLng(50.1, 0), 0);

                //referenci na objekt Google Mapy ulozim do dat
                $this.data('map', map);
            }

        }
      
    };

    $.fn.AppFormItemPropertyAddress = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemPropertyAddress');
            
        }
        
        return this;

    };

})( jQuery );

