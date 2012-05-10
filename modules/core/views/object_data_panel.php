<?php
//jedinecny identifikator pro div na ktery se bude plugin objectDataPanel poustet
$uid = 'o'.mt_rand();
?>

<div id="<?= $uid;?>" <?= isset($css_class) ? 'class="'.$css_class.'"' : '';?> >
</div>

<script type="text/javascript">
$(document).ready(function(){
    $("#<?= $uid;?>").objectDataPanel(<?= text::json_encode($conf);?>);
});
</script>