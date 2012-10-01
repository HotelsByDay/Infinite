<div class="appformitemsubcategoryselect <?= $css ?>" id="<?= $uid;?>">

    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>


    <label><?= $label; ?></label>
    <?= form::select($attr.'[category]', $values, $value); ?>


    <label><?= $sub_label; ?></label>
    <div class="items">
        <?php  foreach ($items as $item):
            // Zjistime jestli je prvek zatrzen
            $checked = in_array($item->pk(), $selected['id']);
        ?>
        <div class="item">
            <input type="checkbox" <?= $checked ? 'checked="checked"' : '';?> id="item_<?= $attr;?>_<?=$item->pk();?>" value="<?= $item->pk();?>" name="<?= $attr;?>[id][<?= $item->pk() ?>]" />
            <label for="item_<?= $attr;?>_<?=$item->pk();?>" class="check"><?= $item->preview();?></label>
        </div>
        <?php endforeach ?>
    </div>

</div>