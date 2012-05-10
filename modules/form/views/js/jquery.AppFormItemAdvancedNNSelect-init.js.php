
$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemAdvancedNNSelect({
        data_url: "<?= $data_url?>",
        remove_interval: <?= $remove_interval ?>,
        _ps: <?= $page_size;?>
    });
});
