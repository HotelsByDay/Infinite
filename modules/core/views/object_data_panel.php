<?php
//jedinecny identifikator pro div na ktery se bude plugin objectDataPanel poustet
$uid = 'o'.mt_rand();
?>

<div id="<?= $uid;?>" <?= isset($css_class) ? 'class="'.$css_class.'"' : '';?> >
</div>

<script type="text/javascript">
    var init = function()
    {
        $("#<?= $uid;?>").objectDataPanel(<?= text::json_encode($conf);?>);
    }

    if (typeof $ != 'undefined')
    {
        $(document).ready(init);
    } else {
        window.onload = init;
    }

</script>