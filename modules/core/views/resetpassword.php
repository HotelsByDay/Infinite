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
<!--[if (gt IE 9)|!(IE)]><!--> <body id="dark" onload="document.forms[0].email.focus();" <!--<![endif]-->

<?= View::factory('noscript');?>

<div class="container">
    <div id="login">
	<div class="logo" style="display:none;"><img src="images/logo.jpg"></div>

        <?php if (isset($processed)): ?>

        <h2><?= __('resetpassword.processed');?></h2>

        <p>
            <a href="<?= appurl::login_page();?>"><?= __('resetpassword.goback_to_login');?></a>
        </p>
        
        <?php else: ?>

        <p><?= __('resetpasswod.help');?></p>

	<form method="post" action="<?= appurl::object_action('resetpassword', 'index');?>">

            <?php if (isset($validation_error)): ?>
            <div class="alert alert-error">
                <?php echo $validation_error;?>
            </div>
            <?php endif ?>

            <fieldset><label for="email"><?= __('resetpassword_page.email');?></label><input id="email" type="text" name="email" value="" tabindex="1"/></fieldset>
            <input type="submit" class="btn btn-primary" value="<?= __('resetpassword_page.reset_action');?>"/>

	</form>
        <?php endif ?>
    </div>
</div>

</body>
</html>