<?= $action_result; ?>

<?php if (isset($banner)): ?>

    <?= $banner; ?>

<?php endif ?>

<form method="POST" action="<?= $form_action_link;?>" <?= isset($banner) ? 'style="display:none;"' : ''; ?>
      css="<?= $css;?>">

    <?= $form_view;?>

    <?php if ($form->actionPanelEnabled()): ?>
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
    <?php endif; ?>

</form>