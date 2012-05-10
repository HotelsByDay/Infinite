function( $_this, $data_container ) {

    //inicializace funkce pro prepnuti tydne
    $data_container.find(".switch_week").each(function(){

        //prectu cislo tydne
        var week_number = $(this).attr('w');

        $(this).click(function(){

            $_this.objectFilter('_updateQuery', $_this, {w:week_number} );

        });
    });

    //tady je potreba provest inicializaci funkce checkboxu pro nastaveni ukolu
    //jako finished nebo "un-finished"
    $data_container.find(".task_finished").click(function(){

        //prectu stav checkboxu
        var finished = $(this).is(":checked");

        //pretypovani na int
        finished = finished + 0;

        //ID zaznamu
        var item_id = $(this).attr('item_id');

        //docasne disabluju
        $(this).attr('disabled', 'disabled');

        //reference bude dostupna v callbacku
        $this = $(this);

        //odeslu pozadavek na server
        $.getJSON("<?= appurl::object_action('agenda', 'task_finished');?>", {f:finished, id:item_id}, function(){

            //vyvolam refresh dat
            $("#main_data_filter").objectFilter('refresh');

            $this.removeAttr('disabled');
        });
    });
    
}