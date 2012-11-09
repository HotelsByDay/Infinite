<?php if (isset($return_link) && ! empty($return_link)):?>
<a class="btn btn-small pull-right" href="<?= $return_link;?>"><?= isset($return_link_label) && ! empty($return_link_label) ? $return_link_label : __('object.form_return_link_label');?></a>
<?php endif ?>

<div id="edit_content" class="edit_content_<?= $controller_name;?>">
    <div class="content-title">
        <h3><?= $headline;?></h3>
        <br class="clear"/>
    </div>
  <div class="form_content form-pg">
    <?= $form;?>
  </div>
</div>