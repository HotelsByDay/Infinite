
$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemRelSelect({
        attr: "<?= $attr;?>",
        preview: "<?= $preview ?>",
        _ps: <?= $page_size;?>,
        min_length: <?= $min_length;?>,
        data_url: "<?= $data_url?>"<?php if (isset($add_new_url)):?>,
        add_new_url: "<?= $add_new_url; ?>",
        dialog: <?= json_encode($dialog_config);?>
        <?php endif ?>
        <?php if (isset($filter_parent_attr)):?>,
        filter_parent_attr: <?= json_encode($filter_parent_attr); ?>
        <?php endif ?>
        <?php if (isset($filter_child_attr)):?>,
        filter_child_attr: <?= json_encode($filter_child_attr); ?>
        <?php endif ?>


    });
});
