<div class="appformitemlangtext appformitemcontainer <?= $css?> " id="<?= $uid;?>">

    <?php if (!empty($error_message)): ?>
        <span class="validation_error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>

        
        
        <style type="text/css">
            /* pro otestovani funkcnosti jquery pluginy */
            div.langitem.warning select {
                border: 1px solid red;
                background-color: pink;
            }
            
            .langitem {
                width: 600px;
                margin: 4px;
            }
            /* tady je to tak dlouhe aby to melo vysi prioritu, protoze se to s necim hada */
            body form .langitems .langitem label {
                float: left;
                width: 300px;
            }
            .langitem select {
                float: right;
            }
            body form .langitem textarea {
                clear: both;
                width: 590px;
                /*height: 100px;*/
            }
        </style>

    <div class="langitem_source" style="display:none;">
        <div class="langitem">
            <select _name="<?= $attr;?>[locales][]">
                <?php foreach ($locales as $locale_key => $locale_name): ?>
                <option value="<?= $locale_key;?>" placeholder="<?= arr::get($placeholders, $locale_key, '');?>"><?= $locale_name;?></option>
                <?php endforeach ?>
            </select>
            <br/>
            <div class="textarea_container">
                <textarea _name="<?= $attr;?>[translates][]"></textarea>
            </div>
        </div>
    </div>

<div class="langitems">
    
    <?php $i=0; foreach ($translates as $locale => $value): $i++; ?>
            <div class="langitem">

                <?php if ($label != ''): ?>
                
                    <?php if ($i > 1) $final_label = $label.' '.$i; else $final_label = $label; ?>

                    <label for="<?= $attr.'_'.$i ?>"><?= $final_label ?></label>
                
                <?php endif ?>

                <select name="<?= $attr;?>[locales][]">
                    <?php foreach ($locales as $locale_key => $locale_name): ?>
                    <option <?= $locale_key == $locale ? 'selected="selected"' : '';?> value="<?= $locale_key;?>" placeholder="<?= arr::get($placeholders, $locale_key, '');?>"><?= $locale_name;?></option>
                    <?php endforeach ?>
                </select>
                <br/>
                <div class="textarea_container">
                    <textarea class="langinput" id="<?= $attr.'_'.$i ?>" placeholder="<?= arr::get($placeholders, $locale, '');?>" name="<?= $attr ?>[translates][]" <?= isset($min_length) ? "minlength=\"$min_length\"" : '' ?> <?= isset($max_length) ? "maxlength=\"$max_length\"" : '' ?>><?= $value ?></textarea>
                </div>
            </div>
    <?php endforeach; ?>
    
</div>


        <?php if ($mode != AppForm::LANG_SLAVE): ?>
            <div class="langadd">
                <span style="display: none;"><?= __('lang.all_translations_added'); ?></span>
                <a href="javascript: ;"><?= __('lang.add_translation'); ?></a>
            </div>
        <?php endif; ?>
        
</div>