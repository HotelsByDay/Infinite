<div class="back_from_overview">
<?php if (isset($return_link) && ! empty($return_link)):?>
<a class="btn btn-small pull-right" href="<?= $return_link;?>"><?= isset($return_link_label) && ! empty($return_link_label) ? $return_link_label : __('object.form_return_link_label');?></a>
<?php endif ?>
</div>

<div class="overview_container" id="<?= $overview_container_id;?>">

    <?= $header; ?>
    <br class="clear"/>

    <div class="view-nav overview_submenu" >
        <?= $submenu; ?>
        <br class="clear"/>
    </div>
    <div class="view-data data overview_subcontent">

    </div>
    <br class="clear"/>

</div>