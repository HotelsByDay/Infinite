<div class="appformitemcontainer <?= $css ?> control-group <?= empty($error_message) ? '' : 'error' ?>">
    <label for="<?= $attr ?>_id"><?= $label ?></label>

    <?php if (isset($country_codes)): ?>
        <?= form::select($attr.'[country_id]', $country_codes, $country_id, Array('id'=>$attr.'_country_id', 'class' => '', 'style' => 'font-family: courier; width: 80px')) ?>
    <?php endif; ?>

    <input type="text" name="<?= $attr ?>[phone]" value="<?= $value ?>" class="input-medium" style="width: 120px; font-family: courier; " />

    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error text-error"><?= $error_message; ?></span>
    <?php endif ?>
</div>


