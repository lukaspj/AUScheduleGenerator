<?php header('Content-type: application/json; charset=utf-8');
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

//TODO: This should do a fetch for the individual aarskort and not for it all.
if (JSONCache::CacheExists($_GET['aarskort']) && false) {
    $data = JSONCache::GetFromCache($_GET['aarskort']);
} else {
    $data = JSONCache::GenerateCache($_GET['aarskort']);
}

echo json_encode($data);
