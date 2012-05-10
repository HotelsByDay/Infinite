<div class="appformitemadvanceditemlist <?= $css ?>" id="<?= $uid;?>">
    <span class="add_loader" style="display:none"><?= __('appformitemadvanceditemlist.add_pi');?></span>
    <a href="#" class="add button blue"><?= $add_button_label;?></a>

    <div class="list">
        <?php foreach ($rel_items as $rel_item): ?>
        <div class="item">
            <?= (string)$rel_item;?>
        </div>
        <?php endforeach ?>
       <div class="clearfix cb"></div>
    </div>
    <div class="clearfix cb"></div>
</div>