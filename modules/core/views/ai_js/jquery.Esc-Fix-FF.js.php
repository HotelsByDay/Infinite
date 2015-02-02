$(document).ready(function(){
    //udelam focus na body - coz zajisti ze kdyz uzivatel
    //nikam neklikne a zmackne klaveu Esc tak se pusti event handler
    //na 'body' a bude mozne zastavit propagaci udalosti, protoze ve FF
    //stisknuti klavesy Esc zrusi aktualne probihajici Ajax pozadavky
    if ($.browser.mozilla) {
        $("body").focus()
                 .keydown(function(event){
                        if (event.keyCode && event.keyCode === $.ui.keyCode.ESCAPE) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                 });
    }
});