//<script>
/**
 *
 *
 */ 
$(document).ready(function(){
    
    $.fn.initCommentPanel = function(){

        //vytvorim si referenci, ktera bude dostupna i v closurech v teto funkci
        var $this = this;

        //provedu inicializaci formulare pro vlozeni noveho komentare
        $this.find('div.form_container').objectForm({
            //v pripade uspesneho ulozeni zaznamu musim vyvolat refresh dat
            //v object data panelu s komentari
            onActionResultSuccess: function(){
                //na object data panelu vyvolam refresh dat
                $this.find('.comment_list').objectDataPanel('refreshData');

                //zaroven schovam otevreny formular pro vlozeni noveho komentare
                $this.find('.form_container').hide();
                $this.find('.add').show();
            },
            onCloseButtonClick: function(){
                //zaroven schovam otevreny formular pro vlozeni noveho komentare
                $this.find('.form_container').hide();
                $this.find('.add').show();
                //nacte se prazdny editacni formular - to je pro pripady kdy
                //uzivatel akitoval editaci a potom formular jen zavrel (pri ulozeni
                //se na serveru automaticky resetuje)
                $this.find('div.form_container').objectForm("loadEditation", '');
                return false;
            }
        });
    }
});