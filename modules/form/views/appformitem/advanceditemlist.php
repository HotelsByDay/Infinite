<div class="appformitemadvanceditemlist <?= $css ?>" id="<?= $uid;?>">


    <span class="add_loader" style="display:none"><?= __('appformitemadvanceditemlist.add_pi');?></span>

    <?php if (isset($hint) && !empty($hint)): ?>
        <span class="hint"><?= $hint; ?></span>
    <?php endif ?>

    <ul class="list unstyled">
        <?php foreach ($rel_items as $rel_item): ?>
            <li class="item <?=($rel_item->model instanceof Model_Room && preg_match('#^HotelBeds #', $rel_item->model->room_type->name) ? "hotelbeds" : "")?>">
                <?= (string)$rel_item;?>
            </li>
        <?php endforeach ?>
    </ul>

    <div class="clearfix cb"></div>

    <?php if ( ! $readonly): ?>
        <a href="#" class="btn btn-success add button grey"><?= $add_button_label;?></a>
    <?php endif; ?>

    <div class="clearfix cb"></div>
</div>