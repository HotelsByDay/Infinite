<?php if ( ! empty($tooltip)):?>
    <?php
        if ( ! isset($tooltip_position_my))
        {
            $tooltip_position_my = 'left top';
        }

        if ( ! isset($tooltip_position_at))
        {
            $tooltip_position_at = 'right center';
        }
    ?>
<span class="tooltip" position_my="<?= $tooltip_position_my;?>" position_at="<?= $tooltip_position_at;?>">?<div class="content" style="display:none"><?= $tooltip;?></div></span>
<?php endif ?>