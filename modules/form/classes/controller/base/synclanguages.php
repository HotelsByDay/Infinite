<?php
/**
 * Created by JetBrains PhpStorm.
 * User: IBM
 * Date: 19.7.12
 * Time: 16:04
 * To change this template use File | Settings | File Templates.
 */
class Controller_Base_SyncLanguages extends Controller_Authentication
{

    /**
     * Synchronizes enabled languages calling model's setEnabledLanguages method
     */
    public function action_set_enabled_languages($object_name, $object_id)
    {
        // Get enabled languages from request
        $languages = (array)arr::get($_POST, AppForm::ENABLED_LANGUAGES_POST_KEY);

        // Set enabled languages through model
        $model = ORM::factory($object_name, $object_id);

        // Check that model implements required interface
        if ($model instanceof Interface_AppFormItemLang_MasterCompatible) {
            $model->setEnabledLanguages($languages);
            $status = 1;
        } else {
            // Log warning
            Kohan::$log->add(Kohana::ERROR, 'set_enabled_languages called with model ("'.$object_name.'") which does not implement Interface_AppFormItemLang_MasterCompatible');
            $status = 0;
        }

        // Send response
        $this->request->headers['Content-Type'] = 'application/json';
        $this->request->response = json_encode(Array('status' => 1));

    }
}
