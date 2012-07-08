<div class="appformitemlangstring appformitemcontainer <?= $css?> " id="<?= $uid;?>">

    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>

        <style type="text/css">
            /* pro otestovani funkcnosti jquery pluginy */
            div.langitem.warning select {
                border: 1px solid red;
                background-color: pink;
            }
        </style>
        
        
<div class="langitems">
    
    <?php $i=0; foreach ($translates as $locale => $value): $i++; ?>
            <div class="langitem">    

                <?php if ($label != ''): ?>

                    <?php if ($i > 1) $final_label = $label.' '.$i; else $final_label = $label; ?>

                    <label for="<?= $attr.'_'.$i ?>"><?= $final_label ?></label>

                <?php endif ?>

                <input class="langinput" type="text" id="<?= $attr.'_'.$i ?>" name="<?= $attr ?>[translates][]" value="<?= $value ?>" <?= isset($min_length) ? "minlength=\"$min_length\"" : '' ?> <?= isset($max_length) ? "maxlength=\"$max_length\"" : '' ?> />
                <?= form::select($attr.'[locales][]', $locales, $locale) ?>
            </div>
    <?php endforeach; ?>
    
</div>
      
        
        <div class="langadd">
        <span style="display: none;"><?= __('lang.all_translations_added'); ?></span>
        <a href="javascript: ;"><?= __('lang.add_translation'); ?></a>
        </div>
        
        
</div>