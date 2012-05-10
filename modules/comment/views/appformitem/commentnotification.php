<div class="appformitemrelnnselect appformitemcommentnotification<?= $css ?>" id="<?= $uid;?>">

    <?php if (!empty($error_message)): ?>
        <span class="validation_error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>

    <label><?= $label;?></label>

    <span class="group_header"><?= __('appformitem.commentnotification.group_super_admins');?></span>
    <?php  foreach ($items['superadmin'] as $item):
        // Zjistime jestli je prvek zatrzen
        $checked = in_array($item->pk(), $selected['id']);
    ?>
    <div class="item">
        <label for="item_<?= $attr;?>_<?=$item->pk();?>"><?= $item->preview();?></label>
        <input type="checkbox" <?= $checked ? 'checked="checked"' : '';?> id="item_<?= $attr;?>_<?=$item->pk();?>" value="<?= $item->pk();?>" name="<?= $attr;?>[id][<?= $item->pk() ?>]" />
    </div>
    <?php endforeach ?>

    <span class="group_header"><?= __('appformitem.commentnotification.group_account_managers');?></span>
    <?php  foreach ($items['accountmanager'] as $item):
        // Zjistime jestli je prvek zatrzen
        $checked = in_array($item->pk(), $selected['id']);
    ?>
    <div class="item">
        <label for="item_<?= $attr;?>_<?=$item->pk();?>"><?= $item->preview();?></label>
        <input type="checkbox" <?= $checked ? 'checked="checked"' : '';?> id="item_<?= $attr;?>_<?=$item->pk();?>" value="<?= $item->pk();?>" name="<?= $attr;?>[id][<?= $item->pk() ?>]" />
    </div>
    <?php endforeach ?>

</div>