// <script>
    

(function( $ ) {

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemWatermark';

   
    /**
     * Metody pro tento plugin.
     */
    var methods = {
        
        init: function( options ) {
            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
            };
            //Pokud prislo nastaveni, tak mergnu s defaultnimi hodnotami
            $.extend( settings, options );

            /**
             * this je nyni neco jako jQuery iterator - to co vratil selector
             * volanim each zajistime provedeni definovane funkce 
             * postupne v kontextu kazdeho z vybranych elementu */
            return this.each(function() {
                
                var $this = $(this);
                // Img element s watermarkem - ten se bude posouvat
                var $watermark = $(".watermark_image", $this);

                // Inputy pro jednotlive hodnoty
                // Pozice watermarku - v % vuci cilovemu obrazku
                var $x = $("input[name$='[x]']", $this);
                var $y = $("input[name$='[y]']", $this);
                // Sirka watermarku - v % vuci cilovemu obrazku
                var $width = $("input[name$='[width]']", $this);
                // Pruhlednost - select
                var $opacity = $("select[name$='[opacity]']");
                
                // Sirka a vyska referencniho obrazku (na nem nastavujeme watermark)
                // Slouzi pro vypocty procentualnich rozmeru
                var width  = $watermark.parent().width();
                var height = $watermark.parent().height();
                
                
                
                // Nastavime watermarku spravnou pozici a rozmery
                if ($width.val() != '') $watermark.width($width.val()+'%');
                $watermark.css('top', $y.val()+'%');
                $watermark.css('left', $x.val+'%');
                // Nastavime obrazku zvolenou opacity
                $watermark.fadeTo('fast', $opacity.val() / 100);
                
                
                
                // Pri zmene opacity zmenime pruhlednosti obrazku
                $opacity.change(function(){
                    $watermark.fadeTo('fast', $(this).val() / 100);
                });
                
                // Nastavime watermark image jako resizable
                $watermark.resizable({ 
                    // Obrazek bude zustavat uvnitr rodicovskeho elementu
                    containment: 'parent',
                    // Zachovani aspect ratio
                    aspectRatio: true,
                    
                    // Callback volany pri zmene velikosti watermarku
                    // - aktualizujeme procentualni sirku v inputu
                    resize: function(event, ui) {
                        var w = ui.helper.width();
                        w = Math.round(w*100 / width);
                        $width.val(w);
                    }
                    
                // Zaroven watermark nastavime jako draggable
                });
//                .parent().draggable({
//                    // Obrazek bude zustavat uvnitr rodicovskeho elementu
//                    containment: 'parent',
//
//                    // Callback volany pri presouvani watermarku
//                    // - prepocteme pozici a zobrazime v inputech
//                    drag: function(event, ui) {
//                        var x = Math.round(ui.position.left*100 / width) + ($width.val() / 2);
//                        var y = Math.round(ui.position.top*100  / height) + (($watermark.height() / height * 100) / 2 );
//                        $x.val(x);
//                        $y.val(y);
//
//
//
//                    }
//
//                });


                $this.find('#appformitemwatermark_pos1,#appformitemwatermark_pos2,#appformitemwatermark_pos3,#appformitemwatermark_pos4,#appformitemwatermark_pos5,#appformitemwatermark_pos6,#appformitemwatermark_pos7').button();



                var padding = 10;

                //pozice nahore vlevo
                $("#appformitemwatermark_pos1").click(function(){
                    $watermark.css({
                        'top':  padding,
                        'left': padding
                    });
                });
                
                //pozice nahore uprostred
                $("#appformitemwatermark_pos2").click(function(){
                    $watermark.css({
                        'top':  padding,
                        'left': ( (width - $watermark.width()) / 2)
                    });
                });
                //pozice vpravo nahore
                $("#appformitemwatermark_pos3").click(function(){
                    $watermark.css({
                        'top':   padding,
                        'right': padding
                    });
                });
                //pozice vlevo dole
                $("#appformitemwatermark_pos4").click(function(){
                    $watermark.css({
                        'bottom': padding,
                        'left': padding
                    });
                });
                //pozice uprostred dole
                $("#appformitemwatermark_pos5").click(function(){
                    $watermark.css({
                        'bottom':  padding,
                        'left': ( (width - $watermark.width()) / 2)
                    });
                });
                //pozice vpravo dole
                $("#appformitemwatermark_pos6").click(function(){
                    $watermark.css({
                        'right':  padding,
                        'bottom': padding
                    });
                });
                //pozice uprostred uprostred
                $("#appformitemwatermark_pos7").click(function(){
                    $watermark.css({
                        'top' : ( (width  - $watermark.width())  / 2),
                        'left': ( (height - $watermark.height()) / 2)
                    });
                });
            });
            
        }
      
    };

    $.fn.AppFormItemWatermark = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {
                
            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemWatermark');
            
        }
        
        return this;

    };

})( jQuery );

