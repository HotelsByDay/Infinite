
$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemRelSelect(<?= json_encode($config) ?>);
});
