<?= $success_message;?>

<?php if ($allow_undo): ?>
<a class="undo" href="#"><?= __('object.item_action_take_back');?></a>
<?php endif ?>

<?php if (count($action_errors) != 0): ?>

    <?= $error_message;?>

    <ul>
        <?php foreach ($action_errors as $id => $item):

            list($preview, $error_message) = $item;

        ?>
        <li><?= $preview;?> - <?= $error_message;?></li>

        <?php endforeach ?>
    </ul>

<?php endif ?>