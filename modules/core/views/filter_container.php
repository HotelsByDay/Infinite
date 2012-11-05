
    <h4><?= $headline;?></h4>
   



        <div class="filterstate_header hd" style="display:none;">
            <h2 class="name"></h2>
            <ul class="boxNav">
                <li><a href="#" class="edit_filter"><?= __('object.edit_filter'); ?></a></li>
                <li><a href="#" class="reset_filterstate"><?= __('object.reset_filter'); ?></a></li>
            </ul>
        </div>

<div class="filter" id="main_data_filter">

        <form method="GET" action="<?= $action_link; ?>" class="search form-horizontal" name="filter_container" id="filter_container" onsubmit="return false;">

            <?= $filter_form; ?>
     
          
<div class="row-fluid show-grid">
<div class="span4" style="margin-top:10px;">
                    <button class="submit_filter button btn btn-primary red btn"><?= __('filter.submit_filter'); ?></button>
                    <button href="#" class="reset_filter btn button blue"><?= __('filter.reset_filter_state'); ?></button>

                    <?php if ($user_filters_enabled): ?>
                        <a href="#" class="button_2 save_filter btn"><?= __('filter.save_filter_state');?></a>
                    <?php endif ?>
    
            
            
           
            
            
            
                <div style="display:none;">
                    <button class="button_3 save_filter btn" ><?= __('filter.save_filter_state'); ?></button>
                    <a href="#" class="cancel_edit_filter btn" ><?= __('object.cancel_edit_filter'); ?></a>
                </div>
           
			
  </div> 
  </div>    
        </form>
    
    <?php if ($export_enabled): ?>
        
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



    <div class="info msg" id="table_result_placeholder" style="display:none;">
        <span class="msg2">
        </span>
    </div>


