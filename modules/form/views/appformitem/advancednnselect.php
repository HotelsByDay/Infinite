
<div class="appformitemadvancednnselect <?= $css ?>" id="<?= $uid;?>">


    <?php if (!empty($error_message) && is_string($error_message)): ?>
        <span class="validation_error alert alert-error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>

    <label for="<?= $uid ?>_autocomplete"><?= $label ?></label>
    <div class="autocomplete clearfix form-inline">
        <input type="text" id="<?= $uid ?>_autocomplete" name="<?= $attr ?>[autocomplete]" value="" />

        <?php if (isset($new) && $new): ?>
            <a href="#" class="add_new btn btn-primary"><?= $new_label;?></a>
        <?php endif ?>
    </div>
    

    <div class="items clearfix">
    <?php

        //predikat, ktery rika ze aktualne neni vybrana ani jedna polozka
        $selection_is_empty = empty($selected);

        foreach ($items as $item):

        if ($show_items == 0)
        {
            $main = FALSE;
        }
        else
        {
           // U prvku musime poznamenat zda je hlavni nebo ne - zda ma po odskrtnuti zustat ve strance
            $main = ($item->sequence <= $show_items) ? '1' : '0';
        }

        //predikat, ktery rika ze aktualni polozka je vybrana
        $active = isset($selected[$item->pk()]);
    ?>
    
    <div class="item item_<?= $item->pk() ?> <?php if ( ! $selection_is_empty) { echo $active ? 'active' : 'inactive';}?>">
        <label class="checkbox">
            <input class="checkbox" type="checkbox" <?= $active ? 'checked="checked"' : '';?> value="<?= $item->pk(); ?>" name="<?= $attr ?>[selected][]" main="<?= $main ?>" />
            <?= $item->preview(); ?>
        </label>
       
        <?php 
            if ($form) {
                // Zde mame v $item instance zaznamu z codebooku
                // ve formu jsou v $item instance zaznamu z relacni tabulky
                echo View::factory($form)
                        ->set('item', arr::get($selected, $item->pk(), false))
                        ->set('attr', $attr)
                        // Pokud je pro danou vazbu chybova hlaska, predame ji do formulare, at si ji zobrazi jak chce
                        ->set('error_message', arr::get((array)$error_message, $item->pk(), false))
                        ->set('id', $item->pk());
            }
        ?>
    </div>
        <?php endforeach ?>
    </div><!-- items -->

    <div class="clearfix"></div>
    
    <?php
    // Tohle je tady definovane dvakrat - nevim jestli ma cenu pro to delat dalsi sablonu.?
    ?>
    <div class="item item_0" style="display: none;">
        
        <label for="<?= $uid ?>_" class="checkbox">
            <input type="checkbox" id="<?= $uid ?>_" value="" name="<?= $attr ?>[selected][]" main="0" />
        </label>
    <?php 
    // Prazdny formular - pro dynamicke vytvareni novych prvku pomoci jQuery clone
    if ($form) {
        echo View::factory($form)
                   ->set('item', false)
                   ->set('attr', $attr)
                // 0 je dulezita - hleda ji jQuery plugin
                   ->set('id', '0');
    }
    ?>
    </div>
</div>