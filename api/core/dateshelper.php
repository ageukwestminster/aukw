<?php

namespace Core;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

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
     * If both params are null then return today's date and the date a year ago from today.
     * If start date is null but end date is not then return end date and a year ago from end date.
     * If end date is null but start date is not then return start date and today.
     * If neither are null then return the two dates
     * 
     * In all cases, before returning any values both start date and end date are validated 
     * by the {@link validateDate} function. 
     * 
     * @param string|null $startdate The beginning date of the accounting period
     * @param string|null $enddate The end date of the accounting period
     * 
     * @return array Cleansed and validated dates in the array format: [ start_date, end_date ]
     * @throws Exception If the date format is invalid
     */
    public static function sanitizeDateValues(?string $startdate, ?string $enddate): array
    {
        $end = (new DateTimeImmutable())->format('Y-m-d');

        if (empty($startdate) && empty($enddate)) {
            // default values are the period 1 year back from today            
            $start = (new DateTimeImmutable($end))->modify('-1 year')->modify('+1 day')->format('Y-m-d');
            return [$start, $end]; 
        } elseif (empty($startdate)) {
            if (self::validateDate($enddate)) {
                $start = (new DateTimeImmutable($enddate))->modify('-1 year')->modify('+1 day')->format('Y-m-d');
                return [$start, $enddate]; 
            } else {
                throw new Exception("Enddate is in the wrong format.");
            }
        } elseif (empty($enddate)) {
            if (self::validateDate($startdate)) {
                return [$startdate, $end]; 
            } else {
                throw new Exception("Startdate is in the wrong format.");
            }
        } else {
            if (!self::validateDate($startdate)) {
                throw new Exception("Startdate is in the wrong format.");
            } elseif (!self::validateDate($enddate)) {
                throw new Exception("Enddate is in the wrong format.");
            }
            return [$startdate, $enddate];
        }
    }

    /**
     * Attempt to create a PHP DateTime object from the given parameters. If
     * it can be successfully returned then return true, otherwise return false.
     * 
     * @param string $date A date in string format.
     * @param string $format PHP date format string. Default value is 'Y-m-d'
     * 
     * @return bool
     */
    public static function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = DateTimeImmutable::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Return the current date and time in MySql format
     * ( yyyy-mm-dd h:m:s )
     *
     * @return string current date and time
     */
    public static function currentDateTime(): string
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('Europe/London'));
        return $now->format('Y-m-d H:i:s');    // MySQL datetime format
    }

    /**
     * Given a start date and an end date of an accounting period, calculate the dates of the previous year's equivalent period.
     * 
     * @param string $startdate The beginning date of the accounting period
     * @param string $enddate The end date of the accounting period
     * @return array Cleansed and validated dates in the array format: [ start_date, end_date ]
     */
    public static function previousPeriod(string $startdate, string $enddate): array
    {
        $cleanvalues = self::sanitizeDateValues($startdate, $enddate);

        return [
            (new DateTimeImmutable($cleanvalues[0]))->modify('-1 year')->format('Y-m-d'),
            (new DateTimeImmutable($cleanvalues[1]))->modify('-1 year')->format('Y-m-d')
        ];
    }
}