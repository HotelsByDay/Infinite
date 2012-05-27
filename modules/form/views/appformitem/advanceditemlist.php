<div class="appformitemadvanceditemlist <?= $css ?>" id="<?= $uid;?>">
    <span class="add_loader" style="display:none"><?= __('appformitemadvanceditemlist.add_pi');?></span>
    <a href="#" class="add button blue"><?= $add_button_label;?></a>

    <ul class="list">
        <?php foreach ($rel_items as $rel_item): ?>
        <li class="item">
            <?= (string)$rel_item;?>
            <div class="clearfix cb"></div>
        </li>
        <?php endforeach ?>
    </ul>
    <div class="clearfix cb"></div>
</div>