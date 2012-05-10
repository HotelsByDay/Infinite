
$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemRelNNSelect(
        <?= json_encode($config); ?>
    );
});
