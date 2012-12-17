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

<div class="textarea_container">
    <textarea style="min-height: 200px;" id="<?= $attr ?>"<?= $name ?>><?= $value ?></textarea>
</div>

</div>