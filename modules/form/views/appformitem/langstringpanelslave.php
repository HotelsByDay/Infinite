<div class="appformitemlangstringpanelslave appformitemcontainer <?= $css?> " id="<?= $uid;?>">

    <?php if ( ! empty($error_message)): ?>
    <span class="validation_error alert alert-error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>


    <?php if ($label != ''): ?>

        <label for="<?= $uid.'_langinput' ?>"><?= $label ?></label>

    <?php endif ?>

    <input class="langinput input-block-level" type="text" id="<?= $uid.'_langinput' ?>" placeholder="" value="" <?= isset($min_length) ? "minlength=\"$min_length\"" : '' ?> <?= isset($max_length) ? "maxlength=\"$max_length\"" : '' ?> />


        <?php $i=0; foreach ($translates as $locale => $value): $i++; ?>

                <input type="hidden" name="<?= $attr;?>[<?= $locale ?>]" value="<?= htmlspecialchars($value) ?>" data-locale="<?= $locale; ?>" />

        <?php endforeach; ?>

</div>