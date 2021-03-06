<!doctype html>
<html lang="en" class="no-js">
<head>
  <meta charset="utf-8">

  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title></title>
  <meta name="description" content="">
  <meta name="author" content="">

  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">

  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">

  <link rel="stylesheet" href="<?= url::base();?>css/style.css?v=1">

</head>


<!--[if lt IE 7 ]> <body class="ie6" id="dark"> <![endif]-->
<!--[if IE 7 ]>    <body class="ie7" id="dark"> <![endif]-->
<!--[if IE 8 ]>    <body class="ie8" id="dark"> <![endif]-->
<!--[if IE 9 ]>    <body class="ie9"  id="dark"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <body id="dark" onload="document.forms[0].username.focus();" <!--<![endif]-->

<?= View::factory('noscript');?>

<div class="container">
    <div id="login">
	<div class="logo" style="display:none;"><img src="images/logo.jpg"></div>
	<form method="post" action="<?= appurl::login_action();?>">

            <?php if (isset($err_msg)): ?>
            <div class="alert alert-error">
                <?php echo $err_msg;?>
            </div>
            <?php endif ?>

            <fieldset><label for="username"><?= __('login_page.login');?></label><input id="username" type="text" name="username" value="" tabindex="1"/></fieldset>
            <fieldset><label for="password"><?= __('login_page.password');?></label><input id="password" type="password" name="password" value="" tabindex="2"/></fieldset>

        <label class="checkbox">
            <input <?= isset($remember) && (bool)$remember ? 'checked="checked"' : '';?> type="checkbox" name="remember" value="1"  />
            <?= __('login_page.remember');?>
        </label>

        <input type="submit" class="btn btn-primary" value="<?= __('login_page.login_action');?>"/>


    </form>

        <?php if (AppConfig::instance()->get('reset_password_option', 'system')): ?>
        <a href="<?= appurl::object_action('resetpassword', 'index');?>">Lost my password</a>
        <?php endif ?>

    </div>
</div>

</body>
</html>