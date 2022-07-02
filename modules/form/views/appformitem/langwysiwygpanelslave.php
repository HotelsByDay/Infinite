<div class="appformitemlangwysiwygpanelslave appformitemcontainer <?= $css?> " id="<?= $uid;?>">

    <?php if ( ! empty($error_message)): ?>
    <span class="validation_error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>


    <?php if ($label != ''): ?>

        <label for="<?= $uid.'_langinput' ?>"><?= $label ?></label>

    <?php endif ?>

    <?php foreach ($translates as $locale => $value): ?>
        <div>
            <textarea name="<?= $attr;?>[<?= $locale ?>]" id="<?= $uid . '_' . $locale .'_' . $attr ?>" data-locale="<?= $locale; ?>"><?= $value ?></textarea>
        </div>

    <?php endforeach; ?>

</div>
