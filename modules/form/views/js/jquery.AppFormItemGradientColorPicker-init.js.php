
$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemGradientColorPicker(<?= json_encode($config) ?>);
});
