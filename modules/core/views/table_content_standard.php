<div class="<?= $controller_name;?> <?= $action_name;?>view">

    <?php if (isset($action_result)): ?>
        <?= $action_result;?>
    <?php endif ?>


    <?= $filter_form; ?>

    <div class="data" id="data_table_container">
        
    </div>

</div><!-- end .box1 .middle_panel-->

<div class="right_panel">
    <?= $user_filter_panel;?>
</div>

<div class="cb"></div>



