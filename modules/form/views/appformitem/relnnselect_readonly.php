<div class="appformitemrelnnselect <?= $css ?>" id="<?= $uid;?>">

    <?php if (!empty($error_message)): ?>
        <span class="validation_error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>

    <label><?= $label;?></label>

    <?php foreach ($items as $item):?>
    <div class="item">
        <label for="item_<?=$item->pk();?>"><?= $item->preview();?></label>
        <input type="checkbox" <?= in_array($item, $selected) ? 'checked="checked"' : '';?> disabled="disabled"  id="item_<?=$item->pk();?>" value="<?= $item->pk();?>" name="<?= $attr;?>[]"/>
       
    </div>
    <?php endforeach ?>
</div>