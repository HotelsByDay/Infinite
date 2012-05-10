<div  class="appformitemnnsimpleselect" name="<?= $attr ?>_item" id="<?= $uid;?>">

    <label for="<?= $attr ?>_id"><?= $label ?></label>


    <style type="text/css">
        .items_groups_column {
            width: 300px;
            float: left;
        }
    </style>

<?php
// Sem si ulozime kolik skupin ma byt ve sloupci a postupne budeme dekrementovat
// Az dosahneme nuly, tak zacneme vypisovat dalsi sloupec a nacteme si dalsi info o poctu
// Tahle logika by mozna mela byt v classe prvku, ale pak by tu musel byt cyklus navic
$col_opened = false;
$col_size = 0;
// V cyklu vypiseme
foreach ($items as $group_id => $codebook):
    // Logiga pro rozdeleni do sloupcu
    if ($col_size <= 0) {
        if ($col_opened) {
            // Nejde o prvni sloupec, takze zavreme predchozi
            echo '</div>';
            $col_opened = false;
        }

        // Timhle vynechame nuly a zaporna cisla
        while ($col_size <= 0 and count($columns_sizes)) $col_size = (int)array_shift($columns_sizes);

        // Pokud dalsi sloupec neni prazdny, otevreme ho
        if ($col_size > 0) {
            echo '<div class="items_groups_column">';
            $col_opened = true;
        }
        else {
            // Jinak ukoncime vypis - dalsi skupina uz neni
            break;
        }
    }
    // Za chvili dojde k vypisu skupiny, takze snizime pocet skupin ktere jeste maji ve sloupci byt
    $col_size--;
?>
    
    <?php // Vypiseme vlastni skupinu checkboxu ?>
    <div class="items_group">
    <label for="<?= $attr.'_'.'group_'.$group_id ?>"><?= arr::get($groups, $group_id) ?></label>
    <?php foreach ($codebook as $key => $val): ?>
    <div class="item">
      <label for="<?= $attr.$key ?>"><?= $val ?></label>
      <input type="checkbox" id="<?= $attr.$key ?>" disabled="disabled" value="<?= $key ?>" <?= (in_array($key, (array)$selected)) ? 'checked="checked"': ' ' ?>/>
    </div>
    <?php endforeach; ?>
    </div>


<?php endforeach;
    // Pokud zustala otevrena posledni skupina, coz nastane vetsinou, tak ji uzavreme
    if ($col_opened) {
        echo '</div>';
    }
?>

</div>