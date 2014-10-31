<ul class="nav navbar-nav pull-right toolbar">
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="hidden-xs"><?= Auth::instance()->get_user()->name(); ?> <i class="fa fa-user"></i></span></a>
        <ul class="dropdown-menu userinfo arrow" role="menu">
            <li class="userlinks">
                <ul class="dropdown-menu">
                    <li><a href="<?= appurl::user_profile();?>"><?= __('general.my_profile');?> <i class="pull-right fa fa-fw fa-pencil"></i></a></li>
                    <li class="divider"></li>
                    <li><a href="<?= appurl::logout_action();?>" class="text-right"><?= __('general.logout');?></a></li>
                </ul>
            </li>
        </ul>
    </li>
</ul>