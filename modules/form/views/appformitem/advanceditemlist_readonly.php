<div class="appformitemadvanceditemlist <?= $css ?>" id="<?= $uid;?>">
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