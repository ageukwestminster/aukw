<?php

namespace Core;

use DateTime;
use DateTimeZone;

/**
 * A class supplying helper functions to manage PHP dates and date ranges.
 * 
 * @category Core
 */
class DatesHelper
{

    /**
     * Given two strings that represent dates (but one or both of them may be empty/null/unset),
     * attempt to generate a sensible date range and return it.
     * 
     * Used in date range selectors for generating reports
     * 
     * Logic: 
     * If both params are null then return todays date and the date a year ago from today.
     * If start date is null but end date is not then return end date and a year ago from end date.
     * If end date is null but start date is not then return start date and today.
     * If neither are null then return the two dates
     * 
     * In all cases, before returning any values both start date and end date are validated 
     * by the {@link validateDate} function. 
     * 
     *
     * @param string $startdate The beginning date of the accounting period
     * @param string $enddate The end date of the accounting period
     * 
     * @return array Cleansed and validated dates in the array format: [ start_date, end_date ]
     * 
     */
    public static function sanitizeDateValues($startdate, $enddate) : array
    {
        $end = date('Y-m-d');

        if(empty($startdate) && empty($enddate)) {
            // default values are the period 1 year back from today            
            $start = (new DateTime($end))->modify('-1 year')->modify('+1 day')->format('Y-m-d');
            return array($start, $end); 
        } else if (empty($startdate)) {
            if (DatesHelper::validateDate($enddate)) {
                $start = (new DateTime($enddate))->modify('-1 year')->modify('+1 day')->format('Y-m-d');
                return array($start, $enddate); 
            }
            else {
                http_response_code(400);  
                echo json_encode(
                    array("message" => "Enddate is in the wrong format.")
                );
                exit(1);
            }
        } else if (empty($enddate)) {
            if (DatesHelper::validateDate($startdate)) {
                return array($startdate, $end); 
            }
            else {
                http_response_code(400);  
                echo json_encode(
                    array("message" => "Startdate is in the wrong format.")
                );
                exit(1);
            }
        } else {
            if (!DatesHelper::validateDate($startdate)) {
                http_response_code(400);  
                echo json_encode(
                    array("message" => "Startdate is in the wrong format.")
                );
                exit(1);
            } else if (!DatesHelper::validateDate($enddate)) {
                http_response_code(400);  
                echo json_encode(
                    array("message" => "Enddate is in the wrong format.")
                );
                exit(1);
            }
            return array($startdate, $enddate);
        }

    }

    /**
     * Attempt to create a PHP DateTime object form the given parameters. If
     * it can be successfully returned then return true, otherwise return false.
     * 
     *
     * @param string $date A date in string format.
     * @param string $format PHP date format string. Default value is 'Y-m-d'
     * 
     * @return bool
     * 
     */
    public static function validateDate(string $date, string $format = 'Y-m-d') : bool{
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Return the current date and time in MySql format
     * ( yyyy-mm-dd h:m:s )
     *
     * @return string current date and time
     * 
     */
    public static function currentDateTime() : string{
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/London'));
        return $now->format('Y-m-d H:i:s');    // MySQL datetime format
    }

    /**
     * Given a start date and an end date of an accounting period, calculate the dates of the preious year's equivalent period.
     * 
     * @param string $startdate The beginning date of the accounting period
     * @param string $enddate The end date of the accounting period
     * @return array Cleansed and validated dates in the array format: [ start_date, end_date ]
     */
    public static function previousPeriod($startdate, $enddate) : array
    {
        $cleanvalues = DatesHelper::sanitizeDateValues($startdate, $enddate);

        return array(
            (new DateTime($cleanvalues[0]))->modify('-1 year')->format('Y-m-d'),
            (new DateTime($cleanvalues[1]))->modify('-1 year')->format('Y-m-d')
        );
    }
}