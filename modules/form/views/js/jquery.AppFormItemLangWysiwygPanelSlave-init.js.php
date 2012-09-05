
$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemLangWysiwygPanelSlave(
        <?= json_encode($config); ?>
    );
});
