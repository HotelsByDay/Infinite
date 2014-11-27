<div class="appformitemcontainer appformitemwysiwyg <?= $css?>" id="<?= $uid;?>">
<?php
    if (isset($disabled)) {
        $name = ''; $disabled = ' disabled="disabled"';
    } else {
        $name = ' name="'.$attr.'"';
    }
?>

<?php if ( ! empty($error_message)): ?>
<span class="validation_error alert alert-error"><?= $error_message;?></span>
<?php endif ?>

<label for="<?= $attr ?>"><?= $label ?></label>

<div class="textarea_container" style="max-width: 960px;">
    <?= $value ?>
</div>

</div>