<div class="appformitemadvanceditemlist <?= $css ?>" id="<?= $uid;?>">

    <ul class="list unstyled">
        <?php foreach ($rel_items as $rel_item): ?>
            <li class="item">
                <?= (string)$rel_item;?>
            </li>
        <?php endforeach ?>
    </ul>

    <div class="clearfix cb"></div>
</div>