<div class="appformitemsimpleitemlist appformitemcontainer <?= $css ?>" id="<?= $uid;?>">

    <?php if (isset($label) and $label): ?>
        <label class="main"><?= $label ?></label>
    <?php endif; ?>
    <div class="list">
        <?php foreach ($rel_items as $rel_item): ?>
        <div class="simple_list_item">
            <?= (string)$rel_item ?>
            <div class="clearfix"></div>
        </div>
        <?php endforeach ?>
    </div>


    <div class="clearfix cb"></div>
    <div class="save_info_message alert alert-info" style="display: none;">
        <?= __('simpleitemlist.save_the_form.info_message') ?>
    </div>

    <?php if ($add_enabled): ?>
        <div class="clearfix cb"></div>
        <div class="add_button">
            <a href="#" class="add_new btn btn-success"><?= $add_button_label;?></a>
        </div>
    <?php endif; ?>

    <div class="clearfix cb"></div>
</div>