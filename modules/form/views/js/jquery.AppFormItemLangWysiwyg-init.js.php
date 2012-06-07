
$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemLangWysiwyg(
        <?= json_encode($config); ?>
    );
});
