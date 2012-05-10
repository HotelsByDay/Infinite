$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemReference({
        data_url: <?= json_encode($data_url);?>,
        preview: <?= json_encode($rel_objects_preview); ?>
    });
});
