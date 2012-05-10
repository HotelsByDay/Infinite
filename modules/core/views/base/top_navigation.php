<strong><?= Auth::instance()->get_user()->name();?></strong> |
<a href="<?= appurl::user_profile();?>"><?= __('general.my_profile');?></a> |
<a href="<?= appurl::logout_action();?>"><?= __('general.logout');?></a>

<div class="cb"></div>

<?= isset($anchor_1) ? $anchor_1 : '';?>