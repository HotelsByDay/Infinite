$(document).ready(function(){

    $("#<?= $uid;?>").AppFormItemGPS(
        <?= json_encode($config) ?>
    );
});
