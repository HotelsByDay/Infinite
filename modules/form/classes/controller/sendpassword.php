<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Controller for AppFormItem_SendPassword
 */
class Controller_SendPassword extends Controller_Layout {


   public function action_reset($userid)
   {
       $result = 1; $error = '';
       $user = ORM::factory('user', $userid);
       if ( ! $user->loaded()) $result = 0;

       if ($result) {
           try {
               $plaintext_password = Text::random();
               $user->password = $plaintext_password;
               $user->save();

               // Send mail via emailq
               Mailer::resetPassword($user, $plaintext_password);
           }
           catch (Exception $e) {
               $error = $e->getMessage();
           }
       }

       $response = array(
           'success' => $result,
           'error' => $error,
       );
       $this->sendJson($response);
   }

}