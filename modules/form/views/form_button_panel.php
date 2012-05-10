<?php
$save_button_ptitle = ___('form_action_button.'.$config_group_name.'.update_ptitle', 'form_action_button.update_ptitle');
?>

<?php
if ( ! $form->is_readonly() &&
        (   ($model->loaded() && Auth::instance()->get_user()->HasPermission($model->object_name(), 'db_update'))
         || ( ! $model->loaded() && Auth::instance()->get_user()->HasPermission($model->object_name(), 'db_insert')))):
?>
<div class="fl">
    <button ptitle="<?= $save_button_ptitle;?>" class="form_button button_1" value="save" name="_a">Uložit</button>
</div>
<?php endif ?>

<div class="fr">
    <button class="form_button_close button_2" name="close">Zavřít</button>
</div>