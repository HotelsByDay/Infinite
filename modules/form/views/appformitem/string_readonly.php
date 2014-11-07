<div class="appformitemcontainer form-group <?= $css?>">
    <?php if (!empty($error_message)): ?>
        <span class="validation_error alert alert-error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>
    <label for="<?= $attr ?>"><?= $label ?></label>
    <input type="text" id="<?= $attr ?>" name="<?= $attr ?>" readonly="readonly" disabled class="form-control" value="<?= htmlspecialchars($value) ?>"/>
</div>