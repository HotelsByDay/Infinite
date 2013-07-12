<?= $action_result
; ?>

<?php if (isset($banner)): ?>

    <?= $banner
    ; ?>

<? endif ?>

<form method="POST" action="<?= $form_action_link;?>" <?= isset($banner) ? 'style="display:none;"' : ''; ?>
      css="<?= $css;?>" <?= isset($form_attributes) ? $form_attributes : '' ?>>

    <?= $form_view;?>

    <div class="cb hr"></div>
    <div class="form_control_panel_wrapper">
        <div class="form_control_panel form-actions">
            <div class="pull-left">
                <?php foreach (arr::getifset($form_buttons, 'l', array()) as $params): ?>
                <?= form::button($params[0], $params[1], arr::get($params, 2))
                ; ?>
                <?php endforeach ?>
            </div>

            <div class="pull-right">
                <?php foreach (arr::getifset($form_buttons, 'r', array()) as $params): ?>
                <?= form::button($params[0], $params[1], arr::get($params, 2))
                ; ?>
                <?php endforeach ?>
            </div>

            <br class="clearfix"/>
        </div>
    </div>

</form>