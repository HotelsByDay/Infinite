<div class="appformitemsubcategoryselect <?= $css ?>" id="<?= $uid;?>">

    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error alert alert-error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>


    <div class="category">
    <label><?= $label; ?></label>
    <?= form::select($attr.'[category]', $values, $value); ?>
    </div>

    <label><?= $sub_label; ?></label>
    <div class="items">
        <div class="column">
            <?php
            // Spocteme velikost sloupce - v poctu polozek
            $items_count = count($items);
            if ( ! $columns_count) {
                $columns_count = 1;
            }
            $column_size = (int)($items_count / $columns_count);
            if ($column_size && $items_count % $column_size) {
                $column_size++;
            }
            $item_number = 0;
            foreach ($items as $item):
                // Zjistime jestli je prvek zatrzen
                $checked = in_array($item->pk(), $selected['id']);
                if ($item_number >= $column_size) {
                    echo '</div><div class="column">';
                    $item_number = 0;
                }
                $item_number++;
            ?>
            <div class="item">

                <label for="item_<?= $attr;?>_<?=$item->pk();?>" class="check checkbox"><?= $item->preview();?><input type="checkbox" <?= $checked ? 'checked="checked"' : '';?> id="item_<?= $attr;?>_<?=$item->pk();?>" value="<?= $item->pk();?>" name="<?= $attr;?>[id][<?= $item->pk() ?>]" /></label>
            </div>
            <?php endforeach ?>
        </div>
    </div>

    <div class="clear"></div>

</div>