<?php
/**
 * Created by PhpStorm.
 * User: Lukas
 * Date: 27-04-2015
 * Time: 17:22
 */

include "iCalEventsGrabber.php";

class BSSEventsGrabber
{
    static function grabEvents($season, $aarskort)
    {
        return iCalEventsGrabber::grabEvents("http://" . $season . "schedule.au.dk/ical/ical.asp?objectclass=student&id=" . $aarskort);
    }
}