<h3><?= $model->loaded() 
        ? __($model->object_name().'.form_edit_headline', array(':preview' => $model->preview()))
        : __($model->object_name().'.form_new_headline');?></h3>
<a name="comment_headline"></a>
<div class="comment_new_form_content">
<?= $form->RenderItem('text'); ?>
<?= $form->RenderItem('comment_attachement'); ?>
<?= $form->RenderItem('notifications'); ?>
</div>