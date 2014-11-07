<ul class="searchNav nav nav-tabs">
<?php
//To ze tady je volani metody appurl::object_action a tedy logika pro vygenerovani
//konecne URL adresy pro odkazy submenu je spatne - melo by to byt v kontroleru,
//ale vzhledem k tomu ze zatim nevim jake budou prvni potrebne customizace to
//tady nechavam a lepe resit se to bude az bude potreba
?>
<?php foreach ((array)$items as $subcontent_name => $info):

    //odkaz je bude explicitne definovan, nebo se standardne generuje
    $action = isset($info['action'])
                ? $info['action']
                : appurl::object_overview_subcontent($object_name, $subcontent_name, $model->pk());
?>
    <li class="<?= arr::getifset($info, 'css', '');?>">
        <a id="submenu_<?= $subcontent_name;?>"
           class="submenu_item <?=arr::getifset($info, 'default') ? 'default': '';?> "
           href="<?= appurl::object_overview($object_name, $model->pk(), NULL, $subcontent_name);?>"
           action="<?= $action?>"
        >
           <?= $info['label'];?>
        </a>
    </li>
<?php endforeach ?>
</ul>

