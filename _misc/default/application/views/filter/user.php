<fieldset>
    <label for="user_filter-fulltext"><?= __('user.filter_fulltext') ?></label>
    <input type="text" name="fulltext" id="user_filter-fulltext" value="<?= arr::get($defaults, 'fulltext', '') ?>" />
</fieldset>
