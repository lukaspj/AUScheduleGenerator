<?php header('Content-type: text/html; charset=utf-8');
/**
 * Created by PhpStorm.
 * User: Lukas
 * Date: 03-03-2015
 * Time: 11:22
 */

if (!isset($_GET['aarskort']))
    die("No aarskort specified.");

$en = false;
if (isset($_GET['en']) && $_GET['en'] == "1")
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

include_once "./framework/JSONCache.php";
include_once "./framework/CalendarEvents.php";

foreach ($aarskort as $kort) {
    if (JSONCache::CacheExists($_GET['aarskort'])) {
        $data = JSONCache::GetFromCache($_GET['aarskort']);
    } else {
        $data = JSONCache::GenerateCache($_GET['aarskort']);
    }
}

$pinkkort = array("201206030", "201407296");
$testkort = array();
$styleAppend = "";
foreach ($pinkkort as $kort) {
    if (in_array($kort, $aarskort)) {
        $styleAppend = "_else";
        break;
    }
}
foreach ($testkort as $kort) {
    if (in_array($kort, $aarskort)) {
        $styleAppend = "_test";
        break;
    }
}

$names = implode(" and ", $data->names);

$events = CalendarEvents::FromArray($data->events);

$content = '<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="utf-8" />
    <title>AU schedule generator | ' . $names . '</title>
    <link rel="stylesheet" href="/auskema/css/foundation.css" />
    <link rel="stylesheet" type="text/css" href="/auskema/style' . $styleAppend . '.css">
    <script src="/auskema/js/vendor/modernizr.js"></script>
</head>
<body>';

if ($names != null) {
    if ($en)
        $content .= "<div class='row'><h2>Schedule for: $names</h2></div>";
    else
        $content .= "<div class='row'><h2>Skema for: $names</h2></div>";
}

$content .= CalendarEventsPrinter::printEvents($events, $en, count($data->names) > 1);

$content .= "
    <div class='row'>
        <h4>Feedback, bugs or feature requests? Contact me at <a href='mailto:lukaspj@outlook.com'>lukaspj@outlook.com</a></h4>
    </div>

    <script src='js/vendor/jquery.js'></script>
    <script src='js/foundation.min.js'></script>
    <script>
        $(document).foundation();
    </script>
    </body>

    </html>";


echo $content;
