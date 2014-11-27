<div class="appformitemrelnnselect appformitemcontainer <?= $css ?>" id="<?= $uid;?>">

    <?php if (!empty($error_message)): ?>
        <span class="validation_error alert alert-error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>

    <label><?= $label;?></label>

    <?php foreach ($items as $item):?>
    <div class="item">

        <label class="checkbox"><?= $item->preview();?>
            <input type="checkbox" <?= in_array($item, $selected) ? 'checked="checked"' : '';?> disabled="disabled"  id="item_<?=$item->pk();?>" value="<?= $item->pk();?>" name="<?= $attr;?>[]"/>
        </label>
       
    </div>
    <?php endforeach ?>
</div>