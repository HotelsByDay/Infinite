<div class="form_action_result_failed info err">
<span>
    <?= $user_message;?>
    <?php if (isset($error_messages) && ! empty($error_messages)): ?>
    <ul>
        <?php foreach ($error_messages as $attr => $error_message): ?>
        <li><?= $error_message;?></li>
        <?php endforeach ?>
    </ul>
    <?php endif ?>
</span>
</div>
