<span class="tooltip" onmouseover="$.showTipHelp($(this), <?= $helpid;?>);">?
    <?php if ( ! (isset($ajax))): ?>
    <div class="text" style="display:none"><?= __('tip_help.topic_'.$helpid);?></div>
    <?php endif ?>
</span>