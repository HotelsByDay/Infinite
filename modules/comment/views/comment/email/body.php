<h3><?= $rel_record_preview;?></h3>
<strong><?= $user_preview;?></strong> napsal:<br/>
<?= $message;?><br/>
<br/>
<?php if (count($attachements) != 0): ?>
<strong>Přílohy:</strong><br/>
<?php foreach ($attachements as $attachement):?>
<a href="<?= $attachement->getURL();?>"><?= $attachement->getFileName();?></a>&nbsp;(<?= $attachement->getFileSize();?>)<br/>
<?php endforeach ?>
<?php endif ?>
--------------------------------------------------------------------------------<br/>
<span style="margin-right:20px;"><?= __('comment.to_see_all_coments_click_here', array(':link' => $show_all_coments_link));?></span>
<span style="margin-right:20px;"><?= __('comment.to_stop_receiving_notifications_click_here', array(':link' => $unsign_from_notifications));?></span>
