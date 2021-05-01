<?php

namespace Core;

use DateTime;

class DatesHelper
{
    // Given two strings that represent dates (but one of them may be empty/null/unset)
    public static function sanitizeDateValues($startdate, $enddate)
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
                http_response_code(422);  
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
                http_response_code(422);  
                echo json_encode(
                    array("message" => "Startdate is in the wrong format.")
                );
                exit(1);
            }
        } else {
            if (!DatesHelper::validateDate($startdate)) {
                http_response_code(422);  
                echo json_encode(
                    array("message" => "Startdate is in the wrong format.")
                );
                exit(1);
            } else if (!DatesHelper::validateDate($enddate)) {
                http_response_code(422);  
                echo json_encode(
                    array("message" => "Enddate is in the wrong format.")
                );
                exit(1);
            }
            return array($startdate, $enddate);
        }

    }

    public static function validateDate($date, $format = 'Y-m-d'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}