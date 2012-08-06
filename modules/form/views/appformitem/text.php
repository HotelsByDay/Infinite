<div class="appformitemcontainer <?= $css?>">
<?php
    if (isset($disabled)) {
        $name = ''; $disabled = ' disabled="disabled"';
    } else {
        $name = ' name="'.$attr.'"';
    }
?>

<?php if ( ! empty($error_message)): ?>
<span class="validation_error"><?= $error_message;?></span>
<?php endif ?>

<label for="<?= $attr ?>"><?= $label ?></label>
<textarea id="<?= $attr ?>"<?= $name ?><?= $disabled ?><?= isset($min_length) ? " minlength=\"$min_length\"" : '' ?> <?= isset($max_length) ? " maxlength=\"$max_length\"" : '' ?>><?= htmlspecialchars($value) ?></textarea>

</div>