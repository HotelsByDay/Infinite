
<div class="view-data container-fluid">


<?php if (isset($total_found) or $top_pager): ?>
    <div class="row">

        <?php if (isset($total_found)): ?>
            <div class="records-found span3">
                <?= __('object.totally_found_items', array(':total_found' => $total_found));?>
            </div>
        <?php endif; ?>

        <?= $top_pager;?>

    </div>
<?php endif; ?>


<?php if (isset($item_action_panel)): ?>
    <div class="row">
        <?= $item_action_panel;?>
    </div>
<?php endif ?>

<?= $data_table;?>

<div class="row">
    <?= $bottom_pager;?>
</div>

</div>