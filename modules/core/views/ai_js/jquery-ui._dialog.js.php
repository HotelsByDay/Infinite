//<script>
/**
 * 
 */
$.widget("ui._dialog", $.ui.dialog, {

    blockUI: function(options) {
        this.options._closeOnEscape = this.options.closeOnEscape;
        this.options.closeOnEscape = false;
        this.uiDialog.block(options);
    },
    unblockUI: function() {
        this.uiDialog.unblock();
        this.options.closeOnEscape = this.options._closeOnEscape;
    },

    /**
     * Provadi inicializaci nacteneho formulare.
     */
    _initForm: function($content , url , options, action_result_callback) {

        //focus hodim na prvni viditelny formularovy prvek
        $content.find('input:visible,textarea:visible,select:visible').first().focus();

        //chci zamezit odeslani formulare
        $content.find('form').submit(function(){
            return false;
        });

        var _this = this;

        //zavreni formulare
        $content.find('.<?= AppForm::FORM_BUTTON_CLOSE_CSS_CLASS;?>').click(function(){
            //dialogove okno zavru
            _this.close();
        });

        //odeslani formulare vyvolam rucne pri kliknuti na jedno z tlacitek
        $content.find('.<?= AppForm::FORM_BUTTON_CSS_CLASS;?>').click(function(data){

            //pokud ma tlacitko confirm atribut, tak bude zpracovan
            if (typeof $(this).attr('confirm') !== 'undefined' && confirm($(this).attr('confirm')) == false){
                return;
            }

            //ziskam formularova data
            var form_data = $content.find('form').serialize();

            //pripojim identifikaci stisknuteho formularoveho tlacitka
            form_data += '&'+$(this).attr('name')+'='+$(this).val();

            //k datum formulare pridam
            if (typeof options !== 'undefined' && options.length != 0) {
                form_data += '&'+$.param(options);
            }

            //zablokuju UI
            _this.blockUI({message: $(this).attr('ptitle')});

            //formular odeslu na URL odkud jsem ho nacetl
            $.ajax({
                type:'POST',
                url:url,
                data: form_data,
                success: function(response){

                    //pokud ze serveru prisel novy obsah, tak jej vlozim do formulare
                    if (typeof response['content'] !== 'undefined')
                    {
                        $content.html(response['content']);
                        _this._initForm($content, url, options, action_result_callback);
                    }

                    if (typeof response['action_status'] !== 'undefined')
                    {
                        //pri uspechu zobrazim pouze informacni hlaseni, ktere nevyzaduje reakci uzivatele
                        if (response['action_status'] == '<?= AppForm::ACTION_RESULT_SUCCESS;?>') {

                                //zobrazi zpravu pro uzivatele
                                $.userInfoMessage(response['action_result']);

                                //vyvolam callback pokud je definovan - predam mu komplet
                                //data, ktera prisla od serveru
                                if (typeof action_result_callback !== 'undefined')
                                {
                                    action_result_callback(response);
                                }

                                //dialogove okno zavru
                                _this.close();

                                
                        } else {

                            //schovam zpravu informujici o uspesnem ulozeni - ta muze
                            //byt v tuto chvili zobrazena
                            $.userInfoMessage(false);
                        }

                        //odblokuju UI
                        _this.unblockUI();


                        /*else {

                            //uzivateli bude zobrazena chybova zprava
                            $.userDialogMessage(response['action_result'], [
                                {
                                    text: "<? __('jquery-ui._dialog.message_window_ok_button_label');?>",
                                    click: function(){
                                        //zavru dialog
                                        $(this).dialog('close');

                                        //vyvolam callback pokud je definovan
                                        if (typeof action_result_callback !== 'undefined')
                                        {
                                            action_result_callback(response);
                                        }

                                        //odblokuju UI
                                        _this.unblockUI();
                                    }
                                }
                            ]);
                        }*/

                    } else {

                        //vyvolam callback pokud je definovan - predam mu komplet
                        //data, ktera prisla od serveru
                        if (typeof action_result_callback !== 'undefined')
                        {
                            action_result_callback(response);
                        }
                        //odblokuju UI
                        _this.unblockUI();
                    }
                },
                error: function(){
                    //uzivateli bude zobrazena chybova zprava
                    $.userUnexpectedErrorDialogMessage();
                },
                dataType: 'json'
            });
            return false;
        });

    },

    /**
     * Metoda slouzi k nacteni editacniho formulare do dialogu.
     *
     * Zajistuje zablokovani UI a zpracovani standardni odpovedi - tj. zobrazi
     * vlastni formular, nastavi title dialogu apod.
     *
     */
    loadForm: function(url, arg1 , arg2) {
        //obsah dialogu odstranim (promennou $content vyuzije nize)
        $content = $(this.uiDialog).find('.ui-dialog-content.ui-widget-content:first');
        //zruseni obsahu
        $content.empty().css('display', 'invisible');
        //vymazani title
        this.option("title", '');
        //pripravim si argumenty - arg1 muze byt callback nebo data, ktera odeslu
        //pri prvnim nacteni formu. A arg2 muze byt callback nebo undefined.

        var options                = typeof arg1 !== 'undefined' ? arg1 : undefined;
        var action_result_callback = typeof arg2 === 'function'  ? arg2 : undefined;

        //otevru dialog
        this.open();
        //zablokovani UI
        this.blockUI();
        //zpristupneni uvnitr callbacku ve funkci getJSON
        var _this = this;
        //nacteni obsahu
        $.ajax({
            type:'POST',
            url:url,
            data: options,
            success: function(response){

                //na klici 'preview' ocekavam retezec pro title dialogu
                if (typeof response['headline'] !== 'undefined')
                {
                    _this.option("title", response['headline']);
                }

                //na klici 'content' ocekavam HTML kod, ktery predstavuje formulare
                if (typeof response['content'] !== 'undefined')
                {
                    $content.html(response['content']);

                    //provede inicializaci ovladacich prvku formulare
                    _this._initForm($content, url, options, action_result_callback);
                }

                //zobrazim obsah dialogu
                $content.css('display', 'block');

                //odblokuju UI
                _this.unblockUI();
            },
            error: function(){
                //uzivatel bude zobrazeno chybove hlaseni a potom bude dialogove okno zavreno
                $.userUnexpectedErrorDialogMessage();
                //v pripade chyby dojde k zavreni dialogu
                _this.close();
                //odblokuji UI
                _this.unblockUI();
            },
            dataType: 'json'
        });
    }
});