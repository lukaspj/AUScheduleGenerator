<?php
/**
 * Created by PhpStorm.
 * User: Lukas
 * Date: 27-04-2015
 * Time: 17:21
 */

require 'class.iCalReader.php';

function readHeader($ch, $header)
{
    global $responseHeaders;
    global $fileName;
    $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $responseHeaders[$url][] = $header;

    if (strlen($header) > 45 && substr($header, 0, 43) == "Content-Disposition: attachment; filename=\"")
        $fileName = substr($header, 43, strlen($header) - 46);

    return strlen($header);
}

class iCalEventsGrabber
{
    static function downloadiCal($url, $ch = null)
    {
        global $fileName;

        // is cURL installed yet?
        if (!function_exists('curl_init')) {
            die('Sorry cURL is not installed!');
        }

        $close = false;
        if ($ch == null) {
            $ch = curl_init();
            $close = true;
        }

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        curl_setopt($ch, CURLOPT_URL, $url);

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'readHeader');

        $server_output = curl_exec($ch);

        if ($close)
            curl_close($ch);

        if ($fileName == "error.ics")
            return false;

        return $server_output;
    }

    static function grabEvents($iCalURL, $ch = null)
    {
        $ical = new ICal(explode(PHP_EOL, iCalEventsGrabber::downloadiCal($iCalURL, $ch)));
        if ($ical === false)
            return false;
        $retArr = array();
        foreach ($ical->events() as $event) {
            $description = isset($event['DESCRIPTION']) ? $event['DESCRIPTION'] : "";
            if (substr($description, 0, 7) == "\\nType:") {
                $description = substr($description, 8, strpos($description, "\\n", 6) - 8);
                if ($description == "FL")
                    $description = "Forelæsninger";
            }
            $location = preg_replace("/(\d{4}) ?- ?(\d{3})/", '<a href="http://www.au.dk/om/organisation/find-au/bygningskort/?b=$1">$1</a>-$2', $event['LOCATION']);

            $summary = isset($event['SUMMARY']) ? $event['SUMMARY'] : "";

            $retArr[] = new CalendarEvents(
                utf8_encode($summary)
                , (int)date("H", $ical->iCalDateToUnixTimestamp($event['DTSTART']))
                , (int)date("H", $ical->iCalDateToUnixTimestamp($event['DTEND']))
                , $description
                , $location
                , (int)date("N", $ical->iCalDateToUnixTimestamp($event['DTSTART']))
                , (int)date("W", $ical->iCalDateToUnixTimestamp($event['DTSTART']))
                , (int)date("W", $ical->iCalDateToUnixTimestamp($event['DTEND'])),
                utf8_encode($ical->cal_name));
        }
        return $retArr;
    }
}