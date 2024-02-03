<div class="appformitemadvanceditemlist <?= $css ?>" id="<?= $uid;?>">


    <span class="add_loader" style="display:none"><?= __('appformitemadvanceditemlist.add_pi');?></span>

    <?php if (isset($hint) && !empty($hint)): ?>
        <span class="hint"><?= $hint; ?></span>
    <?php endif ?>

    <ul class="list unstyled">
        <?php foreach ($rel_items as $rel_item): ?>
            <li id="<?=($rel_item->model instanceof Model_Room ? "li-room-".$rel_item->model->roomid : "")?>" 
                class="item 
                <?=($rel_item->model instanceof Model_Room && preg_match('#^Direct HB#', $rel_item->model->room_type->name) ? "hotelbeds" : "")?>
                <?=($rel_item->model instanceof Model_Room && ($rel_item->model->room_type->is_amenity_rate) ? "amenity_rate" : "")?>"
                >
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
