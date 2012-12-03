<?= $action_result;?>

<?php if (isset($banner)): ?>

<?= $banner ;?>

<?php endif ?>

<form method="POST" action="<?= $form_action_link;?>" <?= isset($banner) ? 'style="display:none;"' : ''; ?>>

    <?php
    //kvuli editaci potrebuji uchovat ID zaznamu, ktere nemusi by v URL
    //napriklady pri editace uzivalteskych filtru nebyva v URL a je potreba ho
    //takto prenaset mezi jednotlivymi zobrazenimi formulare
    if ($model->loaded()): ?>
    <input type="hidden" name="_id" value="<?= $model->pk();?>"/>
    <?php endif ?>
            
    <?= $form_view ?>

    <div class="form_control_panel_wrapper">
        <div class="form_control_panel form-actions">
            <div class="form_control_panel_content">
                <div class="cb hr"></div>
                <div class="pull-left">
                    <?php foreach (arr::getifset($form_buttons, 'l', array()) as $params): ?>
                    <?= form::button($params[0], $params[1], arr::get($params, 2));?>
                    <?php endforeach ?>
                </div>

                <div class="pull-right">
                    <?php foreach (arr::getifset($form_buttons, 'r', array()) as $params): ?>
                    <?= form::button($params[0], $params[1], arr::get($params, 2));?>
                    <?php endforeach ?>
                </div>
                <br class="clearfix"/>
            </div>

        </div>
    </div>

</form>
