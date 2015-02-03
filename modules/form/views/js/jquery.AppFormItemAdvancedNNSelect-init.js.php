
$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemAdvancedNNSelect({
        data_url: "<?= $data_url?>",
        remove_interval: <?= $remove_interval ?><?php if (isset($add_new_url)):?>,
        add_new_url: "<?= $add_new_url; ?>",
        dialog: <?= json_encode($dialog_config);?>
        <?php endif ?>,
        _ps: <?= $page_size;?>
    });
});
