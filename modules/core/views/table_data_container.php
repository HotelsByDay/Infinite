
<div class="view-data row-fluid">


<div class="line-data row-fluid">
    <div class="records-found span3">
        <?= __('object.totally_found_items', array(':total_found' => $total_found));?>
    </div>
    
    <?= $top_pager;?>

    <br class="clear">
</div>

<?php if (isset($item_action_panel)): ?>
    <div class="line-data">
        <?= $item_action_panel;?>
        <br class="clear">
    </div>
<?php endif ?>

<?= $data_table;?>

<div class="line-data">
    <?= $bottom_pager;?>
    <br class="clear">
</div>

</div>