$(document).ready(function(){
    $("#lang_panel_list a").click(function(){

        //atualni adresa vcetne hashe (na tu bude uzivatel presmerovan po tom
        //co system zmeni aktualni jazyk)
        var url = location.href;

        //kod zvoleneho jazyka
        var lang_code = $(this).attr('name');
        
        //presmeruju uzivatele na URL pro zmenu jazyka
        var target_url = "<?= url::base();?>multilang/set?l=" + encodeURIComponent(lang_code) + "&r=" + encodeURIComponent(url);

        //spusti presmerovani
        window.location = target_url;

        return false;
    });
});