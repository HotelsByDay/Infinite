
$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemLangStringPanelSlave(
        <?= json_encode($config); ?>
    );
});
