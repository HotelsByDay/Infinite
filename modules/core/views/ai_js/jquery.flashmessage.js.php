// <script>
(function( $ ){
  $.fn.FlashMessage = function( msg ) {
        // Skryjeme vsechny predchozi flash zpravy - uzivatel provedl akci ktera vyvolala
        // zobrazeni dalsi zpravy - takze pripadnou predchozi zpravu jiz urcite mel cas zaregistrovat
        $('.flash_message').remove();

        var $this = $(this);
        var append = false;
        // Pokud zprava neni zadana parametrem
        // predpokladame, ze se vytvari nad divem obsahujicim zpravu
        // Jinak do tela divu nastavime parametr
        if (typeof(msg) != 'undefined' && msg) {
            $this.html(msg);
            append = true;
        }
        // Tyto tridy divu vzdy radsi pridame
        $this.addClass('flash_message');

        if (append) {
            $('body').append($this);
        }

        $this.show();
        $this.click(function() {
            $this.fadeOut('medium', function(){$this.remove()});
        });
        setTimeout(function(){$this.fadeOut('medium', function(){$this.remove()})}, 10000);
  };

})(jQuery);
