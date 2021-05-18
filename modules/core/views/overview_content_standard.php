<div class="back_from_overview">
    <?php if (isset($return_link) && ! empty($return_link)):?>
        <a class="btn btn-small pull-right" href="<?= $return_link;?>"><i class="icon-chevron-left"></i><?= isset($return_link_label) && ! empty($return_link_label) ? $return_link_label : __('object.form_return_link_label');?></a>
    <?php endif ?>
</div>

<div class="overview_container tab-container tab-sky tab-normal" id="<?= $overview_container_id;?>">

<div class="overview_header">
        <?= $header ?>
</div>
<!-- overview_header -->

    <div class="view-data tab-content data overview_subcontent">

    </div>
    <br class="clear"/>

</div>