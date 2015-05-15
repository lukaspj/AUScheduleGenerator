<?php
/**
 * Created by PhpStorm.
 * User: Lukas
 * Date: 27-04-2015
 * Time: 17:53
 */

include_once "CalendarEvents.php";
include_once "ScienceEventsGrabber.php";
include_once "BSSEventsGrabber.php";
include_once "CalendarEventsPrinter.php";

class JSONCache {
    public static function GenerateCache($aarskortString)
    {
        $aarskort = explode(";", $aarskortString);
        $events = array();
        /**
         * @var $events CalendarEvents[]
         */
        $name = array();
        foreach($aarskort as $kort)
        {
            $scienceEvents = ScienceEventsGrabber::grabEvents($kort);

            $bssEvents = array_merge(BSSEventsGrabber::grabEvents("spring", $kort), BSSEventsGrabber::grabEvents("autumn", $kort));

            $newEvents = array_merge($scienceEvents, $bssEvents);
            /**
             * @var $newEvents CalendarEvents[]
             */

            foreach($newEvents as $event) {
                if($event->getName() != null){
                    $name[] = $event->getName();
                    break;
                }
            }

            foreach($newEvents as $event)
            {
                $add = true;
                foreach($events as $oldevent)
                    if($oldevent->compareTo($event)){
                        $add = false;
                        $oldevent->mergeWith($event);
                        break;
                    }
                if($add)
                    $events[] = $event;
            }
        }
        $jsondata = json_encode(array("names" => $name,
            "time" => time(),
            "events" => CalendarEvents::ToJSONArray($events)));

        $file = fopen("./cache/$aarskortString", "w") or die("Unable to open file!");
        fwrite($file, $jsondata);
        fclose($file);

        return json_decode($jsondata);
    }

    public static function GetFromCache($aarskort)
    {
        $file = fopen("./cache/$aarskort", "r");
        $fileJSON = json_decode(fread($file, filesize("./cache/$aarskort")));
        fclose($file);
        if($fileJSON->time > (time() - (7 * 24 * 60 * 60)))
        {
            return $fileJSON;
        }
        return JSONCache::GenerateCache($aarskort);
    }

    public static function CacheExists($aarskort)
    {
        return file_exists("./cache/$aarskort");
    }
}