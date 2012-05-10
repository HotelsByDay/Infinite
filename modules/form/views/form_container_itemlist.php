<?= $action_result;?>

<?php if (isset($banner)): ?>

<?= $banner ;?>

<? endif ?>

<div class="itemlist_form" <?= isset($banner) ? 'style="display:none;"' : ''; ?>>
<?= $form_view;?>
</div>

