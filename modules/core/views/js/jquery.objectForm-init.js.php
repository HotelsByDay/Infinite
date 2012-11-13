$(document).ready(function(){
    $("#edit_content").objectForm(<?= json_encode($config) ?>);
});