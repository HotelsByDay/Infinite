<?php if (isset($return_link) && ! empty($return_link)):?>
<a href="<?= $return_link;?>"><?= isset($return_link_label) && ! empty($return_link_label) ? $return_link_label : __('object.form_return_link_label');?></a>
<?php endif ?>

<div id="overview_container">

    <?= $header; ?>
    <br class="clear"/>

    <div class="view-nav" id="overview_submenu">
        <?= $submenu; ?>
        <br class="clear"/>
    </div>
    <div class="view-data data" id="overview_subcontent">

    </div>
    <br class="clear"/>

</div>