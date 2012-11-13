<div class="appformitemfile <?= $css ?>" name="<?= $attr;?>_item" id="<?= $uid;?>">

    <span class="label"><?=  $label;?></span>

    <?php if ($table_header): ?>
        <table class="list table">

            <?= $table_header;?>

            <?php foreach ($files as $file): ?>
            <?=(string)$file;?>
            <?php endforeach ?>

        </table>
        <div class="clearfix cb"></div>
    <?php else: ?>
        <div class="list">
            <?php foreach ($files as $file): ?>
            <?=(string)$file;?>
            <?php endforeach ?>
           <div class="clearfix cb"></div>
        </div>
        <div class="clearfix cb"></div>
    <?php endif ?>
</div>