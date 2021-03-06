<?php

class LogAction_Core {

    public static function runControllerEvent($action, $object_name, $object_id=NULL)
    {
        $relAtype = LogNumber::getTableNumber($object_name);
        if (empty($relAtype)) {
            return;
        }

        $object_preview = '';
        if ( ! empty($object_id)) {
            $object = ORM::factory($object_name, $object_id);
            $object_preview = $object->preview();
        }
        $relAid = $object_id;

        $logaction = ORM::factory('logaction');
        // Cislo tabulky
        $logaction->relAid = $relAid;
        // ID updatovaneho zaznamu
        $logaction->relAtype = $relAtype;
        // text
        $logaction->text = __('logaction.access.'.$object_name.'.'.$action, array(':user' => Auth::instance()->get_user()->preview(), ':object' => $object_preview));
        // Action name
        $logaction->action = $action;
        // Ulozeni log_action zaznamu
        $logaction->save();
    }
    
    /**
     * Volá se v případě výskytu události v modelu.
     * Slouží k vytvoření instance logovacího objektu daného modelu 
     * (pokud existuje) a zavolání jeho příslušné metody (update/insert/delete)
     * @param <ORM> $orm instance modelu, ve kterém k události dochází
     * @param <string> $event_string  řetězec specifikující událost
     */
    public function runEvent(ORM $orm, $event_string)
    {
        $class_name = "LogAction_Object_".$orm->object_name();
        if (class_exists($class_name)) {
            $log_object = new $class_name;
            switch ($event_string) {
                case 'update':
                    $log_object->updated($orm);
                break;
                case 'insert':
                    $log_object->inserted($orm);
                break;
                case 'delete':
                    $log_object->deleted($orm);
                break;
                case 'undelete':
                    $log_object->undeleted($orm);
                break;
            }
        }

        /**

        try
        {
        	//nactu si danou tridu
        	$class = new ReflectionClass($class_name);

	    	//metoda existuje
			$method = $class->getMethod($event_string);

			//ted si vytvorim instanci dane tridy
			$instance = $class->newInstance();
        }
        catch (ReflectionException $e)
        {
        	return FALSE;
        }

        //do volane metody budu posilat argumenty teto metody, akorat odstranim argument
        //na indexu [1] ($event_strig)
        $args = func_get_args();

        unset($args[1]);

		//vyvolani dane metody
		$method->invokeArgs($instance, $args);

         */


    }
    
    
    
/**
 * SINGLETON DESIGN PATTERN *************************************** //
 */
    public static function instance()
    {
        static $instance;
        empty($instance) and $instance = new LogAction;
        return $instance;
    }
    protected function __construct() {}
    protected function __clone() {}
    
}
?>
