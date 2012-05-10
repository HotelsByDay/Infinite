<div class="appformitemrelnnselect <?= $css ?>" id="<?= $uid;?>">

    <?php if (!empty($error_message)): ?>
        <span class="validation_error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>

    <label><?= $label;?></label>

    <?php  foreach ($items as $item):
        // Zjistime jestli je prvek zatrzen
        $checked = in_array($item->pk(), $selected['id']);
    ?>
    <div class="item">
        <label for="item_<?= $attr;?>_<?=$item->pk();?>"><?= $item->preview();?></label>
        <input type="checkbox" <?= $checked ? 'checked="checked"' : '';?> id="item_<?= $attr;?>_<?=$item->pk();?>" value="<?= $item->pk();?>" name="<?= $attr;?>[id][<?= $item->pk() ?>]" />


        <?php if ($note): ?>
            <div class="note_outer" <?= $checked ? '' : 'style="display: none;"' ?>>
                <input type="text" name="<?= $attr ?>[note][<?= $item->pk() ?>]"
                       value="<?= $checked ? arr::get($selected['note'], $item->pk()) : '' ?>"
                       <?= $checked ? '' : 'disabled="disabled"' ?> />
            </div>
        <?php endif; ?>

    </div>
    <?php endforeach ?>
</div>