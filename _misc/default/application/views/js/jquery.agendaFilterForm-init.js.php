$(document).ready(function(){
    $("#main_data_filter").agendaFilterForm(<?= json_encode($params);?>);
});