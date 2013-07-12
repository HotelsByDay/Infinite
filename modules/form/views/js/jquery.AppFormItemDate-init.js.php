
$(document).ready(function(){
    $('.date_picker').datepicker(<?= json_encode($config) ?>);
});
