<?php

class LogAction_Object_Comment extends LogAction_Object_Base {

    /**
     * Vytvari custom obsah zpravy, ktera pujde do historie pri vlozeni nove nabidky.
     */
    public function inserted($orm, $message = NULL, $log_action_categoryid = NULL, $overwrite = array())
    {
//        return parent::inserted($orm,
//                                __('comment.logaction_inserted', array(':user' => $orm->user->preview(), ':preview' => $orm->_rel->preview()),
//                                $log_action_categoryid,
//                                $overwrite));
    }

    /**
     * Vytvari custom obsah zpravy, ktera pujde do historie pri vlozeni nove nabidky.
     */
    public function updated($orm, $message = NULL, $log_action_categoryid = NULL, $overwrite = array())
    {
//        return parent::updated($orm,
//                                __('comment.logaction_inserted', array(':user' => $orm->user->preview(), ':preview' => $orm->_rel->preview()),
//                                $log_action_categoryid,
//                                $overwrite));
    }

    /**
     * Vytvari custom obsah zpravy, ktera pujde do historie pri vlozeni nove nabidky.
     */
    public function deleted($orm, $message = NULL, $log_action_categoryid = NULL, $overwrite = array())
    {
//        return parent::updated($orm,
//                                __('comment.logaction_inserted', array(':user' => $orm->user->preview(), ':preview' => $orm->_rel->preview()),
//                                $log_action_categoryid,
//                                $overwrite));
    }

}
