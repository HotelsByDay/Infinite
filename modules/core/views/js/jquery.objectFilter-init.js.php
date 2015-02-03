$(document).ready(function(){
    $("#main_data_filter").objectFilter(<?= text::json_encode($init_params, JSON_FORCE_OBJECT);?>);
});