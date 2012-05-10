<?php

$user_id = Auth::instance()->get_user()->pk();
$relid   = $model->pk();
$reltype = $model->reltype();

//celkovy pocet komentaru k danemu zaznamu
$coment_count = ORM::factory('comment')->getNumberOfAllComments($relid, $reltype);
//pripravim si pocet neprectenych komentaru pro aktualne prihlaseneho uzivatele
$unread_comment_count = ORM::factory('comment')->getNumberOfUnreadComments($user_id, $relid, $reltype);

?>

<div class="comment_widget <?= $unread_comment_count > 0 ? 'unread' : '';?>" title="Komentáře k nabídce <?= $model->preview();?>" onclick="$(document).commentWidgetOpen(<?= $reltype;?>, <?= $relid;?>, $(this));$(this).removeClass('unread');">
    <span class="all_coment_count"><?= $coment_count;?></span> / <span class="unread_comment_count"><?= $unread_comment_count;?></span>
</div>