$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemSendPassword(<?= json_encode($config) ?>);
});