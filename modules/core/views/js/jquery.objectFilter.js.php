//<script>
/**
 * Tento plugin zajistuje funkci filtru pro nacitani dat na poradaci.
 *
 * Na konci pluginu je kod, ktery provadi jeho inicializaci, takze staci do stranky
 * tento plugin pouze vlozit.
 *
 * Ocekava ze bude spusteno na prvku typu div, ktery bude obsahovat
 * prvek typu "form" s atributem 'action' kde musi byt URL, na kterou budou
 * odesilany pozadavky pro nacteni dat.
 * Uvnitr elementu nad kterym je plugin spusten vyhledava prvek(ky) s css tridou
 * 'submit_filter', ktery/e vyvolaji nacteni dat.
 * Prvek s css tridou 'reset_filter' provede vyresetovani filtru do jeho defaultniho
 * stavu.
 * Dale plugin ocekava ze ve strance je prvek, ktery vyhovuje tomuto selectoru:
 * "#data_table_container", ktery obsahuje data odpovidajici nastavenemu filtru.
 * V pripade spusteni noveho filtrovani plugin nad timto prvkem aktivuje progress
 * indicator a po obdrzeni korektni odpovedi provede replacnuti obsahu prvku.
 *
 *
 */
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'objectFilter';

    /**
     * Tento retezec se vlozi do location.hash pro indikaci prazdnych parametru.
     * Pouziva se to pri resetovani stavu filtru.
     */
    var emptyLocationHash = '_';

    /**
     * Defaultni hodnoty pro parametry a nastaveni pluginu
     */
    var default_settings = {
    };

    /**
     * Zde bude ulozena reference na vytvoreny dialog, ktery se bude pouzivat
     * pro ruzne akce - nebude se vytvaret vice instanci.
     */
    var $dialog = null;

    /**
     * Metody pro tento plugin.
     */
    var methods = {

        init: function( options ) {

            this.each(function(){

                var $_this = $(this);

                //vytvorim si kopii defaultnich parametru - ta se pouzije v pripade
                //ze uzivatel se vrati na stav url kdy je prazdny hash (defaultni stav
                //po nacteni stranky)
                var default_params = $.extend(true, {}, options['defaults']);

                var default_params = $.extend(default_params, methods._getCurrentFilterParams($_this));

                //vytvorim instanci objektu s nastavenim pro tuto instanci pluginu
                var settings = {
                    newUserExportFormUrl:  options['newUserExportFormUrl'] || default_settings['newUserExportFormUrl'],
                    newFilterStateFormUrl: options['newFilterStateFormUrl'] || default_settings['newFilterStateFormUrl'],
                    updateFilterStateUrl:  options['updateFilterStateUrl']  || default_settings['updateFilterStateUrl'],
                    removeFilterStateUrl:  options['removeFilterStateUrl']  || default_settings['removeFilterStateUrl'],
                    after_initDataContent: options['after_initDataContent'] || default_settings['after_initDataContent'],
                    userExportUrl:         options['userExportUrl']         || default_settings['userExportUrl']
                };

                //ulozim si aktualni nastaveni pluginu
                methods._setData( $_this , {
                    settings: settings
                });

                //pokud jsou v nastaveni definovany nejake defaultni parametry pro filtrovani
                //tak si je ulozim
                if (typeof options['defaults'] !== 'undefined') {
                    methods._setData( $_this, {
                        request_params: options['defaults']
                    });
                }

                //reference na objekt formulare uvnitr filtru
                var $form = $(this).find('form[action]');

                //objekt formulare, ktery je ocekavan
                settings.request_url = $form.attr('action');

                //jako prvni se inicializuji uzivatelske filtry a pokud je jeden
                //z nich aktivni (podle defautnich parametru filtrovani), tak
                //jej aktivuji a pozdeji tedy nesmi dojit ke standardnimu nacteni dat
                var data_sent = false;

                //pokud jsou definovany parametry filtru
                if (typeof options['filterStateParams'] !== 'undefined') {

                    var active_fs = null;
                    //pokud je v defaults i ID aktivniho filtru tak si ho vytahnu
                    //a nize pouziju k okamzite aktivaci daneho uzivatelskeho filtru
                    if (typeof options['defaults'] !== 'undefined'
                            && typeof options['defaults']['_fs'] !== 'undefined') {
                        active_fs = options['defaults']['_fs'];
                    }

                    //tady bude seznam filtru podle jejich filter_id (hodnota _fs)
                    var filter_list = new Object();

                    //projdu definice filtru a ulozim do dat ke kazde polozce v HTML
                    for (i in options['filterStateParams']) {

                        //provedu inicializaci uzivatelskych filtru
                        //kliknuti na jeden z uzivatelskych filtru
                        $("#user_filter_panel .filterstate_item:eq("+i+")").each(function(){

                            //zkratka
                            var $filterstate_item = $(this);

                            //ID tohoto filtru
                            var filter_id = options['filterStateParams'][i]['_fs'];

                            //data filtru ukladam primo na DOM elementu
                            $filterstate_item.data('filter_id', filter_id)
                                             .data('filter_params', options['filterStateParams'][i]);

                            //nabinduje akce aktivace filtru a jeho odstraneni
                            methods._bindFilterItemAction( $_this , $filterstate_item);

                            //ulozim referenci do seznamu filtru
                            filter_list[filter_id] = $filterstate_item;

                            //pokud se jedna o defaultni filtr, tak nebudu volat updateUserFilter, ale
                            //rovnou vyvolam nacteni dat
                            if (active_fs == filter_id) {

                                //aktivace filtru (=nacteni dat)
                                methods._activateFilterState( $_this , $filterstate_item );

                                //nize uz nedojde ke standardnimu nacteni dat
                                data_sent = true;

                            } else {
                                //statistiku filtru aktualizuju az 1s pozdeji - chci nechat
                                //dost castu pro nacteni dat do stranky
                                window.setTimeout(function(){
                                    //kazdou polozku necham ajaxem aktualizovat
                                    methods._updateUserFilter( $_this, $filterstate_item );
                                }, 1000);
                            }
                        });
                    }

                    //seznam referenci na filtry si ulozim
                    methods._setData($_this, {filter_list: filter_list});
                }
                
                //potrebuji odchytit kliknuti na tlacitko 'vyhledat'
                $(".submit_filter", $_this).click(function(e) {
                    // Ignorujeme kliknuti na selecty - o ty se postara change handler
                    if ($(this).is('select')) return;
                    //metoda zorbazi progress indicator a odesle pozadavek na nactenidat
                    methods._setState( $_this, methods._getCurrentFilterParams($_this) );

                    return false;
                });
                $("select.submit_filter", $_this).change(function(e){
                    //metoda zorbazi progress indicator a odesle pozadavek na nactenidat
                    methods._setState( $_this, methods._getCurrentFilterParams($_this) );
                    return false;
                });

                //dale pridam obsluhu tlacitka pro vyresetovani filtru
                //@TODO: zkontrolovat jestli bude fungovat spravne na polich
                //s naseptavacem protoze tam se hodnota uklada v data() a je
                //potreba aby po resetovani se vyvolala change udalost, coz by
                //zajistilo ze se vymazou i data().
                $(".reset_filter", $_this).click(function() {
                    //volam primo na JavaScript DOM element

                    //formular vycistim
                    $("#main_data_filter").objectFilterForm('clear');

                    //vytahnu hodnoty prazdneho formulare
                    var params = $("#main_data_filter").objectFilterForm('getValues');

                    //reset cisla stranky
                    params['_pi'] = 0;

                    //podle parametru 'vycisteneho' formulare filtruju
                    methods._setState( $_this , params );

                    return false;
                });

                //pokud je ve strance tlacitko pro ulozeni stavu filtru, tak
                //se vytori dialogove okno, ktere obsahuje formular pro vlozeni
                //nazvu filtru, ktery ma byt ulozen
                $save_filter_state_button = $(".save_filter", $_this);

                //vytvorim instanci dialogu, ktera se bude pouzivat pro zobrazeni
                //formularu - pro editaci filtru, editaci polozek apod.
                $dialog = $( document.createElement('div') ).hide()
                                                            .appendTo($_this);

                //inicializace dialogoveho okna
                $dialog._dialog({
                    autoOpen:false,
                    closeOnEscape:true,
                    width:400,
                    height:300,
                    draggable:true,
                    resizable:false,
                    position:'center'
                });

                if ($save_filter_state_button.length != 0) {
                    
                    //tlacitko pro ulozeni stavu filtru
                    $(".save_filter", $_this).click(function(){

                        //dialog musi pri prvnim nacteni formulare poslat i
                        //parametry filtru - ty si pak musi formular udrzet
                        var filter_params = methods._getCurrentFilterParams( $_this );

                        var defaults = {
                            '_filter_params': filter_params
                        };

                        //definuje data, ktera prepisou aktualni hodnoty formulare
                        //i pokud uz bude nacten existujici zaznam
                        var form_data = {
                            'overwrite': defaults
                        };

                        //na atributu 'active_fs' je reference na DOM objekt
                        //aktivniho uzivatelskeho filtru
                        var $active_filter_item = methods._getData( $_this , 'active_fs');

                        if (typeof $active_filter_item !== 'undefined' ) {
                            //je aktivni uzivatelsky filtr - z parametru vyhledavani si vytahnu
                            //jeho ID a misto ulozeni dojde k jeho editaci
                            form_data['_id'] = $active_filter_item.data('filter_id');
                        }
                        
                        //serializace dat
                        form_data = $.param(form_data);

                        //otevru dialogove okno s formularem pro vlozeni nazvu filtru
                        $dialog._dialog('loadForm', options.newFilterStateFormUrl, form_data, function(response){
                            if (response['action_status'] == '<?= AppForm::ACTION_RESULT_SUCCESS;?>') {
                                //zavru dialogove okno
                                $dialog._dialog('close');
                                //potrebuju aktualizovat stav dane polozky ve filtrech

                                //pokud byl vytvoren novy filtr tak misto DOM elementu
                                //posilam jeho ID a v metode _updateUserFilter dojde
                                //k jeho nacteni ze serveru
                                if (typeof $active_filter_item === 'undefined') {
                                    $active_filter_item = response['id'];
                                }

                                methods._updateUserFilter( $_this , $active_filter_item , filter_params);
                            }
                        });

                        return false;
                    });
                }

                //panel s informaci o aktivnim uzivatelskem filtru
                //tlacitko pro zruseni filtru
                $(".filterstate_header .reset_filterstate", $_this).click(function(){

                    methods._deactivateFilterState( $_this );

                    return false;
                });

                //tlacitko pro zobrazeni nastaveni filtrovaciho formulare
                $(".filterstate_header .edit_filter", $_this).click(function(){

                    methods._startFilterStateEdit( $_this );

                    return false;
                });

                //tlacitko pro zobrazeni nastaveni filtrovaciho formulare
                $(".cancel_edit_filter", $_this).click(function(){

                    methods._endFilterStateEdit( $_this );

                    return false;
                });

                //ovladaci prvek pro export do CSV
                if ($(".export_control", $_this).length != 0) {
                    methods._bindExportControl( $_this , $(".export_control", $_this) );
                }

                //prvni div uvnitr elementu .filter_actions obsahuje tlacitka pro 
                //standardni funkce filtru (vyhledat, resetovat)
                //druhy div uvnitr elementu .filter_actions obsahuje tlacitka
                //pro editaci ulozeneho filtru (ulozit zmeny, zrusit editaci)
                //ja tyto divy s tlacitky budu do stranky vkladat a vyjimat podle
                //potreby, tak aby byla zachovana funkcnost stistku klavesy Enter
                //na jakemkoli inputu na formulari
                var $edit_filter_buttons = $(".filter_actions div:nth-child(2)").show() //div je defaultne skrytej aby neproblikl na strance nez je detachovan
                                                                                .detach();
                //tento vyjmuty blok ulozim do dat teto instance
                methods._setData( $_this, 'edit_filter_buttons', $edit_filter_buttons);

                var $use_filter_buttons = $(".filter_actions div:first");
                //tento vyjmuty blok ulozim do dat teto instance
                methods._setData( $_this, 'use_filter_buttons', $use_filter_buttons);

                //nabinduji udalost hash change aby GUI reagovalo na stisk tlacitka zpet
                $(window).bind( 'hashchange', function(e) {
                    //pokud neni nastaven zadny hash,
                    if (window.location.hash.length == 0 || window.location.hash == emptyLocationHash) {
                        //defaultni parametry posilam do metody v kopii jinak by se mi
                        //pres reference prepsaly kdyz by uzivatel neco zmenil (napr. velikost stranky)
                        methods._restoreState($_this, $.extend(true, {}, default_params));
                    } else {
                        methods._restoreState($_this);
                    }
                 });

                 $(window).trigger('hashchange');

            });
        },

        _bindItemActionControl: function( $_this , $data_container ) {

            //checkbox pro oznaceni vsech polozek na strance
            $data_container.find('.select_all').click(function(){
                if ($(this).is(":checked")) {
                    $data_container.find('.item:checkbox').attr('checked', 'checked');
                } else {
                    $data_container.find('.item:checkbox').removeAttr('checked');
                }
            });

            $data_container.find('.action_button').click(function(e){

                var selected = methods._getSelectedItems();

                var $link = $(this);

                //pokud nejsou vybrane zadne nabidky, tak uzivatele upozornim
                //na to ze musi nejake nabidky vybrat
                if (selected == '') {
                    // Pokud akci lze volat pouze nad zvolenymi zaznamy, oznamime uzivateli ze nejake musi vybrat
                    if ($link.attr('need_selection')) {
                        //zobrazim zpravu - bude automaticky skryta za 60s
                        methods._showMessage($_this, "<?= __('filter.no_items_selected');?>", 60000);
                        return false;
                    }
                }

                //nazev akce, kterou budu vyvolavat
                var action = $(this).attr('action');

                //pokud je definovan atribut confirm, tak musi uzivatel
                //akci potvrdit
                var confirm_message = $(this).attr('confirm');

                if (typeof confirm_message !== 'undefined' && ! confirm(confirm_message)) {
                    return false;
                }

                methods._requestAction($_this, action, selected);

                //defaultni akce by mohla udelat cokoli (treba odeslat formular)
                // - tomu chci zamezit
                e.preventDefault();
                return false;
            });

        },

        _showMessage: function($_this, message, autohide) {

            //pokud je aktivni nejaky timer pro skryti panelu se zpravou
            //tak jej deaktivuji
            clearTimeout(this.timer);

            //obsah zpravy vlozim do prislusneho panelu
            $(document).find('#table_result_placeholder').show().html(message);

            //pokud je definovan autohide, tak dojde k automaticke skryti panelu
            //po definovane dobe
            if (typeof autohide !== 'undefined') {
                $ref = $(document).find('#table_result_placeholder');

                this.timer = window.setTimeout(function(){
                    $ref.hide();
                }, autohide);
            }
        },

        _hideMessage: function($_this) {
            $(document).find('#table_result_placeholder').hide();
        },

        _requestAction: function($_this, action, selected, undo) {

            //vytahnu si aktualni parametry filtrovani
            var request_params = methods._getData( $_this , 'request_params');

            //pridam parametry, ktere zaridi provedeni pozadovane akce nad
            //pozadovanymi zaznamy
            request_params['a'] = action;
            request_params['i'] = selected;

            request_params['d'] = typeof undo === 'undefined'
                                    ? 1
                                    : 0;

            methods._sendQuery( $_this, request_params);
        },

        _getSelectedItems: function(){

            var list = '';

            $("#data_table_container input.item:checked").each(function(){
                list += $(this).attr('item_id') + ',';
            });

            return list;
        },

        _bindExportControl: function ( $_this , $item ) {

            //z nastaveni pluginu si vytahnu URL pro nacteni editacniho formulare
            var settings = methods._getData( $_this, 'settings' );

            var form_url = settings['newUserExportFormUrl'];

            $item.change(function(){

                //aktualne vybrana hodnota
                var value = $(this).val();

                switch (value) {
                    //prvni polozka s textem 'jako...' - nema zadnou funkci
                    case 'dummy':
                    break;

                    //polozka 'vlastni sestava' - uzivatel ji vybere pokud
                    //chce vytvorit novou sestavu
                    case 'new':

                        //otevru dalog s formularem pro nastaveni nove sestavy pro export
                        $dialog._dialog('loadForm', form_url , {}, function(response){

                            if (response['action_status'] == '<?= AppForm::ACTION_RESULT_SUCCESS;?>') {

                                //zavru dialogove okno
                                $dialog._dialog('close');

                                //vyvolam stazeni souboru, ktery
                                if (typeof response['id'] !== 'undefined') {
                                    methods._downloadExport($_this, response['id']);
                                }
                            }
                        });


                    break;

                    //zbyvajici polozky - uzivatel vybral jednu z jiz 
                    //existujicich sestav
                    default:
                    
                        //vezmu id a vyvolam download
                        methods._downloadExport( $_this, value );
                }


                //vzdy chci vybrat zpatky prvni polozku
                $item.find('option:first').attr('selected', 'selected');
            });

        },


        _downloadExport: function( $_this , user_exportid ) {
        
            //z nastaveni pluginu si vytahnu URL pro generovani exportu dat
            var settings = methods._getData( $_this, 'settings' );
            var export_url = settings['userExportUrl'];

            //vytahnu si parametry filtru, ktere byly pouzity pri poslednim 
            //uspesnem nacteni dat
            var params = methods._getData( $_this, 'request_params');

            //pridam ID user_export zaznamu
            params['user_exportid'] = user_exportid;

            //download pres ajax
            $.download(export_url, params, 'post', function(){
                //tento callback je vyvolan v pripade ze dojde k chybe pri stahovani souboru
                
            });
        },

        _bindFilterItemAction: function ( $_this , $filterstate_item ) {

            //nazev filtru
            var name = $filterstate_item.find('.name').html();

            //nabinduju akci aktivaci filtru
            $filterstate_item.find(".activate_filter").click(function(){
                //provede aktivaci uzivatelskeho filtru
                methods._activateFilterState( $_this , $filterstate_item );
                //prevents default anchor action
                return false;
            });

            //odstraneni filtru
            $filterstate_item.find('.remove_filter').click(function(){

                //oznaceni prvku k odstraneni
                $filterstate_item.addClass('to_be_removed');

                //z nastaveni si vezmu URL pro odstraneni filtru a ID aktivniho filtru (viz. nize)
                var settings = methods._getData( $_this , 'settings');
                var $activefilter_item = methods._getData( $_this , 'active_fs');

                //ID filtru
                var filter_id = $filterstate_item.data('filter_id');

                if ($.confirm('<?= __("filter.confirm_filterstate_remove_1");?>' + name + '<?= __("filter.confirm_filterstate_remove_2");?>')) {
                    $.get(settings['removeFilterStateUrl'], {id:filter_id});
                    //odstraneni ze stranky
                    if ($activefilter_item == $filterstate_item) {
                        methods._deactivateFilterState( $_this );
                    }
                    $filterstate_item.remove();
                }

                //prvek nebude odstranen - uzivatel odstraneni nepotvrdil
                $filterstate_item.removeClass('to_be_removed');

                return false;
            });
        },


        _startFilterStateEdit: function ( $_this ) {

            //vyprazdnim blok, ktery obsahuje ovladaci tlacitka pro formular
            $_this.find('.filter_actions div').detach();

            //vlozim tam tlacitka pro ovladani editace filtru
            var $edit_filter_buttons = methods._getData( $_this, 'edit_filter_buttons');
            $_this.find('.filter_actions').prepend($edit_filter_buttons);

            //zobrazim formular pro vlozeni filtrovacich parametru
            $_this.find("form.search").show();

            //focus hodim na prvni viditelny formularovy prvek
            $_this.find("form.search").find('input:visible,textarea:visible,select:visible').first().focus();

            //schovam tlacitko pro vyvolani editace filtru 
            $(".filterstate_header .edit_filter", $_this).hide();

        },

        _endFilterStateEdit: function ( $_this ) {

            //vyprazdnim blok, ktery obsahuje ovladaci tlacitka pro formular
            $_this.find('.filter_actions div').detach();

            //vlozim tam tlacitka pro ovladani filtru
            var $use_filter_buttons = methods._getData( $_this, 'use_filter_buttons');
            $_this.find('.filter_actions').prepend($use_filter_buttons);

            //zobrazim formular pro vlozeni filtrovacich parametru
            $_this.find("form.search").hide();
            
            //zobrazim tlacitko pro vyvolani editace filtru
            $(".filterstate_header .edit_filter", $_this).show();
        
        },

        _deactivateFilterState: function ( $_this ) {

            if ( ! $_this.find(".filterstate_header").hasClass('active')) {
                return;
            }

            //zrusim ID aktivniho uzivatelskeho filtru
            var data = methods._getData( $_this );
            delete data['active_fs'];
            methods._setData( $_this , data );

            //vyprazdnim filtrovaci formular
            $("#main_data_filter").objectFilterForm('clear');

            //odstranim tridu active, ktera zajistuje editaci uzivatelskeho filtru
            $_this.find(".filterstate_header").removeClass("active");

            //metoda zorbazi progress indicator a odesle pozadavek na nactenidat
            methods._setState( $_this, null );

            //vyprazdnim blok, ktery obsahuje ovladaci tlacitka pro formular
            $_this.find('.filter_actions div').detach();

            //vlozim tam tlacitka pro ovladani filtru
            var $use_filter_buttons = methods._getData( $_this, 'use_filter_buttons');
            $_this.find('.filter_actions').prepend($use_filter_buttons);

            //panel schovam
            $_this.find(".filterstate_header").hide();

            //zobrazim vypis filtru uzivatele
            $_this.find('.filterSaved').show();

            //zobrazim filtrovaci formular
            $_this.find("form.search").show();
        },

        _activateFilterStateGUI: function ( $_this , $filterstate_item) {

            //muze nastat situace kdy je aktivni editace filtru a je zavolana
            //tato metoda - napriklad po ulozeni zmeny ve filtru. Pak potrebuji
            //zrusit predchozi editaci
            methods._endFilterStateEdit( $_this );

            //ulozim ID aktivniho uzivatelskeho filtru
            methods._setData( $_this , 'active_fs' , $filterstate_item);

            //ze seznamu filtru si vytahnu jeho nazev
            var filterstate_name = $filterstate_item.find('.name').html();

            //vlozim do panelu, ktery informuje uzivatele o tom ze ma aktivni filter (zobrazim ho za nize)
            $_this.find(".filterstate_header .name").html(filterstate_name);

            //trida aktive se kontroluje pri ukladani stavu filtru - zajisti
            //ze dojde k editaci daneho filtru
            $_this.find(".filterstate_header").addClass("active");

            //skryju formular pro vlozeni filtrovacich parametru
            $_this.find("form.search").hide();

            //schovam vypis filtru uzivatele
            $_this.find('.filterSaved').hide();

            //zobrazim info panel o uzivatelskem filtru
            $_this.find(".filterstate_header").show();

            //z dat prvku vytahnu jeho parametry
            $("#main_data_filter").objectFilterForm('setValues', $filterstate_item.data('filter_params'));

        },

        _activateFilterState: function ( $_this , $filterstate_item ) {

            //ziskam ID filtru z jeho dat
            var filter_id = $filterstate_item.data('filter_id');
            
            //z dat prvku vytahnu jeho parametry
            var filterstate_params = $filterstate_item.data('filter_params');

            //do parametru musim pridat ID filtru - to zajisti ze se na serveru
            //prepocitaji statistiky filtru
            filterstate_params['_fs'] = filter_id;

            //velikot stranky chci explicitne nastavit na aktualne zvolenou
            var current_filter_params = methods._getCurrentFilterParams( $_this );

            filterstate_params['_ps'] = current_filter_params['_ps'];

            //odeslu pozadavek na cteni dat s parametry daneho filtru
            methods._setState( $_this , filterstate_params );
        },

        /**
         * Tato metoda slouzi k aktualizaci oblibeneho filtru uzivatele.
         *
         * Je volana ve 2 pripadech:
         *      A) Automaticky pro nacteni tranky kdy se nacitaji statistiky
         *          kazdeho filtru. 3. argument je prazdny, dochazi k aktualizaci
         *          pouze statistiky.
         *      B) Pri vlozeno nebo editace oblibeneho filtru uzivatele. Pak
         *          je pri vlozeni nove nabidky 2. argument roven ID noveho filtru
         *          a dochazi k nacteni cele html polozky. NEBO dochazi k editaci
         *          existujici nabidky a pak je 2. argument reference na dany DOM
         *          objekt - dochazi k nacteni cele polozky
         *
         */
        _updateUserFilter: function ( $_this , $filterstate_item, filter_params) {

            //url pro aktualizaci stavu uzivatelskeho filtru
            var settings = methods._getData( $_this , 'settings' );
            var url = settings['updateFilterStateUrl'];

            //$filterstate_item je bud ID noveho filtru nebo referenci
            //na existujici  ($)
            if (typeof $filterstate_item !== 'object') {
                var filter_id = $filterstate_item;
            } else {
                var filter_id = $filterstate_item.data('filter_id');
            }

            //pokud je definovan parametre pro predani parametru filtru, tak pocitame
            //s tim ze mohlo dojit k jejich zmene a je tedy nutne nacist celou polozku
            //filtru znovu (mohl se treba zmenit nazev)
            if (typeof filter_params !== 'undefined') {
            
                $.get(url, {id:filter_id, c:1}, function(response){

                    //vlozeni noveho filtru do stranky
                    if (typeof $filterstate_item !== 'object') {

                        //vytvoreni jquery objektu
                        $filterstate_item = $(response);

                        //ulozim ID filtru
                        $filterstate_item.data('filter_id', filter_id);
                        
                        //pridam do seznamu filtru na zacatek
                        $li_item = $( document.createElement('li') );
                        $li_item.prepend($filterstate_item);
                        $("#user_filter_panel ul").prepend($li_item);
                        
                        //pridam do seznamu filtru v datech
                        var filter_list = methods._getData($_this, 'filter_list');

                        //pridam do seznamu filtru
                        filter_list[filter_id] = $filterstate_item;

                        //aktualizovany seznam ulozim do dat
                        methods._setData($_this, {filter_list: filter_list});

                    } else {
                        //vlozim novy obsah polozky, nemelo by obsahovat statistiku
                        //protoze ta dojde aktualni pri nacteni dat, ktere je nize
                        //vyvolano
                        $filterstate_item.empty().html(response);
                    }

                    //aktualizace parametru
                    $filterstate_item.data('filter_params', filter_params);

                    //nabindovani akce aktivace filtru
                    methods._bindFilterItemAction( $_this , $filterstate_item );
                        
                    //aktivace filtru
                    methods._activateFilterState( $_this , $filterstate_item );

                    //dale provedu animaci nove nebo zeditovane polozky
                    methods._animateFilterItem( $_this, $filterstate_item );
                });
            //nebo jen statistiku filtru ?
            } else {
                $filterstate_item.find(".stats").load(url, {id:filter_id});
            }
        },

        //@TODO: dodelat rozumnou animaci
        _animateFilterItem: function ( $_this , $filterstate_item ) {

            $filterstate_item.animate({backgroundColor:'#4E1402'}, 300, function(){
                $(this).stop().animate({backgroundColor:'#943D20'}, 100);
            });
            
        },

        _getCurrentFilterParams: function ( $_this ) {

            //reference na objekt formulare uvnitr filtru
            //var $form = $($_this).find('form[action]');

            //pripravim si parametry pro pozadavek
            var filter_parameters = $("#main_data_filter").objectFilterForm('getValues');

            var f=function(){
            $form.find('input,textarea,select').each(function(){
                if (typeof $(this).attr('name') !== 'undefined') {
                    //vyjimka pro radio a checkbox
                    if ($(this).is(':checkbox')) {
                        if ( ! $(this).is(':checked')) {
                            return;
                        }
                        filter_parameters[$(this).attr('name')] = $(this).val();
                    } else if ($(this).is(':radio')) {
                        if ( ! $(this).is(':checked')) {
                            return;
                        }
                        filter_parameters[$(this).attr('name')] = $(this).val();
                    } else {
                        filter_parameters[$(this).attr('name')] = $(this).val();
                    }
                }
            });}

            //k parametrum se jeste pokusim pridat velikost stranky, kterou si
            //uzivatel mohl jiz zvolit pri minuleh pozadavku na cteni dat
            var request_params = methods._getData( $_this, 'request_params' );
            if (typeof request_params !== 'undefined' && typeof request_params['_ps'] !== 'undefined') {
                filter_parameters['_ps'] = request_params['_ps'];
            }

            return filter_parameters;
        },

        _sendQuery: function( $_this, params, $filterstate_item) {

            //nactu si URL na kterou budu posilat pozadavek
            var settings = methods._getData( $_this, 'settings' );
            var request_url = settings.request_url;

            //pokud existuje predchozi pozadavek, tak ho zrusim a potom
            //vytvorim novy
            var previous_request = methods._getData( $_this, 'request' );
            if ( typeof previous_request !== 'undefined' ) {
                previous_request.abort();
                delete previous_request;
            }

            //udelam si referenci na block na kterej se pousti progerss indicator
            var $data_container = $("#data_table_container");

            //zobrazim progress indicator a odesilam pozadavek
            $data_container.block({
                message: "<?= __('general.loading_table_data');?>"
            });

            //udelam si ajax object pro poslani post pozadavku
            var jqXHR = $._ajax(request_url, {
                                        type: 'POST',
                                        data: params,
                                        success: function( response_data ) {

                                            //pri uspesnem nacteni dat dojde k vymazani
                                            //obsahu ".result_placeholder" - ktery obsahuje
                                            //vysledek posledni provedene akce, nebo je uz prazdny
                                            methods._hideMessage($_this);

                                            //v odpovedi od serveru muze byt novy hlavni nadpis filtru
                                            if (typeof response_data['headline'] !== 'undefined') {
                                                $_this.find('h1').html(response_data['headline']);
                                            }

                                            //na tomto klici ocekavam vyfiltrovana data
                                            if ( typeof response_data['content'] !== 'undefined' ) {
                                                //do data containeru vlozim data - navic si udelam referenci
                                                $data_container.html(response_data['content']);
                                                //ulozim si zvlast celkovy pocet nalezenych dat (vyuziva se pri ulozeni stavu filtru)
                                                methods._setData( $_this, 'c', response_data['c']);
                                                //zavolam metodu, ktera zajisti inicializaci dat - napriklad pager
                                                methods._initDataContent( $_this, $data_container );
                                                //po inicializaci dat odstranim progress indicator
                                                $data_container.unblock();
                                            }

                                            //na klici 'action_result' ocekavam HTML kod, ktery reprezentuje
                                            //vysledek provedene akce
                                            if (typeof response_data['action_result'] !== 'undefined') {

                                                //ze zpravy si vytvrim span prvek - odpoed muze obsahovat tlacitko
                                                //"undo"
                                                $message = $( document.createElement('span') ).html(response_data['action_result']);

                                                //do vysledku akce muze patrit tlacitko 'zpet', ktere akci vrati
                                                $message.find('.undo').click(function(){

                                                    //na klici 'action_name' ocekavam nazev provedene akce
                                                    //a na klici 'action_selected' ocekavam vybet vybranych
                                                    //prvku - tyto hodnoty sou v odpovedi jen kvuli tomu aby
                                                    //bylo mozne vyvolat 'undo' akci
                                                    var action   = response_data['action_name'];
                                                    var selected = response_data['action_selected']
                                                    
                                                    methods._requestAction( $_this , action, selected, 0);

                                                    methods._hideMessage($_this);

                                                    return false;
                                                });
                                                
                                                methods._showMessage($_this, $message);
                                            }
                                            
                                            //na klici 'fs_stat' muze byt aktualizovana statistika
                                            //pro fitlerstate polozku definovanou klicem '_fs' ve
                                            //filtrovacich parametrech
                                            if (typeof response_data['_fs'] !== 'undefined'
                                                && typeof $filterstate_item !== 'undefined') {

                                                $filterstate_item.find(".stats").html(response_data['fs_stat']);
                                            }

                                            //parametry filtrovani si ulozim
                                            //je to kvuli tomu ze pri strankovani budu pouzivat ulozene paramtry
                                            //filtrovnai - protoze uzivatel by mohl zmenit obsah inputu pro fulltext
                                            //a pak pouzit jenom strankovani nebo razeni

                                            //tyto parametry nebudu ukladat, jinak by se
                                            //mohli dostat do location hash
                                            delete params['a'];
                                            delete params['i'];
                                            delete params['d'];

                                            methods._setData( $_this, {
                                                request_params: params
                                            });

                                            $_this.trigger('tableDataReplaced');

                                        },
                                        error: function( jqXHR, textStatus, errorThrown ) {

                                            //funkce isValidXHRError slouzi predevsim k detekci
                                            //chyby AJAXu zpusobene opustenim stranky
                                            if ($.isValidXHRError(jqXHR)) {
                                                //uzivateli bude zobrazena obecna chybova hlaska
                                                $.userUnexpectedErrorDialogMessage();
                                            }
                                            //@TODO: pridat zobrazeni chybove hlasky
                                            $data_container.unblock();
                                            //smazu nepotrebny jqXHR objekt
                                            methods._setData( $_this, {request: undefined});
                                        },
                                        dataType: 'json'
            });

            //objekt reprezentujici pozadavek si ulozim - abych ho abortovat
            //v pripade ze uzivatel vyvola jinou akci pred nactenim novych dat
            methods._setData( $_this, {
                request: jqXHR
            });

        },

        /**
         * Metoda provadi inicializaci panelu s nactenymi daty.
         * Jeda se o aktivovani prvku pro ovladani strankovani, razeni, velikosti
         * stranky apod.
         */
        _initDataContent: function($_this, $data_container){

            //tlacitka pro prechod na ostatni stranky s vysledky
            $data_container.find('.pager_button').click(function(){
                //pozadovany page offeset je ulozen v atributu 'of'
                var page_index = $(this).attr('pi');
                //pokud na odkazu neni definovany atribut 'of' - cilovy
                //page offset na ktery tlacitko vede, tak se nebude nic
                //provade
                if (typeof page_index === 'undefined') {
                    return false;
                }
                //do ulozenych parametru vyhledavani ulozim novy offset
                //a odeslu novy pozadavek
                methods._updateState( $_this, {_pi: page_index} );
                //prevents default anchor action
                return false;
            });

            //prechod na specifcky radek vysledku
            $data_container.find('.page_index_input').keypress(function(e){
                if (e.which == 13) {
                    //pozadovany page offset je hodnotou tohoto inputu
                    var page_index = $(this).val();
                    //do ulozenych parametru vyhledavani ulozim novy offset
                    //a odeslu novy pozadavek
                    methods._updateState( $_this, {_pi: page_index} );
                }
            });

            //vyber velikosti stranky
            $data_container.find('.page_size_select').change(function() {
                //pozadovana page size hodnota prvku
                var page_size = $(this).val();
                //do ulozenych parametru vyhledavani ulozim novy page_size
                //a odeslu novy pozadavek
                methods._updateState( $_this, {_ps: page_size} );
            });
            //Tlacitka pro razeni podle jednotlivych atributu
            $data_container.find("a[name]").click(function(){
                var ob  = $(this).attr('name');
                var obd = $(this).attr('dir');
                //pokud neni smer razeni definovan, tak zapisu prazdny retezec
                //a na serveru by se mel vybrat defaultni, ale musi byt definovan
                //nazev atributu
                if (typeof ob === 'undefined' || ob == '') {
                    return false;
                }
                //pokud je smer nedefinovany, tak chci aby se na server
                //poslala prazdna hodnota
                if (typeof obd === 'undefined') {
                    return false;
                }
                //mezi parametry vyhledavani pridam atribut a smer razeni a
                //dale resetuji index stranky - zobrazi se prvni stranka
                methods._updateState( $_this, {_ob: ob, _obd: obd, _pi:0} );
                return false;
            });

            //ve vypsanych datech muzou byt tlacitka pro vyvolani editace v
            //ajaxem nactenem formulari - ty standardne inicializuji
            $data_container.find(".edit_ajax[href]").click(function(){

                //url pro nacteni editacniho formulare
                var edit_url = $(this).attr('href');

                var $clicked_item = $(this);
                var options = {};
                var width = $clicked_item.attr('data-dialog-width');
                var height = $clicked_item.attr('data-dialog-height');
                if (typeof width != 'undefined' && width) {
                    $dialog._dialog('option', 'width', width);
                }
                if (typeof height != 'undefined' && height) {
                    $dialog._dialog('option', 'height', height);
                }
                // @todo - tady je problem s predanim options
                $dialog._dialog('loadForm',edit_url, {}, function(response) {
                    if (response['action_status'] == '<?= AppForm::ACTION_RESULT_SUCCESS;?>') {
                        //zavru dialogove okno
                        $dialog._dialog('close');
                        //refresh dat
                        methods._updateState( $_this );
                    }
                });

                return false;
            });

            //tlacitka pro vyvolani akce nad zaznamem
            $data_container.find('.action').click(function(){
            
                //nazev akce, kterou tento prvek vyvolava
                var action_name = $(this).attr('action');

                var confirm_message = $(this).attr('confirm');

                if (typeof confirm_message === 'string') {
                    var $item_parent = $(this).parents('.item:first').addClass('to_be_actioned to_be_actioned-'+action_name);
                    if ($.confirm(confirm_message)) {
                        methods._requestAction($_this, action_name, $(this).attr('item_id'));
                        return false;
                    }
                    $item_parent.removeClass('to_be_actioned to_be_actioned-'+action_name);
                } else {
                    methods._requestAction($_this, action_name, $(this).attr('item_id'));
                }

                return false;
            });

            $data_container.find('.btn-table-export[href]').click(function(){
                var $btn = $(this).addClass('btn-loading');
                var current_filter_params = $.bbq.getState() || {};
                $.ajax({
                    url: $(this).attr('href'),
                    type: 'POST',
                    data: current_filter_params,
                    dataType:'json',
                    success: function(data){
                        $btn.removeClass('btn-loading');
                        if (typeof data === 'object' && data != null  && typeof data['f'] !== 'undefined') {
                            window.location.href = data['f'];
                        } else {
                            alert(typeof data !== 'object' || data == null || typeof data['e'] === 'undefined'
                                    ? "<?= __('object.data_export.error');?>"
                                    : data['e']);
                        }
                    },
                    error: function(){
                        alert("<?= __('object.data_export.error');?>");
                    }
                });
                return false;
            });

            //inicializuje tlacitka pro vyvolani hromadnych akci nad zaznamy
            methods._bindItemActionControl( $_this, $data_container );

            //pokud je v nastaveni definovany callback - after_initDataContent
            //tak ho vyvolam
            var settings = methods._getData( $_this, 'settings' );

            if (typeof settings['after_initDataContent'] === 'function') {
                settings['after_initDataContent']( $_this , $data_container);
            }

        },

        _updateState: function( $this, params ) {

            //vytahnu si aktualni parametry
            var current_params = methods._getData( $this, 'request_params' );

            if ( typeof current_params === 'undefined' ) {
                return false;
            }

            //aktualizuju
            $.extend( current_params, params );

            //pustim novy request
            return methods._setState( $this, current_params );

        },

        /**
         * Uklada aktualni stav filtru do window.location.hash.
         *
         * Z aktualniho stavu filtru uklada kompletni obsah polozky 'request_params'
         * ktera je ulozena v datech teto instance.
         *
         */
        _setState: function ( $_this , explicit_params ) {

            //pokud nejsou explicitne definovane parametry vyhledavani
            //tak si je tady posbiram - explicitni definice je pouze
            //pro specialni pripady

            //pripravim parametry pro filtrovani
            var request_params = methods._getData( $_this , 'request_params');

            if (explicit_params == null) {
                request_params = '#' + emptyLocationHash;
            } else {
                //standardne posbirane parametry prepisu temi co jsou explicitne definovane
                $.extend(request_params, explicit_params);
            }

            //navratova hodnota typu bool rika zda skutecne doslo k modifikaci
            //aktualniho window.location.hash
            var hash_modified = $.bbq.pushState( request_params );

            if ( ! hash_modified) {
                $(window).trigger('hashchange');
            }
        },

        _restoreState: function ( $_this , parameters ) {

            if (typeof parameters === 'undefined') {
                parameters = $.bbq.getState() || {};
            }

            //pokud je stav totozny jako ten aktualni tak se nebude nic provadat

            //projdu vsechny inputy a bud jim nastavim prazdnou hodnotu nebo

            //hlavni formular vyresetuju a pak nastavim hodnoty, ktere jsou explicitne
            //uvedene ve 'stavu' - promenne parameters
            $("#main_data_filter").objectFilterForm('setValues', parameters);

            var $filterstate_item = undefined;

            //pokud je definovano i ID aktivniho filtru tak provedu aktivaci
            //v ramci GUI
            if (typeof parameters['_fs'] !== 'undefined') {

                //ID filtru
                var filter_id = parameters['_fs'];

                var filter_list = methods._getData( $_this, 'filter_list' );

                if (typeof filter_list[filter_id] !== 'undefined') {

                    //referenci pouziju dale jako argument metody _sendQuery
                    $filterstate_item = filter_list[filter_id];

                    //provede aktivace filtru z pohledu GUI - tato metoda se vola
                    //i pri obnoveni stavu kvuli zmene window.location.hash
                    methods._activateFilterStateGUI ( $_this, $filterstate_item);
                }
            } else {
                methods._deactivateFilterState( $_this );
            }

            methods._sendQuery( $_this, parameters , $filterstate_item);

        },

        /**
         *
         */
        _setData: function( $this, key, value ) {

            if (typeof key === 'object' ) {
                var data = $this.data( plugin_name_space )
                if ( typeof data === 'undefined' ) {
                    data = {};
                }
                //budu extendovat to co mam aktualne v datech ulozene
                $.extend( data, key );

                $this.data( plugin_name_space, data );
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

            if (typeof current_data === 'undefined') {
                return undefined;
            }

            if (typeof key === 'undefined') {
                return current_data;
            }

            return current_data[ key ];

        },

        _log: function( text ) {
            if ( typeof console !== 'undefined') {
                console.log( text );
            }
        },

        // VEREJNE ROZHRANI //
        refresh: function( useCurrentFilterParams ) {

            this.each(function(){

                var $_this = $(this);

                //metoda zorbazi progress indicator a odesle pozadavek na nactenidat
                var request_params;
                if (typeof useCurrentFilterParams !== 'undefined' && useCurrentFilterParams === true) {
                    request_params = methods._getCurrentFilterParams($_this);
                    methods._setState( $_this, request_params );
                } else {
                    request_params = methods._getData( $_this , 'request_params');
                }

                methods._sendQuery( $_this, request_params);
                
            });
        }


    };

    $.fn.objectFilter = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {

            return methods[ method ].apply( $(this), Array.prototype.slice.call( arguments, 1 ) );

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.objectFilter');

        }

        return this;

    };

})( jQuery );
