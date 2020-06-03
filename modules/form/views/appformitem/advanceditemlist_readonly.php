<div class="appformitemadvanceditemlist <?= $css ?>" id="<?= $uid;?>">

    <ul class="list unstyled">
        <?php foreach ($rel_items as $rel_item): ?>
            <li id="<?=($rel_item->model instanceof Model_Room ? "li-room-".$rel_item->model->roomid : "")?>" 
            	class="item 
            	<?=($rel_item->model instanceof Model_Room && preg_match('#^HotelBeds #', $rel_item->model->room_type->name) ? "hotelbeds" : "")?>
            	<?=($rel_item->model instanceof Model_Room && ($rel_item->model->room_type->is_amenity_rate) ? "amenity_rate" : "")?>"
            >
                <?= (string)$rel_item;?>
            </li>
        <?php endforeach ?>
    </ul>

    <div class="clearfix cb"></div>
</div>