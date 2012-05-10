<div class="dc1 clearfix">
    <label for="agenda_filter-fulltext"><?= __('agenda.filter_fulltext');?></label>
    <input type="text" id="agenda_filter-fulltext" name="<?= Filter_Base::FULLTEXT_QUERY_KEY;?>" value="<?= htmlspecialchars(arr::get($defaults, Filter_Base::FULLTEXT_QUERY_KEY, ''));?>"/>
    <button class="submit_filter"><?= __('filter.submit_filter'); ?></button>
</div>

<div class="dc1 clearfix">
    <a href="#" id="agenda_filter-prev_week" class="prev_week">&lt;</a>
    <a href="#" id="agenda_filter-this_week" class="this_week"><?= __('agenda.filter_this_week');?></a>
    <input type="hidden" id="agenda_filter-w" name="w" value="<?= arr::get($defaults, 'w');?>"/>
    <a href="#" id="agenda_filter-next_week" class="next_week">&gt;</a>
</div>

