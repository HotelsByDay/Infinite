$(document).ready(function(){

    $("#<?= $uid;?>").AppFormItemPropertyAddress(
        <?= json_encode($config) ?>
    );
});
