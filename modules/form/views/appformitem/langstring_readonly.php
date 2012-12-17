<div class="appformitemlangstring appformitemcontainer <?= $css?> " id="<?= $uid;?>">

    <?php if (!empty($error_message)): ?>
    <span class="validation_error alert alert-error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>


    <div class="langitems">

        <?php $i=0; foreach ($translates as $locale => $value): $i++; ?>
        <div class="langitem">

            <?php if ($i > 1) $final_label = $label.' '.$i; else $final_label = $label; ?>

            <label for="<?= $attr.'_'.$i ?>"><?= $final_label ?></label>
            <span class="value"><?= arr::get($locales, $locale);?></span>

            <input class="langinput" type="text" id="<?= $attr.'_'.$i ?>" readonly="readonly" value="<?= htmlspecialchars($value) ?>" />
        </div>
        <?php endforeach; ?>

    </div>



</div>