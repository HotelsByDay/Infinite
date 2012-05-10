<style type="text/css">
    .appformitemadvancednnselect .item {
        float: left;
        width: 200px;
        text-align: center;
        border: 1px solid #71bdf0;
        padding: 10px;
        margin: 10px;
    }
    .appformitemadvancednnselect .item .form {
        clear: both;
    }
</style>

<div class="appformitemadvancednnselect <?= $css ?>" id="<?= $uid;?>">
        
    <div class="items clear">
    <?php
        foreach ($items as $item):

            // U prvku musime poznamenat zda je hlavni nebo ne - zda ma po odskrtnuti zustat ve strance
            if ($show_items == 0)
            {
                $main = FALSE;
            }
            else
            {
                // U prvku musime poznamenat zda je hlavni nebo ne - zda ma po odskrtnuti zustat ve strance
                $main = ($item->sequence <= $show_items) ? '1' : '0';
            }
    ?>
    
    <div class="item item_<?= $item->pk() ?>">
        <input type="checkbox" <?= isset($selected[$item->pk()]) ? 'checked="checked"' : '';?> disabled="disabled" id="<?= $uid ?>_<?=$item->pk() ?>" value="<?= $item->pk(); ?>" name="<?= $attr ?>[selected][]" main="<?= $main ?>" />
        
        <label for="<?= $uid ?>_<?=$item->pk() ?>"><?= $item->preview(); ?></label>
       
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
        

    </div>
