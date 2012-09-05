<div class="appformitemlangwysiwygpanelslave appformitemcontainer <?= $css?> " id="<?= $uid;?>">

    <?php if ( ! empty($error_message)): ?>
    <span class="validation_error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>


    <?php if ($label != ''): ?>

        <label for="<?= $uid.'_langinput' ?>"><?= $label ?></label>

    <?php endif ?>

    <textarea class="langinput" id="<?= $uid.'_langinput' ?>" placeholder="" <?= isset($min_length) ? "minlength=\"$min_length\"" : '' ?> <?= isset($max_length) ? "maxlength=\"$max_length\"" : '' ?>></textarea>


        <?php $i=0; foreach ($translates as $locale => $value): $i++; ?>

            <textarea class="hidden" name="<?= $attr;?>[<?= $locale ?>]" data-locale="<?= $locale; ?>" style="display: none;"><?= $value ?></textarea>

        <?php endforeach; ?>

</div>