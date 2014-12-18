// <script>
$.confirm = function(message){
    return confirm(message);
}

$.userInfoMessage = function(message) {

    //pokud ma argument message hodnotu false, tak se aktualni zprava schova
    if (message === false) {
        //pokud je panel se zpravnou ve strance tak z domu "odpojim"
        if (typeof this.$message_placeholder !== 'undefined') {
            this.$message_placeholder.detach();
        }
        return;
    }

    //vytvorim novy div, ktery bude tvorit placeholder bloku se zpravou
    if (typeof this.$message_placeholder === 'undefined') {
        this.$message_placeholder = $( document.createElement('div') )
                                    .attr('id', 'msg1_container')
                                    .addClass('msg info top')
                                    .hide();

        this.$message_content = $( document.createElement('span') ).addClass('msg2');
        this.$message_placeholder.append( this.$message_content );
    } else {
        //panel se zpravou muze byt stale ve strance, protoze zprava informujici
        //o minule akci nemusela byt skryta
        this.$message_placeholder.detach();
    }

    //resetuju timeout, ktery muze stale byt aktivni (pokud nekdo zbesile klika)
    clearTimeout(this.timer);

    //vlastni zprava
    this.$message_placeholder.find('.msg2').html(message);

    //zobrazim a vlozim do stranky (po kazdem skryti je ze stranky detachovan
    this.$message_placeholder.show().appendTo('body');

    //reference jako lokalni promenna, ktera bude pristupna v callbacku v setTimetou
    var $ref = this.$message_placeholder;

    //za definovany interval skryju
    this.timer = window.setTimeout(function(){
        $ref.animate({
            opacity: 0.0
        }, 500, function(){
            //div vyprazdnim a skryju, opacitu vratim na puvodni hodnotu
            //aby to bylo pripraveno pro dalsi zobrazeni
            $(this).css({opacity:1.0}).detach();
       });
    }, 5000);
}

/**
 * Zorbazuje dialogove okno se zpravnou, ktere obsahuje
 * definovanou zpravu a tlacitka. Pokud nejsou tlacitka explicitne
 * definovane, tak obsahuje jen jedno tlacitko, ktere vyvola zavreni
 * okna se zpravou.
 */
$.userDialogMessage = function(message, buttons) {

    if (typeof this.$message_dialog === 'undefined') {
        //vytvorim nove dialogove okno, pro samotnou zpravu uzivatele
        this.$message_dialog = $( document.createElement('div') ).hide().html(message);

        //inicializace jQuery dialog, ktery zobrazi vyslednou zpravu
        this.$message_dialog.dialog({
                            dialogClass: 'ui-message_window',
                            autoOpen: false,
                            closeOnEscape: false,
                            modal:true,
                            draggable:false,
                            position: 'center',
                            resizable: false,
                            dialogClass: 'unexpected_error_dialog'
                            });
    }

    if (typeof buttons === 'undefined') {
        //defaultni varianta tlaciek obsahuje pouze tlacitko OK, kterym bude okno
        //se zpravou zavreno
        buttons = new Array(
            {
                text: "<?= __('jquery-ui._dialog.message_window_ok_button_label');?>",
                click: function(){
                    //zavru dialog
                    $(this).dialog('close');
                }
            }
        );        
    }

    //diaogu jsou prirazena tlacitka
    this.$message_dialog.dialog('option', 'buttons', buttons);
    //zobrazeni zpravy
    this.$message_dialog.dialog('open');
    //focus nahodim na prvni tlacitko na dialogu, tak aby bylo mozne
    //dialog se zpravou "od-enterovat"
    this.$message_dialog.find('.ui-dialog-buttonset .ui-button:first').focus();
}

/**
 * Tato funkce slouzi k zobrazeni obecne hlasky uzivateli, ktera obsahuje
 * info o tom ze doslo k neocekavane chybe. Hlaska muze obsahovat kontaktni
 * informace nebo treba i kontaktni formmular.
 */
$.userUnexpectedErrorDialogMessage = function()
{
    var message = '';
    if ($("#unexpected_error_message").length != 0) {
        message = $("#unexpected_error_message").html();
    } else {
        message = "<?= __('general.unexpected_error_message');?>";
    }
    return $.userDialogMessage(message);
}

/**
 * Tato funkce se pouziva k detekci validni chyby v error callbacku
 * pri provadeni ajax pozadavaku. Jako nevalidni chyba je oznaceno napriklad
 * automaticke zruseni ajax pozadavaku kdyz uzivatel opusti stranku - status
 * je pak roven hodnote '0' a v takovem pripade aplikace nesmi reagovat jako na chybu.
 */
