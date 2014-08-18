<?php
/** @var $attr string */
/** @var $label string */
/** @var $css string */
/** @var $fast_search boolean */
/** @var $allow_check_all boolean */
/** @var $note boolean */
/** @var $additional_data boolean */
/** @var $column_width integer */
/** @var $min_column_size integer */
/** @var $items ORM[] */
/** @var $selected array */
/** @var $link string */
/** @var $additional_data_label string */
/** @var $preview_format string */
?>
<div class="appformitemcontainer appformitemrelnnselect <?= $css ?>" id="<?= $uid;?>">

    <?php if (!empty($error_message)): ?>
        <span class="validation_error alert alert-error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>

    <label><?= $label;?></label>


    <?php if ($fast_search): ?>
        <div class="fast_search">
            <div class="input-append">
                <input class="input-medium fast_search" type="text">
                <span class="add-on"><i class="icon icon-search"></i></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($allow_check_all): ?>
        <div class="check_all">
            <a class="button blue check_all btn btn-mini "><?= __('relnnselect.check_all'); ?></a>
            <a class="button blue uncheck_all btn btn-mini "><?= __('relnnselect.uncheck_all'); ?></a>
        </div>
    <?php endif; ?>


    <div class="items">
        <?php
        if ( ! $columns_count) {
            $columns_count = 1;
        }
        ?>
        <div class="column" style="width: <?= $column_width ?>px">

        <?php
            // Spocteme velikost sloupce - v poctu polozek
            $items_count = count($items);

            $column_size = max((int)($items_count / $columns_count), $min_column_size);
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
                    echo '</div><div class="column" style="width: '.$column_width.'px">';
                    $item_number = 0;
                }
                $item_number++;
        ?>

        <div class="item">

            <label for="item_<?= $attr;?>_<?=$item->pk();?>" class="check checkbox"><?
                echo $item->preview($preview_format);
                if ($link) { ?>&nbsp;<a href="<?= AppUrl::object_overview($item->object_name(), $item->pk()) ?>" target="_blank"><?= $link ?></a><? }
                ?><input type="checkbox" <?= $checked ? 'checked="checked"' : '';?> id="item_<?= $attr;?>_<?=$item->pk();?>" value="<?= $item->pk();?>" name="<?= $attr;?>[id][<?= $item->pk() ?>]" />
            </label>

            <?php if ($note): ?>
                <div class="note_outer" <?= $checked ? '' : 'style="display: none;"' ?>>
                    <input type="text" name="<?= $attr ?>[note][<?= $item->pk() ?>]"
                           value="<?= $checked ? arr::get($selected['note'], $item->pk()) : '' ?>"
                           <?= $checked ? '' : 'disabled="disabled"' ?> />
                </div>
            <?php endif; ?>

            <?php if ($additional_data): ?>
                <div class="note_outer" <?= $checked ? '' : 'style="display: none;"' ?>>
                    <label><?= $additional_data_label ?></label>
                    <input type="text"
                           name="<?= $attr ?>[<?= $additional_data ?>][<?= $item->pk() ?>]"
                           value="<?= $checked ? arr::get($selected[$additional_data], $item->pk()) : '' ?>"
                        <?= $checked ? '' : 'disabled="disabled"' ?> />
                </div>
            <?php endif ?>

        </div>
        <?php endforeach ?>

        </div>
    </div>


</div>