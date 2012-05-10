<table>
  <tr>
    <td>
      <label for="logaction_filter-fulltext"><?= __('logaction.filter_fulltext') ?></label>
    </td>
    <td>
      <input type="text" name="<?= Filter_Base::FULLTEXT_QUERY_KEY; ?>" id="logaction_filter-fulltext" value="<?= arr::get($defaults, Filter_Base::FULLTEXT_QUERY_KEY); ?>"/>
    </td>

    <td>
      <label for="logaction_filter-user"><?= __('logaction.filter_userid') ?></label>
    </td>
    <td>
      <input type="text" name="user" id="logaction_filter-user" value="<?= arr::get($defaults, 'user', '') ?>" />
      <input type="hidden" name="userid" id="logaction_filter-userid" value="<?= arr::get($defaults, 'userid', '') ?>" />
    </td>

  </tr>
  <tr>
    <td>
      <label for="logaction_filter-created_from"><?= __('logaction_filter.filter_created_from') ?></label>
    </td>
    <td>
      <input type="text" class="jq-datepicker w75px" watermark="<?= __('object.from');?>" name="created_from" id="logaction_filter-created_from" value="<?= arr::get($defaults, 'created_from', '') ?>" />
      <label for="logaction_filter-created_to" class="separator">-</label>
      <input type="text" class="jq-datepicker w75px" watermark="<?= __('object.to');?>" name="created_to" id="logaction_filter-created_to" value="<?= arr::get($defaults, 'created_to', '') ?>" />
    </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
