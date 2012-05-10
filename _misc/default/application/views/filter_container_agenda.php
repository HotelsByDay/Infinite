<div id="main_data_filter" class="clearfix">

        <div class="filterstate_header hd" style="display:none;">
            <h2 class="name"></h2>
            <ul class="boxNav">
                <li><a href="#" class="edit_filter"><?= __('object.edit_filter'); ?></a></li>
                <li><a href="#" class="reset_filterstate"><?= __('object.reset_filter'); ?></a></li>
            </ul>
        </div>

    <div class="dc32">
        <form method="GET" action="<?= $action_link; ?>" class="search" name="filter_container" id="filter_container" >

            <div class="advanced_filter">
                <?= $filter_form; ?>
            </div>

        </form>

    </div><!-- end .dc32 -->

    <?php if ($user_filters_enabled): ?>
        <div class="dc3 last filterSaved">

            <h3>Uložené filtry</h3>
            
            <div class="saved_filters">
                <?php foreach ($saved_filters as $saved_filter): ?>
                    <?= $saved_filter;?>
                <?php endforeach ?>
            </div>
        </div>
    <?php endif ?>
    
    <?php if ($export_enabled): ?>
        <br/>
        <br/>
        Exportovat
        <select class="export_control">
            <option value="dummy">jako...</option>
            <option value="1">Sestava A</option>
            <option value="2">Sestava B</option>
            <?php if ($new_export_enabled):?>
            <option value="new">nova sestava</option>
            <?php endif ?>
        </select>
    <?php endif ?>

</div>

