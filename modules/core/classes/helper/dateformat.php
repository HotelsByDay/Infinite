<?php


class Helper_DateFormat {


    /**
     * @static
     * @param $string date string
     * @param null $format format of date string - if null then default format is used
     * @return string date inf Y-m-d format
     */
    public static function getMysqlDate($user_date, $input_date_format=null)
    {
        // If format is not specified then load predefined date format
        if (is_null($input_date_format)) {
            $input_date_format = self::getDateFormat();
        }
        // Create datetime object
        $date = new DateTime();
        // Create date from string in given format
        $date = $date->createFromFormat($input_date_format, $user_date);
        // Return string in DB format
        return $date->format('Y-m-d');
    }


    /**
     * @static
     * @param $mysql_date string
     * @param null $format string
     * @return string date in desired format
     */
    public static function getUserDate($mysql_date, $output_date_format=null)
    {
        if (empty($mysql_date) or $mysql_date == '0000-00-00') {
            return null;
        }
        // If format is not specified then load predefined date format
        if (is_null($output_date_format)) {
            $output_date_format = self::getDateFormat();
        }

        return date($output_date_format, strtotime($mysql_date));
    }


    /**
     * @static
     * @param $mysql_date string
     * @param null $time_format string - time part format
     * @param null $date_format string - date time format
     * @return string date in desired format
     */
    public static function getUserDateTime($mysql_datetime, $datetime_format='@date H:i')
    {
        if (empty($mysql_datetime) or $mysql_datetime == '0000-00-00' or $mysql_datetime == '0000-00-00 00:00:00') {
            return null;
        }
        // If predefined date format is needed
        if (strpos($datetime_format, '@date') !== false) {
            // Get date format
            $date_format = self::getDateFormat();
            // Update datetime format string
            $datetime_format = str_replace('@date', $date_format, $datetime_format);
        }

        // Return datetime string in given format
        return date($datetime_format, strtotime($mysql_datetime));
    }



    /**
     * @static
     * @return date format string for usage in php
     */
    public static function getDateFormat()
    {
        // Read date format from config
        return AppConfig::instance()->get('date_format', 'datetime');
    }

    /**
     * @static
     * @return date format string for jQuery DatePicker plugin
     */
    public static function getDatePickerDateFormat()
    {
        // Read date format from config
        return AppConfig::instance()->get('datepicker_date_format', 'datetime');
    }


}
