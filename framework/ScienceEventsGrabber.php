<?php
/**
 * Created by PhpStorm.
 * User: Lukas
 * Date: 27-04-2015
 * Time: 17:19
 */

require 'simple_html_dom.php';

function dayToInt($day)
{
    switch($day)
    {
        case "Mandag":
            return 1;
        case "Tirsdag":
            return 2;
        case "Onsdag":
            return 3;
        case "Torsdag":
            return 4;
        case "Fredag":
            return 5;
        case "Lørdag":
            return 6;
        case "Søndag":
            return 7;
        default:
            die("Shit got real");
    }
}

class ScienceEventsGrabber {
    static function downloadScienceScheme($aarskort){

        // is cURL installed yet?
        if (!function_exists('curl_init')){
            die('Sorry cURL is not installed!');
        }

        $ch = curl_init();

        //curl_setopt( $ch, CURLOPT_COOKIE, $strCookie );
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, '');  //could be empty, but cause problems on some hosts
        curl_setopt($ch, CURLOPT_COOKIEFILE, '');  //could be empty, but cause problems on some hosts

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_URL,"http://timetable.scitech.au.dk/apps/skema/VaelgElevskema.asp?webnavn=skema");
        curl_exec ($ch);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        curl_setopt($ch, CURLOPT_URL,"http://timetable.scitech.au.dk/apps/skema/ElevSkema.asp");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(array("aarskort"=>$aarskort,
                "B1" => "S%F8g")));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        $server_output = curl_exec ($ch);

        curl_close ($ch);

        return $server_output;
    }

    public static function grabEvents($aarskort)
    {
        $htmlScheme = ScienceEventsGrabber::downloadScienceScheme($aarskort);
        $html = str_get_html($htmlScheme);
        $schemeHeader = $html->find('h2',0);
        $courseHeaders = $html->find('h3');
        $retArr = array();
        foreach($courseHeaders as $header) {
            $name = utf8_encode(substr($schemeHeader->innertext, 17));
            $element = $header->next_sibling();
            $description = "";
            while($element != null && $element->tag != "h3")
            {
                if($element->tag == "strong")
                {
                    $description = $element->plaintext;
                    $element = $element->next_sibling();
                    continue;
                }
                if($element->tag == "table")
                {
                    $tables = $element->children();
                    foreach($tables as $table) {

                        $teamStr = $table->children(0)->xmltext;
                        $teamStr = preg_replace("/href=['\"](.*)['\"]/", "href=\"http://timetable.scitech.au.dk/apps/skema/$1\"", $teamStr);
                        $teamStr = utf8_encode($teamStr);

                        $weekStr = $table->children(4)->plaintext;
                        $weekStr = substr($weekStr, 4);
                        $timeStr = explode(" - ", $table->children(2)->plaintext);

                        $location = $table->children(3)->plaintext;
                        if($location == "")
                            $location = $table->children(5)->plaintext;
                        $location = preg_replace("/(\d{4}) ?- ?(\d{3})/", '<a href="http://www.au.dk/om/organisation/find-au/bygningskort/?b=$1">$1</a>-$2', $location);

                        $dow = dayToInt($table->children(1)->plaintext);
                        if (strpos($weekStr, ", ") !== FALSE) {
                            $weekStrs = explode(", ", $weekStr);
                            $weeks = explode("-", $weekStrs[0]);
                            $retArr[] = new CalendarEvents(
                                utf8_encode($header->innertext)
                                , (int)$timeStr[0]
                                , (int)$timeStr[1]
                                , $description
                                , $location
                                , $dow
                                , (int)$weeks[0]
                                , (int)$weeks[1] == 0 ? $weeks[0] : $weeks[1]
                                , $name
                                , null
                                , $teamStr);
                            $weeks = explode("-", $weekStrs[1]);
                            $retArr[] = new CalendarEvents(
                                utf8_encode($header->innertext)
                                , (int)$timeStr[0]
                                , (int)$timeStr[1]
                                , $description
                                , $location
                                , $dow
                                , (int)$weeks[0]
                                , (int)$weeks[1] == 0 ? $weeks[0] : $weeks[1]
                                , $name
                                , null
                                , $teamStr);
                        } else {
                            $weeks = explode("-", $weekStr);
                            $retArr[] = new CalendarEvents(
                                utf8_encode($header->innertext)
                                , (int)$timeStr[0]
                                , (int)$timeStr[1]
                                , $description
                                , $location
                                , $dow
                                , (int)$weeks[0]
                                , (int)$weeks[1] == 0 ? $weeks[0] : $weeks[1]
                                , $name
                                , null
                                , $teamStr);
                        }
                    }
                }
                $element = $element->next_sibling();
            }
        }
        return $retArr;
    }
}