$.isValidXHRError = function(jqXHR) {
    return jqXHR.status != 0;
}

/**
 * Metoda slouzi k zobrazeni qtipu s tematem konkretni napovedy (ID pozadovaneho
 * tema napovedy je dano druhym argumentem). Prvni argument definuje prvek ve
 * strance ke kteremu ma byt qtip "prilepen".
 */
$.showTipHelp = function($element, helpid) {

    //instanci qtipu definuji pouze jedenkrat a to k $(document) elementu
    //tato instance se pri kazdem pozadavku prilepi k pozadovanemu konkretnimu
    //prvku ve strance
    if (typeof this.$qtip === 'undefined') {

        $(document).qtip({
            content: {
                title: '<?= __('tip_help.tip_title');?>'
            },
            position: {
                my: 'left center',
                at: 'right center'
            },
            show: {
                //vzdy pouze jeden qtip ve strance
                solo: true
            },
            hide: {
                //pri mouseleave udalosti na cilovem prvku skryt qtip
                target: $element,
                event: 'mouseleave'
            }
        });
    }

    //pokud je uvnitr prvku div.text, tak prave jeho obsah bude zobrazen
    //jako obsahu qTipu namisto nacteni obsahu ajaxem

    if ($element.find('div.text').length != 0) {
        $(document).qtip('api').set('content.text', $element.find('div.text').html())
                               .set('position.target', $element);
    } else {
        //tento text je zobrazen nez doleti zpatky ajax pozadavek
        $(document).qtip('api').set('content.text', "<?= __('tip_help.loading_in_progress');?>")
                               .set('content.ajax', {
                                        url: "<?= appurl::object_action('help', 'tip');?>",
                                        data: {helpid: helpid},
                                        type: 'GET',
                                        //toto nastaveni mi ted pri testovani nefunguje - melo
                                        //by cachovat odpovedi na ajax pozadavky
                                        once: true
                               })
                               .set('position.target', $element);
    }

    //timto je qtip fyzicky zobrazen
    $(document).qtip('show');
}

$(document).ready(function() {
    $(".jq-datepicker").datepicker({ dateFormat: 'd.m.yy' });

    if ($('#hfm #flash_message').length) {
        $('#hfm').FlashMessage();
    }

});


// ajax function overload
// - redirect support added
$._ajax = function(arg1, arg2)
{
    // Get original success callback
    if (typeof (arg1) === 'object') {
        var originalCallback = arg1.success;
    } else if (typeof (arg2) === 'object') {
        var originalCallback = arg2.success;
    }

    // Put it into new callback and process potential redirect
    var newCallback = function(data, status, jqXHR) {
        // If redirect should be executed
        if (typeof data !== 'undefined' && typeof data.redirect_to === 'string') {
            window.location.href = data.redirect_to;
            return;
        }

        // Pokud se maji nahradit nejake casti DOMu, tak to udelame
        if (typeof data !== 'undefined' && typeof data._fill_dom === 'object') {
            for (var selector in data._fill_dom) {
                $(selector).html(data._fill_dom[selector]);
            }
        }

        if (typeof originalCallback === 'function') {
            originalCallback(data, status, jqXHR);
        }
    }

    // Change arguments callback
    if (typeof (arg1) === 'object') {
        arg1.success = newCallback;
    } else if (typeof (arg2) === 'object') {
        arg2.success = newCallback;
    }
    $.ajax(arg1, arg2);
}

// getJSON function overload
// - redirect support added
$._getJSON = function(arg0, arg1, arg2)
{
    // Get original success callback
    if (typeof (arg1) === 'function') {
        var originalCallback = arg1;
    } else if (typeof (arg2) === 'function') {
        var originalCallback = arg2;
    }

    // Put it into new callback and process potential redirect
    var newCallback = function(data, status, jqXHR) {
        // If redirect should be executed
        if (typeof data !== 'undefined' && typeof data.redirect_to === 'string') {
            window.location.href = data.redirect_to;
            return;
        }

        if (typeof originalCallback === 'function') {
            originalCallback(data, status, jqXHR);
        }
    }

    // Change arguments callback
    if (typeof (arg1) === 'function') {
        arg1 = newCallback;
    } else if (typeof (arg2) === 'function') {
        arg2 = newCallback;
    }
    $.getJSON(arg0, arg1, arg2);
}