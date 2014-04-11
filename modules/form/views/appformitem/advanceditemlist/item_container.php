<?php
//pro vazbu mezi labelem a inputem pro oznaceni zaznamu k odstraneni
//se generuje nahodna hodnota - prefix 'b'(button) je jen pro to aby se jednalo
//o vice nahodnou hodnotu, protoze se generuje na formulari vice nahodnych hodnot
//s jinymi prefixy
$randid = 'b'.mt_rand();
?>

<div id="container<?= $randid;?>">

    <?= $form->Render(); ?>

    <?php if ( ! $form->is_readonly()): ?>
        <a href="<?= $model->loaded() ? appurl::object_delete($model->object_name(), $model->pk()) : '';?>" class="delete btn btn-danger btn-mini" item_id="<?= $model->pk();?>"><?= __('appformitemadvancedselect.delete_label');?></a>
    <?php endif ?>
</div>