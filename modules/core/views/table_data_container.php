
<div class="view-data">


<div class="line-data">
    <div class="records-found">
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