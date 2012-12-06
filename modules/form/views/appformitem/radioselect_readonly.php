<div class="appformitemcontainer appformitemradioselect <?= $css ?>">
    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error alert alert-error" style="color:red;"><?= $error_message;?></span>
    <?php endif ?>

    <?php if ( ! empty($label)): ?>
        <label><?= $label ?></label>
    <?php endif; ?>


    <div class="items">
    <?php foreach ($values as $val => $label): ?>

            <label class="radio"><?= $label ?>
            <?= form::radio('', $val, ($val == $value), Array('disabled' => 'disabled')) ?>
            </label>

    <?php endforeach; ?>
    </div>
</div>