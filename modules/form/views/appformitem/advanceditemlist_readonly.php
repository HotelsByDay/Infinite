<div class="appformitemadvanceditemlist <?= $css ?>" id="<?= $uid;?>">

    <ul class="list unstyled">
        <?php foreach ($rel_items as $rel_item): ?>
            <li class="item <?=($rel_item->model instanceof Model_Room && preg_match('#^HotelBeds #', $rel_item->model->room_type->name) ? "hotelbeds" : "")?>">
                <?= (string)$rel_item;?>
            </li>
        <?php endforeach ?>
    </ul>

    <div class="clearfix cb"></div>
</div>