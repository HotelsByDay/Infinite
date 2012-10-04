<?php


class Helper_DateFormat {

    // User timezone string
    protected static $user_timezone = null;

    /**
     * Set current user timezone - should be called in bootstrap.php
     * @static
     * @param $timezone
     */
    public static function setUserTimezone($timezone)
    {
        self::$user_timezone = $timezone;
    }



    /**
     * @static
     * @param $string date string
     * @param null $format format of date string - if null then default format is used
     * @return string date inf Y-m-d format
     */
    public static function getMysqlDate($user_date, $input_date_format=null)
    {
        if (empty($user_date)) {
            return NULL;
        }
        // If format is not specified then load predefined date format
        if (is_null($input_date_format)) {
            $input_date_format = self::getDateFormat();
        }
        // Create datetime object
        $date = new DateTime();
        // Create date from string in given format (!) there must be assignment (!)
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
        $mysql_date = trim($mysql_date);
        if (empty($mysql_date) or $mysql_date == '0000-00-00' or $mysql_date == '0000-00-00 00:00:00') {
            return null;
        }
        // If format is not specified then load predefined date format
        if (is_null($output_date_format)) {
            $output_date_format = self::getDateFormat();
        }

        // If user's timezone is defined and mysql_date contains time information
        if (self::$user_timezone and strlen($mysql_date) == 19) {
            // Transform date into users timezone - we will return only date part probably but even day may be changed
            // by timezone change
            $mysql_date = Time::toUserTZ(self::$user_timezone, 'Y-m-d H:i:s', $mysql_date);
        }

        return date($output_date_format, strtotime($mysql_date));
    }


    /**
     * Converts user datetime string in given format (or in default format) into mysql datetime string.
     * Timezone ready - if self::$user_timezone is set then mysql_datetime is transformed from default system timezone to user timezone.
     * @static
     * @param $user_datetime string
     * @param null $time_format string - time part format
     * @param null $date_format string - date time format
     * @return string date in desired format
     */
    public static function getMysqlDateTime($user_datetime, $user_datetime_format='@date H:i')
    {
        $user_datetime = trim($user_datetime);
        if (empty($user_datetime)) {
            return null;
        }
        // If predefined date format is needed
        if (strpos($user_datetime_format, '@date') !== false) {
            // Get date format
            $date_format = self::getDateFormat();
            // Update datetime format string
            $user_datetime_format = str_replace('@date', $date_format, $user_datetime_format);
        }

        // Create datetime object
        $date = new DateTime();
        // Create date from string in given format
        $date = $date->createFromFormat($user_datetime_format, $user_datetime);
        // Get mysql date time string
        $mysql_datetime = $date->format('Y-m-d H:i:s');

        // If user's timezone is defined
        if (self::$user_timezone) {
            // Transform datetime into in user's timezone into db (default) timezone
            $mysql_datetime = Time::toSystemTZ(self::$user_timezone, 'Y-m-d H:i:s', $user_datetime);
        }

        // Return datetime string in given format
        return $mysql_datetime;
    }

    /**
     * @static
     * @param $mysql_datetime
     * @param string $user_datetime_format
     * @return Returns similar value as getUserDateTime but each of date and time is closed in <span> with class
     */
    public static function getUserDateTimeFormatted($mysql_datetime, $time_format='H:i')
    {
        return '<span class="date">' . static::getUserDate($mysql_datetime) . '</span>&nbsp;<span class="time">' . static::getUserDate($mysql_datetime, $time_format) . '</span>';
    }

    /**
     * Converts mysql datetime string into user datetime string in given format (or in default format).
     * Timezone ready - if self::$user_timezone is set then mysql_datetime is transformed from default system timezone to user timezone.
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

        // If user's timezone is defined
        if (self::$user_timezone) {
            // Transform datetime into user timezone
            $mysql_datetime = Time::toUserTZ(self::$user_timezone, 'Y-m-d H:i:s', $mysql_datetime);
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
