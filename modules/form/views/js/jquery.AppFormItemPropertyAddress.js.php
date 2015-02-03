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
                readonly : true,
                gps_ok : false
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

                //vstupy podle kterych se vyhledavaji gps souradnice
                var $address_input = $this.find('input[name$="[address]"]');

                var $postalcode_input = $this.find('input[name$="[postal_code]"]');

                // inputy pro zobrazeni gps souradnic
                var $latitude_input = $this.find('input[name$="[latitude]"]');

                var $longitude_input = $this.find('input[name$="[longitude]"]');

                // inputy pro zobrazeni google gps souradnic
                var $glatitude_input = $this.find('input[name$="[google_latitude]"]');

                var $glongitude_input = $this.find('input[name$="[google_longitude]"]');

                // inputy pro zobrazeni google gps souradnic
                var $alatitude_input = $this.find('input[name$="[arn_latitude]"]');

                var $alongitude_input = $this.find('input[name$="[arn_longitude]"]');

                var $indexed_csc_input = $("input[name$='[property_location_indexed_csc]']", $this);

                var $use_arn_btn = $(".use_arn", $this);
                var $use_google_btn = $(".use_google", $this);


                $use_arn_btn.click(function(){
                    $latitude_input.val($alatitude_input.val());
                    $longitude_input.val($alongitude_input.val());
                    displayMarkerAtCurrentAddress();
                });
                $use_google_btn.click(function(){
                    $latitude_input.val($glatitude_input.val());
                    $longitude_input.val($glongitude_input.val());
                    displayMarkerAtCurrentAddress();
                });

                //pred zmenou pozice si originalni souradnice ulozim a zobrazim
                //tlacitko, ktere slouzi pro jejich obnoveni
                var $show_original_position_button = $this.find('.show_original_position');

                // pri stisknuti klavesy enter v inputu dojde k vyhledani
                $address_input.keypress(function(e){
                    if (e.which == 13) {
                        $address_input.trigger('change');
                        return false;
                    }
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
                        if ($this.find('input[name$="[google_'+attr+']"]').length != 0) {
                            $this.find('input[name$="[google_'+attr+']"]').val(components[attr]);
                        }
                        if ($this.find('input[name$="['+attr+']"]').length != 0 && $this.find('input[name$="['+attr+']"]').val() == '') {
                            $this.find('input[name$="['+attr+']"]').val(components[attr]);
                        }
                    }
                };


                /**
                 * Z formulare zobrazujiciho adresu si vytahne souradnice a zobrazi marker
                 * na dane pozici.
                 */
                var displayMarkerAtCurrentAddress = function () {

                    //prectu souradnice z formularovich poli
                    var lat = $.trim($latitude_input.val());
                    var lon = $.trim($longitude_input.val());

                    //prectu google souradnice z formularovich poli
                    var glat = $.trim($glatitude_input.val());
                    var glon = $.trim($glongitude_input.val());
                    //prectu arn souradnice z formularovich poli
                    var alat = $.trim($alatitude_input.val());
                    var alon = $.trim($alongitude_input.val());



                    var importanceOrder = function(marker, b) {
                        return 1000000; //  * marker.importance;
                    };

                    // Google GPS marker

                    if (glat != '' && glon != '') {
                        var icon = new GIcon(G_DEFAULT_ICON);
                        icon.image = '<?= DOMAIN_ADMIN.'/css/images/google_gps_icon.png' ?>';
                        var params = {
                            draggable : false
                            ,icon: icon
                        };
                        if ( ! gmarker) {
                            gmarker = new GMarker(new GLatLng(glat,glon), params);
                            map.addOverlay(gmarker);
                        } else {
                            gmarker.setLatLng(new GLatLng(glat, glon));
                        }
                    } else if (gmarker) {
                        gmarker.hide();
                    }



                    // ARN GPS marker
                    if (alat != '' && alon != '') {
                        var icon = new GIcon(G_DEFAULT_ICON);
                        icon.image = '<?= DOMAIN_ADMIN.'/css/images/arn_gps_icon.png' ?>';
                        var params = {
                            draggable : false
                            ,icon: icon
                        };
                        if ( ! amarker) {
                            amarker = new GMarker(new GLatLng(alat,alon), params);
                            map.addOverlay(amarker);
                        } else {
                            amarker.setLatLng(new GLatLng(alat,alon));
                        }
                    } else if (amarker) {
                        amarker.hide();
                    }



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



                    // Priblizime mapu tak aby byly videt vsechny markery
                    var bounds = new GLatLngBounds();
                    var zoom_out = 1;
                    if (marker) {
                        bounds.extend(marker.getPoint());
                    }
                    // Pokud jsou GPS hotelu jiz zkontrolovane, pak nemusime delat zoom-out na Gooogle a ARN markery
                    if ( ! settings.gps_ok) {
                        if (gmarker) {
                            bounds.extend(gmarker.getPoint());
                        }
                        if (amarker) {
                            //   bounds.extend(amarker.getPoint());
                        }
                    } else {
                        zoom_out = 3;
                    }
                    map.setCenter(bounds.getCenter(), map.getBoundsZoomLevel(bounds) - zoom_out);

                    //pridam listener - kdyz skonci dragovani tak si chci ulozit nove souradnice
                    //timto uzivateli dovolim explicitne definovat souradnice
                    if (marker) GEvent.addListener(marker, "dragend", function() {

                        var point = marker.getPoint();

                        //mapu vycentruju na nove souradnice bodu
                        map.panTo(point);

                        //pred zmenou pozice balonku ulozim puvodni souradnice, ktere pujdou
                        //pomoci tlacitka obnovit
                        if (typeof $show_original_position_button.data('lat') === 'undefined'
                            && typeof $show_original_position_button.data('lon') === 'undefined') {

                            $show_original_position_button.data('lat', $latitude_input.val());
                            $show_original_position_button.data('lon', $longitude_input.val());

                            $show_original_position_button.show();
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
                            applyAddress( response );

                            //zobrazi na mape marker pro oznaceni aktualni adresy
                            displayMarkerAtCurrentAddress();


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
                $(".load_gps", $this).on('click', search_gps);


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
                displayMarkerAtCurrentAddress();

            });
            
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

