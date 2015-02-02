<div class="btn-group">
  <a class="btn btn-danger" data-toggle="dropdown" href="#"><i class="icon-user icon-white"></i><?= Auth::instance()->get_user()->name(); ?></a>
  <a class="btn btn-danger dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
  <ul class="dropdown-menu">
    <li><a href="<?= appurl::user_profile();?>"><i class="icon-user"></i><?= __('general.my_profile');?></a></li>
    <li><a href="<?= appurl::logout_action();?>"><i class="icon-off"></i><?= __('general.logout');?></a></li>
  </ul>
</div>

<div class="cb"></div>

<?= isset($anchor_1) ? $anchor_1 : '';?>