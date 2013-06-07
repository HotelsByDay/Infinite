//<script>
(function( $ ) {

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = '_ajaxForm';


    var methods = {

        blockUI: function(options) {
            $(this).block();
        },
        unblockUI: function() {
            $(this).unblock();
        },

        /**
         * Provadi inicializaci nacteneho formulare.
         */
        _initForm: function($content , url , options, action_result_callback) {

            //focus hodim na prvni viditelny formularovy prvek
        //    $content.find('input:visible,textarea:visible,select:visible').first().focus();


            //chci zamezit odeslani formulare
            $content.find('form').submit(function(){
                return false;
            });

            var $this = $(this);

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
                $this._ajaxForm('blockUI', {message: $(this).attr('ptitle')});

                //formular odeslu na URL odkud jsem ho nacetl
                $._ajax({
                    type:'POST',
                    url:url,
                    data: form_data,
                    success: function(response) {

                        //pokud ze serveru prisel novy obsah, tak jej vlozim do formulare
                        if (typeof response['content'] !== 'undefined')
                        {
                            $content.html(response['content']);
                            $this._ajaxForm('_initForm', $content, url, options, action_result_callback);
                        }

                        if (typeof response['action_status'] !== 'undefined')
                        {
                            //pri uspechu zobrazim pouze informacni hlaseni, ktere nevyzaduje reakci uzivatele
                            if (response['action_status'] == '<?= AppForm::ACTION_RESULT_SUCCESS;?>') {

                                    // Result callbacku
                                    var callbackResult;

                                    // zobrazi zpravu pro uzivatele
                                    $.userInfoMessage(response['action_result']);

                                    // vyvolam callback pokud je definovan - predam mu komplet
                                    // data, ktera prisla od serveru
                                    if (typeof action_result_callback !== 'undefined')
                                    {
                                        callbackResult = action_result_callback(response);
                                    }

                            } else {

                                //schovam zpravu informujici o uspesnem ulozeni - ta muze
                                //byt v tuto chvili zobrazena
                                $.userInfoMessage(false);
                            }

                            //odblokuju UI
                            $this._ajaxForm('unblockUI');



                        } else {

                            //vyvolam callback pokud je definovan - predam mu komplet
                            //data, ktera prisla od serveru
                            if (typeof action_result_callback !== 'undefined')
                            {
                                action_result_callback(response);
                            }
                            //odblokuju UI
                            $this._ajaxForm('unblockUI');
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
        loadForm: function(load_url, arg1 , arg2) {
            console.log('loadForm called');
            //obsah dialogu odstranim (promennou $content vyuzije nize)
            var $content = $(this);
            //zruseni obsahu
            $content.empty().css('display', 'invisible');
            //pripravim si argumenty - arg1 muze byt callback nebo data, ktera odeslu
            //pri prvnim nacteni formu. A arg2 muze byt callback nebo undefined.

            var options                = typeof arg1 !== 'undefined' ? arg1 : undefined;
            var action_result_callback = typeof arg2 === 'function'  ? arg2 : undefined;

            var $this = $(this);
            //zablokovani UI
            $this._ajaxForm('blockUI');

            var $this = $(this);

            //nacteni obsahu (formulare)
            $._ajax({
                type:'POST',
                url:load_url,
                data: options,
                success: function(response) {
                    //na klici 'content' ocekavam HTML kod, ktery predstavuje formulare
                    if (typeof response['content'] !== 'undefined')
                    {
                        $content.html(response['content']);

                        $this._ajaxForm('_initForm', $content, load_url, options, action_result_callback);

                        action_result_callback(response);
                    }

                    //zobrazim obsah dialogu
                    $content.css('display', 'block');

                    //odblokuju UI
                    $this._ajaxForm('unblockUI');
                },
                error: function(){
                    //uzivatel bude zobrazeno chybove hlaseni a potom bude dialogove okno zavreno
                    $.userUnexpectedErrorDialogMessage();
                    //odblokuji UI
                    $this._ajaxForm('unblockUI');
                },
                dataType: 'json'
            });
        }
    }; // end of methods list


    $.fn._ajaxForm = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {

            return methods[ method ].apply( this, Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery._ajaxForm');

        }

        return this;

    };

})( jQuery );