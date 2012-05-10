
$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemLangString(
        <?= json_encode($config); ?>
    );
});
