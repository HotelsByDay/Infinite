<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" class="no-js">
<head>

    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta http-equiv="Cache-Control" content="no-store"/>

    <meta http-equiv="Pragma" content="no-cache"/>
    <meta http-equiv="Cache-Control" content="no-cache"/>

    <title><?= AppConfig::instance()->get('system_title', 'application');?></title>

    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">

    <link rel="stylesheet" media="handheld" href="<?= url::base();?>css/handheld.css?v=1"/>
    <link href="<?=url::base();?>css/jquery-ui-1.8.7.custom.css" rel="stylesheet" type="text/css" ></link>
    <link rel="stylesheet" href="<?= url::base();?>css/style.css?v=1"/>
</head>

<!--[if lt IE 7 ]> <body class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <body class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <body class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <body class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <body onunload=";" beforeunload=";"> <!--<![endif]-->

<?= View::factory('noscript');?>


<div id="content">
    <div id="content-in">

        <?=$content;?>
    </div><!-- end .content-in -->
</div><!-- end .content -->



<div id="unexpected_error_message" style="display:none;">
    <?= View::factory('/error/unexpected_error_message');?>
</div>

<?=$js_files_include;?>


</body>
</html>