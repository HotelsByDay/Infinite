<div class="appformitemsimpleitemlist <?= $css ?>" id="<?= $uid;?>">


    <div class="list">
        <?php foreach ($rel_items as $rel_item): ?>
        <div class="simple_list_item">
            <?= (string)$rel_item;?>
            <div class="clearfix cb"></div>
        </div>
        <?php endforeach ?>
    </div>

    <?php if ($add_enabled): ?>
        <div class="clearfix cb"></div>
        <a href="#" class="add_new btn btn-success"><?= $add_button_label;?></a>
    <?php endif; ?>

    <div class="clearfix cb"></div>
</div>