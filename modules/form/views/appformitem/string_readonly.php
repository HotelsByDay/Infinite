<div class="appformitemcontainer <?= $css?>">
    <?php if (!empty($error_message)): ?>
        <span class="validation_error alert alert-error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>
    <label for="<?= $attr ?>"><?= $label ?></label>
    <input type="text" id="<?= $attr ?>" readonly="readonly" value="<?= htmlspecialchars($value) ?>" class="<?= isset($input_class) ? $input_class : '' ?>" />
</div>