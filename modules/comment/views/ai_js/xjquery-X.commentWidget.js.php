//<script>
/**
 * Funkce zajistuje otevreni dialogoveho okna, do ktere je nacten panel s komentari
 * pro zaznam, ktery je definovan parametry reltype a relid.
 *
 * Do obsahu dialogu se nacte prehled novych neprectenych komentaru, formular
 * pro vlozeni noveho komentare (aby mohl uzivatel rychle odpovedet) a odkaz
 * pro prechod na kompletni vypis komentaru.
 *
 */
$(document).ready(function(){ 
    
    jQuery.fn.commentWidgetOpen = function(reltype, relid, $widget){

        //pokud neni vytvorena instance jQuery dialogu, tak ji vytvorim
        if (typeof this.$dialog === 'undefined') {

            //vytvori se objekt dialogu, ale nebude zatim pripojen do DOMu
            this.$dialog = $( document.createElement('div') );

            //dovnitr pridam div, ktery ponese obsah
            this.$dialog.prepend( $( document.createElement('div') ).addClass('content') );

            //dialog se bude otevirat - pripojim jej tedy do DOMu
            $(document).prepend(this.$dialog);

            //inicializace dialogu
            this.$dialog.dialog({
                autoOpen: false,
                close: function(){
                    $(this).find('.content').empty();
                },
                width: 800,
                height: 650,
                title: $widget.attr('title'),
                resizable: true,
                draggable: true,
                modal: true
            });
        }

        //dialog uzivateli zobrazinm
        this.$dialog.dialog('open');

        //ihned po otevreni zobrazim progres indicator
        this.$dialog.block();

        //ajaxem zacnu nacitat obsah
        var parameters = {
            reltype: reltype,
            relid: relid
        };

        //reference na objekt dialogu - pres klicove slovo 'this' nebude v callbacku
        //pristupna
        var $dialog_ref = this.$dialog;

        $.ajax({
            type:'POST',
            url:"<?= url::base();?>comment/record_dialog_overview",
            data: parameters,
            success: function(response){

                //do dialogu vlozim obsah vygenerovany serverem
                if (typeof response['record_overview'] !== 'undefiend') {
                    $dialog_ref.find('.content').html(response['record_overview']);
                }

                //od serveru ocekavam i novy html kod pro widget - mel by zobrazovat
                //0 neprectenych komentaru
                if (typeof response['widget'] !== 'undefined') {
                    $widget.replaceWith(response['widget']);
                }

                //odblokuju UI
                $dialog_ref.unblock();
            },
            //doslo-li k nezname chybe (napriklad vystup serveru neni JSON, takze tam
            //asi doslo k nejake neocekavane chybe)
            error: function(response){
                //uzivateli bude zobrazena chybova zprava
                $.userUnexpectedErrorDialogMessage();
            },
            dataType: 'json'
        });
    }
});