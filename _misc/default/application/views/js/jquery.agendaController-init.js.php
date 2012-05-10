$(document).ready(function(){

    $dialog = $( document.createElement('div') ).hide()
                                                .appendTo($(this));

    //defaultni parametry dialogu
    var dialog_options = {
        modal:true,
        draggable:false,
        autoOpen: false
    };

    //inicializace jQuery dialogu
    $dialog._dialog(dialog_options);

    $(".agenda_button_new").click(function(){
        //href obsahuje URL pro nacteni formulare
        var url = $('a:first', this).attr('href');

        console.log(url);

        $dialog._dialog('loadForm', url, {}, function(response){
            if (response['action_status'] == '<?= AppForm::ACTION_RESULT_SUCCESS;?>') {
                
                //uzavru dialog
                $dialog._dialog('close');

                //pokud je ve strance filtr dat, tak
                $("#main_data_filter").objectFilter('refresh');
            }
        });

        return false;

    });

});