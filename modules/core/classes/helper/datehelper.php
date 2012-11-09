<?php
/**
 * Helper for datetime operations
 * @author Jiri Dajc
 */
class Helper_DateHelper
{

    /**
     * Returns last week number for given year
     * @static
     * @param null $year
     * @return int
     */
    public static function getLastWeekOfTheYear($year=null)
    {
        if (empty($year)) {
            $year = Date('Y');
        }
        $date = new DateTime;
        $date->setISODate((int)$year, 53);
        return ($date->format("W") === "53" ? 53 : 52);
    }



}
