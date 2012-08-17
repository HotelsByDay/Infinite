
$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemSimpleColorPicker(<?= json_encode($config) ?>);

});
