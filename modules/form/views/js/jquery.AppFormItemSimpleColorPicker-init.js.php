
$(document).ready(function(){
    alert('aabb');
    $("#<?= $uid;?>").AppFormItemSimpleColorPicker(<?= json_encode($config) ?>);

});
