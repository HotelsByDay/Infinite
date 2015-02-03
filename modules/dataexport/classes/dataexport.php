<?php defined('SYSPATH') OR die('No direct access allowed.');

class DataExport
{
    public static function Factory($config, $data, $filter_params=array())
    {
        if ( ! isset($config['driver']))
        {
            throw new DataExport_Exception_InvalidConfiguration('Table data export configuration must define "driver" option.');
        }

        $driver_class_name = 'DataExport_Driver_'.(string)$config['driver'];

        try
        {
            $reflected_class = new ReflectionClass($driver_class_name);

            $driver_instance = $reflected_class->newInstance($config, $data, new DataExport_FileStorage(), $filter_params);
        }
        catch (ReflectionException $e)
        {
            throw new DataExport_Exception_DriverNotFound('Unable to instantiate driver class "'.$driver_class_name.'" via constructor. Class may not exists or Constructor is not callable.');
        }

        return $driver_instance;
    }
}
