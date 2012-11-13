<div class="appformitemcontainer appformitemradioselect <?= $css ?>">
    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error" style="color:red;"><?= $error_message;?></span>
    <?php endif ?>

    <?php if ( ! empty($label)): ?>
        <label><?= $label ?></label>
    <?php endif; ?>


    <div class="items">
    <?php foreach ($values as $val => $label): ?>
        <div class="span6">
            <label class="radio"  for="<?= $uid ?>_<?= $val ?>"><?= $label ?>
            <?= form::radio($attr, $val, ($val == $value), Array('id' => $uid.'_'.$val)) ?>
            </label>
        </div>
    <?php endforeach; ?>
    </div>
</div>