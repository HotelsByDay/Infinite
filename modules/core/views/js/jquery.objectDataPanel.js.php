//<script>
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'objectDataPanel';

    /**
     * Defaultni hodnoty pro parametry a nastaveni pluginu
     */
    var default_settings = {
        fulltext: true,
        fulltext_default: '',
        multiple_filters: true,
        filters:{}
    };

    /**
     * Metody pro tento plugin.
     */
    var methods = {
        
        init: function( options ) {

            return this.each(function(){

                var $this = $(this);

                //novy objekt pro ulozeni nastaveni teto instance
                var settings = new Object();

                //rozsirim o defaultni nastaveni
                $.extend(settings, default_settings);

                //vytvorim instanci objektu s nastavenim pro tuto instanci pluginu
                $.extend(settings, options);

                //ulozim si aktualni nastaveni pluginu
                methods._setData( $this , {
                    settings: settings
                });

                //protoze budu dynamicky vkladat do stranky prvky, tak cely element
                //skryju abych nezpusobil reflow
                //a odstranim jakykoli obsah prvku
                $content_container = $(document.createElement('div')).addClass('odp-content odp-content-initializing');

                $this.append( $content_container );

                //Pokud bude podle konfigurace aktivovan fulltextovy filtr
                //nebo aspon systemovy filtr, tak toto prepnu na false
                //coz zajisti ze bude container vlozen do prvku - tim se chci
                //vyhnout na test prazdnosti elementu
                var filter_container_empty = true;

                //vytvorim si blok ktery bude drzet ve ohledne filtru
                var $filter_container = $( document.createElement('div') ).addClass('odp-filter-container');


                //pred fulltext input vlozi tags container, ale pouze vpripade
                //ze budou vyuzity - tedy pokud jsou definovane nejake prednastavene
                //filtry. settings.filters. muze byt rovno null v pripade ze v konfigurace
                //neni definovany ani jeden prednastaveny filtr
                var has_preset_filters = (typeof settings.filters !== 'undefined' && settings.filters != null && settings.filters.length != 0 && settings.filters != '0');

                if ( has_preset_filters ) {
                    //vlozim blok, ktery zobrazuje tagy (ty slouzi k zobrazeni, ktere
                    //prednastavene filtry jsou aktivni)
                    var $tags_container = $( document.createElement('div') )
                                                    .addClass('odp-tags-container')
                                                    .appendTo($filter_container);

                    var $preset_filter_container = $( document.createElement('div') ).addClass('odp-presetfilters-container');

                    var $preset_filter_list = $( document.createElement('ul') ).addClass('odp-presetfilters-list')
                                                                                    .appendTo($preset_filter_container);
                    //dale musim zobrazit prvek, pro vyber systemoveho filtru
                    for (var index in settings.filters) {

                        var filter_item = settings.filters[index];

                        //do obsahu li vlozim nazev filtru
                        //a do dat ulozim jeho hodnotu
                        var filter_label = filter_item['label'];
                        var filter_value = filter_item['value'];
                        var is_default   = filter_item['default'] === true;

                        $( document.createElement('li') ).html( filter_label )
                                                         .data('v', filter_value )
                                                         .data('l', filter_label)
                                                         .click(function(){
                                                              methods._addTag( $this, $(this).data('l'), $(this).data('v') )
                                                          })
                                                         .appendTo($preset_filter_list);
                    }

                    //filtr container bude obsahovat minimalne systemove (prednastavene) filtry
                    filter_container_empty = false;
                }

                //dale vlozim input pro fulltextove vyhledavani - pokud je
                //podle konfigurace povoleno
                if ( settings.fulltext ) {

                    //dale si vlozim input pro fulltextove vyhledavani
                    //vlozim do containeru pro filtr
                    var $fulltext_input_container = $( document.createElement('div') )
                                                    .addClass('odp-fulltext-input-container input-append')
                                                    .appendTo($filter_container);


                    //vytvorim si input 
                    var $fulltext_input = $( document.createElement('input') ).attr('type', 'text')
                                            .val(settings.fulltext_default)
                                            .keypress(function(e){
                                                if (e.which == 13) {
                                                    methods._sendQuery($this);
                                                }
                                            }).addClass('odp-fulltext-input')
                                            .appendTo($fulltext_input_container);

                    //vytvorim si tlacitko pro vymazani obsahu filtru
                    //a nabnduju click udalost
                    var $clear_input_button = $( document.createElement('input')).attr('type', 'button').addClass('odp-fulltext-input-clear-button btn')
                        .val('x')
                        .click(function() {
                            $fulltext_input.val('').focus();
                            methods._removeTag( $this, true );
                        })
                        .appendTo($fulltext_input_container);

                                                                             
                    //filter_container bude obshovat minimalne fulltextovy filtry
                    filter_container_empty = false;
                }

                //pokud je tedy filter_container neprazdny, tak pridam i tlacitko
                //pro spusteni hledani
                if ( ! filter_container_empty ) {

                    //vytvorim si input
                    var $submit_input = $( document.createElement('button') )
                                               .attr('type', 'submit')
                                               .html('<?= __('object_data_panel.search');?>')
                                               .addClass('odp-submit-input button btn btn-primary')
                                               .click(function(){
                                                   //poslu pozadavek na hledani dat spolesne s parametry
                                                   methods._sendQuery( $this );

                                               })
                                               .appendTo($filter_container);
                }

                //preset_filter_container neste seznam prednstavenych filtru
                if ( has_preset_filters ) {
                    $preset_filter_container.appendTo( $filter_container );

                }

                //vytvorim instanci jQuery.dialogu spolecnou pro vsechny instance
                //tohoto pluginu, pokud jeste neexistuje
                if (typeof $dialog === 'undefined') {

                    var $dialog = $( document.createElement('div') ).addClass('obd-dialog_object')
                                                                .hide()
                                                                .appendTo($this);

                    //defaultni parametry dialogu
                    var dialog_options = {
                        modal:true,
                        draggable:false,
                        autoOpen: false
                    };

                    //vezmu defaultni nastaveni dialogu z argumentu metody
                    var dialog_options = $.extend(dialog_options, options['dialog']);

                    //inicializace jQuery dialogu
                    $dialog._dialog(dialog_options);

                    //referenci na objekt $dialog ulozim do dat instance
                    methods._setData($this, 'dialog', $dialog);
                }
                
                //funkce pro pridavani novych zaznamu
                if (typeof settings.newUrl !== 'undefined')
                {
                    //vytvori tlacitko pro pridani noveho zaznamu
                    var $add_new_button = $( document.createElement('button') ).html(settings['newButtonLabel'])
                                                                           .addClass('button btn btn-primary')
                                                                           .appendTo($filter_container);

                    //otevreni a nacteni obsahu dialogu
                    $add_new_button.click(function(){
                        methods._loadForm($this, settings.newUrl, $(this).html());
                    });
                }

                //konecne pripojim k DOMu prvek, ktery drzi prvky pro funkci filtru
                $filter_container.appendTo( $content_container );

                if ( has_preset_filters ) {
                    //tady najdu defaultni filtry a nastavim je jako aktivni
                    for (var index in settings.filters) {

                        var filter_item = settings.filters[index];

                        //do obsahu li vlozim nazev filtru
                        //a do dat ulozim jeho hodnotu
                        var filter_label = filter_item['label'];
                        var filter_value = filter_item['value'];
                        var is_default   = filter_item['default'] === true;

                        //pokud patri tento prednastaveny filtr mezi defaultne aktivni
                        //tak ho rovnou pridam
                        if ( is_default ) {
                            methods._addTag( $this, filter_label, filter_value );
                        }
                    }
                }

                //pridam div, ktery bude slouzit jako action result message paceholder
                $data_container = $( document.createElement('div') ).addClass('odp-data-action-placeholder')
                                                                    .appendTo($content_container);

                //pridam div, ktery ponese datovy obsah
                $data_container = $( document.createElement('div') ).addClass('odp-data-container')
                                                                    .appendTo($content_container);

                //inicializace dokoncena - muzu obsah znovu zobrazit
                $this.find('.odp-content').removeClass('odp-content-initializing');
                
                //inicialziace dokoncena - provedu uvodni nacteni dat
                methods._sendQuery( $this );

                //je aktivovana funkce pro automaticky periodicky refresh dat
                if (typeof settings['autorefresh'] !== 'undefined') {

                    //pocet kroku odpovida poctu sekund mezi refreshi
                    var steps = settings['autorefresh'];

                    //pripravim si progress indicator, kterym bude aktualizace
                    //rizena - tj. kdyz progress indicator dojede na 100% tak
                    //se vyvola updateQuery metoda
                    //progress indicator bude pruh, jehoz barevne pozadi se bude postupne
                    //natahovat na celou sirku

                    var $progress_indicator = $( document.createElement('div') ).addClass('odg-autorefresh-pi')
                                                                                .attr('style', 'width:100px;height:20px;')
                                                                                .appendTo($filter_container);

                    //pozadi progress indicatoru, ktere bude zobrazovat aktualni
                    //'postup'
                    var $progress_indicator_bar = $( document.createElement('div') ).addClass('odg-autorefresh-pi-bar')
                                                                                    .attr('style', 'background-color:red;width:0px;height:20px;')
                                                                                    .appendTo($progress_indicator);



                    var progress_indicator_cycle = function(){

                        $progress_indicator_bar.animate({width:'100%'}, steps * 1000, function(){

                            //graficky reset progress indicatoru
                            $progress_indicator_bar.css({width:'0px'});

                            //refresh dat
                            methods._updateQuery($this, undefined, true);

                            //nove vyvolani cyklu
                            progress_indicator_cycle();
                        });
                    }

                    progress_indicator_cycle();
                }
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

                //pokud nejsou vybrane zadne nabidky, tak uzivatele upozornim
                //na to ze musi nejake nabidky vybrat
                if (selected == '') {
                    //zobrazim zpravu - bude automaticky skryta za 60s
                    methods._showMessage($_this, "<?= 'Nebyly označeny žádné záznamy.';?>", 60000);
                    return false;
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
            $_this.find('.odp-data-action-placeholder').show().html(message);

            //pokud je definovan autohide, tak dojde k automaticke skryti panelu
            //po definovane dobe
            if (typeof autohide !== 'undefined') {
                $ref = $_this.find('.odp-data-action-placeholder');

                this.timer = window.setTimeout(function(){
                    $ref.hide();
                }, autohide);
            }
        },

        _hideMessage: function($_this) {
            $_this.find('.odp-data-action-placeholder').hide();
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

        _getSelectedItems: function($_this){

            var list = '';

            $_this.find('.odp-data-container input.item:checked').each(function(){
                list += $(this).attr('item_id') + ',';
            });

            return list;
        },

        _loadForm: function( $this, url, title, options) {

            var $dialog = methods._getData($this, 'dialog');

            //nadpis dialogu bude nastaven podle tretiho parametru
            $dialog._dialog('option', 'title', title);

            // dalsi options dialogu
            if (typeof options != 'undefined' && options) {
                for (var i in options) {
                    $dialog._dialog('option', i, options[i]);
                }
            }


            $dialog._dialog('loadForm', url, {}, function(response) {
                if (response['action_status'] == '<?= AppForm::ACTION_RESULT_SUCCESS;?>') {
                    $dialog._dialog('close');
                    //vyvolam refresh dat
                    methods._updateQuery($this);
                }
            });
        },

        /**
         * Metoda provadi animaci tagu prednastaveneho filtru.
         */
        _animateTag: function( $this, value ) {

            //nebo vyhledam taky podle hodnoty
            $this.find('.odp-tags-container .odp-tag').each(function(){
                if ( $(this).data('v') == value ) {

                    //tady se provede animace
                    //...
                }
            });

        },

        _addTag: function ( $this, label, value) {

            var settings = methods._getData( $this , 'settings');

            //pokud je zakazano mit vice aktivnich tagu, tak ty ktere jsou
            //nyni aktivni odstrnaim
            if ( ! settings.multiple_filters ) {

                methods._removeTag( $this, true );
            }

            //vezmu si aktualni vycet tagu
            var current_tags = methods._getData( $this, 'tags' );

            //pokud nejsou zatim zadne tagy aktivni, tak musim inicializovat objekt
            if ( typeof current_tags === 'undefined' ) {
                current_tags = {};
            }

            //pokud je tag uz aktivni, tak mu udelam vizualni animaci a koncim
            if ( typeof current_tags[value] !== 'undefined' ) {
                methods._animateTag( $this, value );
                return;
            }

            //vlozim do pole tagy, indexu podle hodnoty, protoze chci mit
            //tagy unikatni
            current_tags[value] = label;

            methods._setData( $this, 'tags', current_tags );

            //pridam tag vizualne

            $tag_element = $( document.createElement('div') ).addClass('odp-tag')
                                                             .data('v', value);
            //vytvorim popisek tagu a tlacitko pro jeho zruseni (tomu nabidnuju click udalost)
            $tag_label = $( document.createElement('span') ).html(label)
                                                            .appendTo($tag_element);

            $tag_remove_button = $( document.createElement('span') ).html('x')
                                                                    .appendTo($tag_element)
                                                                    .click(function(){
                                                                        //predam primo i referenci na prvek v DOMu - pro rychlejsi mazani
                                                                        methods._removeTag( $this, value, $(this).parents('.odp-tag') );
                                                                    });

            $this.find('.odp-tags-container').prepend($tag_element);

        },

        _removeTag: function ( $this, value, $dom_ref ) {


            //vezmu si aktualni vycet tagu
            var current_tags = methods._getData( $this, 'tags');

            //pokud nemam zadne tagy tak koncim
            if ( typeof current_tags === 'undefined' ) {
                return;
            }

            //pokud je value rovno true, tak odstranim vsechny tagy
            if ( value === true ) {

                methods._setData( $this, 'tags', {} );
                $this.find('.odp-tags-container .odp-tag').remove();

            } else {
                //tag odstranim tak ze jeho hodnotu nastavim na undefined
                delete current_tags[value];

                methods._setData( $this, 'tags', current_tags );

                //pokud mam k dispozici primo referenci na DOM prvek tak odstranim
                //ten a je hotovo
                if ( typeof $dom_ref !== 'undefined' ) {
                    $dom_ref.remove();
                } else {
                    //nebo vyhledam taky podle hodnoty
                    $this.find('.odp-tags-container .odp-tag').each(function(){
                        if ( $(this).data('v') == value ) {
                            $(this).remove();
                        }
                    });
                }
            }

        },

        _updateQuery: function( $this, params , dont_block_ui) {

            //vytahnu si aktualni parametry
            var current_params = methods._getData( $this, 'request_params' );

            if ( typeof current_params === 'undefined' ) {
                return false;
            }

            //aktualizuju
            $.extend( current_params, params );

            //pustim novy request
            return methods._sendQuery( $this, current_params , dont_block_ui);

        },

        _sendQuery: function( $_this, explicit_params , dont_block_ui) {

            //@TODO: Pridat progress indicator

            //pokud nejsou explicitne defionvane parametry vyhledavani
            //tak si je tady posbiram - explicitni definice je pouze
            //pro specialni pripady

            //pripravim parametry pro filtrovani
            var params = {};

            //vezmu hodnotu fulltext filtru
            params['_q'] = $_this.find('.odp-fulltext-input').val();

            //vycet aktivnich prednastavenych filtru
            //je take parametr
            var currently_active_tags = methods._getData( $_this, 'tags' );
            params['_f'] = [];
            var i = 0;
            for ( var tag_value in currently_active_tags ) {
                params['_f'][i++] = tag_value;
            }

            //standardne posbirane parametry prepisu temi co jsou explicitne definovane
            $.extend(params, explicit_params);

            //pokud existuje predchozi pozadavek, tak ho zrusim a potom
            //vytvorim novy
            var previous_request = methods._getData( $_this, 'request' );
            if ( typeof previous_request !== 'undefined' ) {
                previous_request.abort();
                delete previous_request;
            }

            //z nastaveni si vytahnu URL pro dotazovani na data
            var settings = methods._getData( $_this, 'settings');

            //k parametrum se jeste pokusim pridat velikost stranky, kterou si
            //uzivatel mohl jiz zvolit
            var request_params = methods._getData( $_this, 'request_params' );
            if (typeof request_params !== 'undefined' && typeof request_params['_ps'] !== 'undefined') {
                params['_ps'] = request_params['_ps'];
            }

            var $data_container = $_this.find('.odp-data-container');

            if ( ! dont_block_ui) {
                //aktivuji progress indicator
                $data_container.block({
                    message: "<?= __('general.blockUI_filtering_table_data');?>"
                });
            }

            //udelam si ajax object pro poslani post pozadavku
            var jqXHR = $._ajax(settings.dataUrl, {
                                        type: 'POST',
                                        data: params,
                                        success: function( data ){
                                            if ( typeof data['html'] !== 'undefined' ) {
                                                //do data containeru vlozim data - navic si udelam referenci
                                                $data_container.empty();
                                                $data_container.html(data['html'])
                                                //zavolam metodu, ktera zajisti inicializaci dat - napriklad pager
                                                methods._initDataContent( $_this, $data_container );

                                                if ( ! dont_block_ui) {
                                                    //odblokuju UI
                                                    $data_container.unblock();
                                                }
                                            }

                                            //na klici 'action_result' ocekavam HTML kod, ktery reprezentuje
                                            //vysledek provedene akce
                                            if (typeof data['action_result'] !== 'undefined') {

                                                //ze zpravy si vytvrim span prvek - odpoed muze obsahovat tlacitko
                                                //"undo"
                                                $message = $( document.createElement('span') ).html(data['action_result']);

                                                //do vysledku akce muze patrit tlacitko 'zpet', ktere akci vrati
                                                $message.find('.undo').click(function(){

                                                    //na klici 'action_name' ocekavam nazev provedene akce
                                                    //a na klici 'action_selected' ocekavam vybet vybranych
                                                    //prvku - tyto hodnoty sou v odpovedi jen kvuli tomu aby
                                                    //bylo mozne vyvolat 'undo' akci
                                                    var action   = data['action_name'];
                                                    var selected = data['action_selected']

                                                    methods._requestAction( $_this , action, selected, 0);

                                                    methods._hideMessage($_this);

                                                    return false;
                                                });

                                                methods._showMessage($_this, $message);
                                            }

                                        },
                                        error: function( jqXHR, textStatus, errorThrown ) {

                                            //funkce isValidXHRError slouzi predevsim k detekci
                                            //chyby AJAXu zpusobene opustenim stranky
                                            if ($.isValidXHRError(jqXHR)) {
                                                //uzivateli bude zobrazena obecna chybova hlaska
                                                $.userUnexpectedErrorDialogMessage();
                                            }

                                            if ( ! dont_block_ui) {
                                                //odblokuju UI
                                                $data_container.unblock();
                                            }
                                        },
                                        dataType: 'json'
            });

            //parametry i objekt reprezentujici pozadavek si ulozim
            //je to kvuli tomu ze pri strankovani budu pouzivat ulozene paramtry
            //filtrovnai - protoze uzivatel by mohl zmenit obsah inputu pro fulltext
            //a pak pouzit jenom strankovani nebo razeni
            methods._setData( $_this, {
                request: jqXHR,
                request_params: params
            });


        },

        _initDataContent: function( $_this ,$data_container ) {

            //tlacitka pro prechod na ostatni stranky s vysledky
            $data_container.find('.pager_button').click(function(){
                //pozadovany page index je ulozen v atributu 'pi'
                var page_index = $(this).attr('pi');
                //pokud na odkazu neni definovany atribut 'pi' - cilovy
                //page index na ktery tlacitko vede, tak se nebude nic
                //provade
                if (typeof page_index === 'undefined') {
                    return false;
                }
                //do ulozenych parametru vyhledavani ulozim novy offset
                //a odeslu novy pozadavek
                methods._updateQuery( $_this, {_pi: page_index} );
                //prevents default anchor action
                return false;
            })
            //vyber velikosti stranky
            $data_container.find('.page_size_select').change(function(){
                //pozadovana page size hodnota prvku
                var page_size = $(this).val();
                //do ulozenych parametru vyhledavani ulozim novy page_size
                //a odeslu novy pozadavek
                methods._updateQuery( $_this, {_ps: page_size} );
            });
            //prechod na specifcky radek vysledku
            $data_container.find('.page_index_input').keypress(function(e){
                if (e.which == 13) {
                    //pozadovany page offset je hodnotou tohoto inputu
                    var page_index = $(this).val();
                    //do ulozenych parametru vyhledavani ulozim novy offset
                    //a odeslu novy pozadavek
                    methods._updateQuery( $_this, {_pi: page_index} );
                }
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
                methods._updateQuery( $_this, {_ob: ob, _obd: obd, _pi:1} );
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
            
            //inicializuje tlacitka pro vyvolani hromadnych akci nad zaznamy
            methods._bindItemActionControl( $_this, $data_container );

            //moznost editace, pres Ajax-loaded formularu v dialogovem okne
            $data_container.find('.edit_ajax[href]').each(function(){
                $(this).click(function(){
                    var settings = methods._getData($_this, 'settings');
                    var $clicked_item = $(this);
                    if (typeof settings['onEditAjaxClick'] === 'function') {
                        return settings['onEditAjaxClick']($clicked_item);
                    } else {
                        var options = {};
                        var width = $clicked_item.attr('data-dialog-width');
                        var height = $clicked_item.attr('data-dialog-height');
                        if (typeof width != 'undefined' && width) {
                            options['width'] = width;
                        }
                        if (typeof height != 'undefined' && height) {
                            options['height'] = height;
                        }
                        methods._loadForm($_this, $(this).attr('href'), null, options);
                        return false;
                    }
                });
            });

            //pokud je v nastaveni definovany callback - after_initDataContent
            //tak ho vyvolam
            var settings = methods._getData( $_this, 'settings' );

            if (typeof settings['after_initDataContent'] === 'function') {
                settings['after_initDataContent']( $_this , $data_container);
            }
        },

        /**
         *
         */
        _setData: function( $this, key, value ) {

            if (typeof key === 'object' ) {

                var current_data = $this.data( plugin_name_space);

                if (typeof current_data === 'undefined') {
                    current_data = new Object();
                }

                //budu extendovat to co mam aktualne v datech ulozene
                $.extend( current_data , key);

                $this.data( plugin_name_space, current_data );

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

            if (typeof key === 'undefined') {
                return current_data;
            }

            return current_data[ key ];

        },

        refreshData: function() {

            return this.each(function(){

                methods._sendQuery($(this));
            });
            
        },

        _log: function( text ) {
            if ( typeof console !== 'undefined') {
                console.log( text );
            }
        }

    };

    $.fn.objectDataPanel = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.objectDataPanel');
            
        }
        
        return this;

    };

})( jQuery );