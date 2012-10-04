<div class="appformitemrelnnselect <?= $css ?>" id="<?= $uid;?>">

    <?php if (!empty($error_message)): ?>
        <span class="validation_error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>

    <label><?= $label;?></label>


    <?php if ($allow_check_all): ?>
        <div class="check_all">
            <button class="button blue check_all"><?= __('relnnselect.check_all'); ?></button>
            <button class="button blue uncheck_all"><?= __('relnnselect.uncheck_all'); ?></button>
        </div>
    <?php endif; ?>


    <div class="items">
        <?php if ($columns_count): ?>
            <div class="column">
        <?php endif; ?>

        <?php
            // Spocteme velikost sloupce - v poctu polozek
            $items_count = count($items);
            if ( ! $columns_count) {
                $columns_count = 1;
            }
            $column_size = (int)($items_count / $columns_count);
            if ($column_size and $items_count % $column_size) {
                $column_size++;
            }
            $item_number = 0;
            foreach ($items as $item):
                // Zjistime jestli je prvek zatrzen
                $checked = in_array($item->pk(), $selected['id']);

                // Pokud je definovan pocet sloupcu a dovrsil se pocet prvku ve sloupci
                if ($columns_count and $item_number >= $column_size) {
                    // Otevreme novy sloupec
                    echo '</div><div class="column">';
                    $item_number = 0;
                }
                $item_number++;
        ?>

        <div class="item">
            <input type="checkbox" <?= $checked ? 'checked="checked"' : '';?> id="item_<?= $attr;?>_<?=$item->pk();?>" value="<?= $item->pk();?>" name="<?= $attr;?>[id][<?= $item->pk() ?>]" />
            <label for="item_<?= $attr;?>_<?=$item->pk();?>" class="check"><?= $item->preview();?></label>

            <?php if ($note): ?>
                <div class="note_outer" <?= $checked ? '' : 'style="display: none;"' ?>>
                    <input type="text" name="<?= $attr ?>[note][<?= $item->pk() ?>]"
                           value="<?= $checked ? arr::get($selected['note'], $item->pk()) : '' ?>"
                           <?= $checked ? '' : 'disabled="disabled"' ?> />
                </div>
            <?php endif; ?>

        </div>
        <?php endforeach ?>

        <?php if ($columns_count): ?>
            </div>
        <?php endif; ?>
    </div>


</div>