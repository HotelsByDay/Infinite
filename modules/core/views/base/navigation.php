<?php
//Pripravim si promenne, ktere budu dale pouzivat
$user = Auth::instance()->get_user();


 echo Menu::instance()->getMenu();
 
?>
<?php if (false): ?>

<div class="navWrap">
    <ul id="menu">
        <li class="imgTab"><a href="<?= appurl::homepage();?>"><img src="<?= url::base();?>css/images/home.png"></a></li>

        <li class="<?=($active_object == 'advert')?'onTab':'';?>">
            <a href="<?=appurl::object_table('advert');?>"><?=__('advert.menu_name');?></a>
        </li>

        <li class="<?=($active_object == 'interest')?'onTab':'';?>">
            <a href="<?=appurl::object_table('interest');?>"><?=__('interest.menu_name');?></a>
        </li>

        <li class="<?=($active_object == 'demand')?'onTab':'';?>">
            <a href="<?=appurl::object_table('demand');?>"><?=__('demand.menu_name');?></a>
        </li>

        <li class="<?=($active_object == 'client')?'onTab':'';?>">
            <a href="<?=appurl::object_table('client');?>"><?=__('client.menu_name');?></a>
        </li>
        
        <li class="<?=($active_object == 'agenda')?'onTab':'';?>">
            <a href="<?=appurl::object_table('agenda');?>"><?=__('agenda.menu_name');?></a>
        </li>

        <li class="<?=($active_object == 'seller')?'onTab':'';?>">
            <a href="<?=appurl::object_table('seller');?>"><?=__('seller.menu_name');?></a>
        </li>

        <li class="menu_right imgTab"><a href="<?= appurl::homepage();?>"><img src="<?= url::base();?>css/images/settings.png"></a>

            <div class="dropdown_1column">
                <div class="col_1">
                    <ul class="">

                        <li><a href="<?= appurl::object_table('serie');?>">Číselné řady</a></li>
                        <li>----------</li>
                        <li><a href="<?= appurl::object_table('user');?>">Uživatelé</a></li>
                    </ul>
                </div>
            </div>
        </li>

        <li class="menu_right <?=($active_object == 'logaction')?'onTab':'';?>">
            <a href="<?=appurl::object_table('logaction');?>"><?=__('logaction.menu_name');?></a>
        </li>

        <li class="menu_right <?=($active_object == 'export')?'onTab':'';?>">
            <a href="<?=appurl::object_table('export');?>"><?=__('export.menu_name');?></a>
        </li>
    </ul>
</div><!-- End navWrap -->
<?php if (in_array($active_object, array('advert', 'interest', 'demand', 'client', 'agenda', 'seller', 'export', 'logaction'))): ?>
<div class="subNavWrap">

    <ul id="subMenu">

        <?php if (Auth::instance()->get_user()->HasPermission($active_object, 'action_new')): ?>
        <li class="caption">Přidat</li>
        <ul id="subAction">
            <?php foreach ((array)kohana::config($active_object.'.create_new_button') as $lang_key => $href): ?>
                <li>
                    <a href="<?= $href;?>" style="display:inline;"><?= __($lang_key);?></a>
                </li>
            <?php endforeach ?>
        </ul>
        <?php endif ?>
    </ul>

</div><!-- End subNavWrap -->
<?php endif ?>

<?php endif; ?>
