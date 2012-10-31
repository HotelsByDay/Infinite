<!doctype html>
<html lang="en" class="no-js">
    <head>
        <title><?= AppConfig::instance()->get('system_header', 'application'); ?></title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">

        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    </head>
    <body style="background:white;font-family: Tahoma, 'Lucida Grande CE', lucida, sans-serif; font-size:13px; ">

        <div class="container" id="main_content" <?= Kohana::$environment != Kohana::PRODUCTION && isset($type) ? 'style="display:none;"' : '';?>>
            <div id="message_block" style="width: 630px; margin: 100px auto; background: white; padding: 30px;text-align:center">
                <div style="text-align:center;">
                    <h1 style="font-size:18px;margin-bottom:20px;"><?= isset($user_title) ? $user_title : __('error.unexpected_error.title'); ?></h1>
                    <p style="font-size:14px;"><?= isset($user_message) ? $user_message : __('error.unexpected_error.message'); ?></p>
                </div>
            </div>
        </div>
    </body>
</html>