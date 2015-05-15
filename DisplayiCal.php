<?php header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename=calendar.ics');
/**
 * Created by PhpStorm.
 * User: Lukas
 * Date: 03-03-2015
 * Time: 11:22
 */

if(!isset($_GET['aarskort']))
    die("No aarskort specified.");

$en = false;
if(isset($_GET['en']) && $_GET['en'] == "1")
    $en = true;

$aarskort = explode(";", $_GET['aarskort']);

// Create connection
$conn = new mysqli("db.clan-net.dk", "web139637", "25n2a9j889", "web139637_1");

foreach ($aarskort as $kort) {
// Check connection
    if (!$conn->connect_error) {
        $sql = "SELECT views FROM skemaviews WHERE aarskort='" . $kort . "'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $sql = "UPDATE skemaviews SET views=" . ($row["views"] + 1) . " WHERE aarskort=" . $kort;
            if ($conn->query($sql) === TRUE) {
            } else {
                echo "Error: $sql <br>" . $conn->error;
            }
        } else {
            $sql = "INSERT INTO skemaviews (aarskort, views) VALUES(" . $kort . ", 1)";
            if ($conn->query($sql) === TRUE) {
            } else {
                echo "Error: $sql <br>" . $conn->error;
            }
        }
    }
}
$conn->close();

$cacheFile = "./cache/" . $_GET['aarskort'] . "_ical";

if($en)
    $cacheFile .= "_en";
if(file_exists($cacheFile))
{
    $file = fopen($cacheFile, "r");
    $first_line = fgets($file);
    if($first_line > (time() - (7 * 24 * 60 * 60)))
    {
        $contents = fread($file, filesize($cacheFile));
        fclose($file);
        echo $contents;
        return;
    }
    fclose($file);
}


include "./Framework.php";
include "./CalendarEvents.php";

$events = array();
$name = array();
foreach($aarskort as $kort)
{
    $scienceEvents = getScienceevents($kort);

    $bssEvents = array_merge(getBSSevents("spring", $kort), getBSSevents("autumn", $kort));

    $newEvents = array_merge($scienceEvents, $bssEvents);

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
$names = implode(" and ", $name);

// the iCal date format. Note the Z on the end indicates a UTC timestamp.
define('DATE_ICAL', 'Ymd\THis\Z');

// max line length is 75 chars. New line is \\n

$content = "BEGIN:VCALENDAR
VERSION:2.0\n";
if($en)
    $content .= "PRODID:LukasPJ//AU ScheduleGenerator//EN\n";
else
    $content .= "PRODID:LukasPJ//AU SkemaGenerator//DA\n";

// loop over events
foreach ($events as $event):
    $day = $event->getDayOfWeek();
    switch($day) {
        case 1:
            $day = "MO";
            break;
        case 2:
            $day = "TU";
            break;
        case 3:
            $day = "WE";
            break;
        case 4:
            $day = "TH";
            break;
        case 5:
            $day = "FR";
            break;
    }
    $content .=
        "BEGIN:VEVENT
SUMMARY:" . $event->getSummary() . "(" . $event->getDescription() . ")
UID:" . uniqid() . "
DTSTART:" . date(DATE_ICAL, $event->getFrom()) . "
DURATION:PT" . date("G", $event->getTo() - $event->getFrom()) /*. "H
DTEND:" . date(DATE_ICAL, strtotime($event->getTo())) */. "H
RRULE:FREQ=WEEKLY;BYDAY=$day;UNTIL=" . date(DATE_ICAL, $event->getTo()) . "
LOCATION:" . $event->getLocation() . "
END:VEVENT\n";
endforeach;

// close calendar
$content .= "END:VCALENDAR";

echo $content;

$file = fopen($cacheFile, "w") or die("Unable to open file!");
fwrite($file, time() . PHP_EOL);
fwrite($file, $content);
fclose($file);



echo $content;