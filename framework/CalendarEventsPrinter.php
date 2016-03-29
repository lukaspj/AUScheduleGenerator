<?php

/**
 * Created by PhpStorm.
 * User: Lukas
 * Date: 27-04-2015
 * Time: 17:28
 */
class CalendarEventsPrinter
{
    static function printEventTable($events, $times, $eventMap, $weekFrom, $weekTo, $weekMin, $en, $multiple)
    {
        /**
         * @var $events CalendarEvents[]
         */
        $fromTime = array(999999999999, 999999999999, 999999999999, 999999999999, 999999999999);
        $toTime = array(0, 0, 0, 0, 0);

        $intervalFrom = 23;
        $intervalTo = 0;

        foreach ($events as $event) {
            if (($event->getWeekFrom() - $weekMin) >= $weekFrom and ($event->getWeekFrom() - $weekMin) <= $weekTo or
                ($event->getWeekTo() - $weekMin) <= $weekTo and ($event->getWeekTo() - $weekMin) >= $weekFrom or
                ($event->getWeekFrom() - $weekMin) <= $weekFrom and ($event->getWeekTo() - $weekMin) >= $weekFrom
            ) {
                $dow = $event->getDayOfWeek();

                $fromHours = $event->getFrom();
                $toHours = $event->getTo();

                if ($fromTime[$dow - 1] > $event->getFrom()) {
                    $fromTime[$dow - 1] = $event->getFrom();
                    if ($intervalFrom > $fromHours)
                        $intervalFrom = $fromHours;

                }
                if ($toTime[$dow - 1] < $event->getTo()) {
                    $toTime[$dow - 1] = $event->getTo();
                    if ($intervalTo < $toHours)
                        $intervalTo = $toHours;
                }
            }
        }

        $weekstring = "Uge";
        $mondayString = "Mandag";
        $tuesdayString = "Tirsdag";
        $wednesdayString = "Onsdag";
        $thursdayString = "Torsdag";
        $fridayString = "Fredag";
        $teamString = "Hold";

        if ($en) {
            $weekstring = "Week";
            $mondayString = "Monday";
            $tuesdayString = "Tuesday";
            $wednesdayString = "Wednesday";
            $thursdayString = "Thursday";
            $fridayString = "Friday";
            $teamString = "Team";
        }

        $dateFrom = date("d/m", strtotime(date("Y") . "W" . ($weekMin + $weekFrom) . "1"));
        $dateTo = date("d/m", strtotime(date("Y") . "W" . ($weekMin + $weekTo) . "7"));
        $out = "    <div class='row'>
    <strong>$weekstring: " . ($weekMin + $weekFrom) . " - " . ($weekMin + $weekTo) . " ($dateFrom - $dateTo)</strong>";

        if (($weekMin + $weekFrom) == ($weekMin + $weekTo))
            $out = "    <div class='row'>
    <strong>$weekstring: " . ($weekMin + $weekFrom) . " ($dateFrom - $dateTo)</strong>";

        $out .= "    <table>
        <thead>
        <tr>
            <th class='time-column'></th>
            <th>$mondayString</th>
            <th>$tuesdayString</th>
            <th>$wednesdayString</th>
            <th>$thursdayString</th>
            <th>$fridayString</th>
        </tr>
        </thead>
        <tbody>
        ";
        $hourCount = 0;
        for ($i = $intervalFrom; $i < $intervalTo; $i++) {
            $out .= "<tr><td class='time-column'>$i:00</td>";

            for ($j = 0; $j < 5; $j++) {
                $out .= "<td>";
                if (sizeof($times[$weekFrom][$j][$i]) > 0) {
                    $hourCount++;
                    $classAppend = "";
                    switch (sizeof($times[$weekFrom][$j][$i])) {
                        case 2:
                            $classAppend = " half";
                            break;
                        case 3:
                            $classAppend = " third";
                            break;
                        case 4:
                            $classAppend = " fourth";
                            break;
                    }
                    foreach ($times[$weekFrom][$j][$i] as $event) {
                        /* @var $event CalendarEvents */
                        $description = $event->getDescription();
                        if ($en) {
                            if ($description == "Forelæsninger")
                                $description = "Lecture";
                            if ($description == "Teoretiske øvelser")
                                $description = "Theoretical exercises";
                            if ($description == "Hold")
                                $description = "Group";
                        }
                        $out .= "<div class='box boxColor" . $eventMap[$event->getSummary()] . $classAppend . "'>";
                        $out .= "<strong>" . $event->getSummary() . "</strong><br />";
                        $out .= "<strong>" . $description . "</strong><br />";
                        if($event->getTeam() != null) {
                            $out .= "<em>$teamString " . $event->getTeam() . "</em><br />";
                        }
                        $out .= "" . $event->getLocation() . "<br />";
                        if ($multiple)
                            $out .= "<i>" . implode(" + ", $event->getNames()) . "</i><br/>";
                        $out .= "</div>";
                    }
                }
                $out .= "</td>";
            }

            $out .= "</tr>";
        }
        $out .= "
    </tbody>
    </table>
    </div>";
        if ($hourCount > 0)
            return $out;
        return "";
    }


    static function times_equal($a, $b)
    {
        for ($i = 0; $i < 5; $i++) {
            for ($j = 0; $j < 24; $j++) {
                if (isset($a[$i][$j]))
                    if (isset($b[$i][$j])) {
                        if (sizeof($a[$i][$j]) != sizeof($b[$i][$j]))
                            return false;
                        for ($k = 0; $k < sizeof($a[$i][$j]); $k++)
                            if (!$a[$i][$j][$k]->compareEventTo($b[$i][$j][$k]))
                                return false;
                    } else
                        return false;
                else
                    if (isset($b[$i][$j]))
                        return false;
            }
        }
        return true;
    }

    static function printEvents($events, $en = false, $multiple = false)
    {
        /**
         * @var $events CalendarEvents[]
         */
        $return = "";
        $fromTime = array(999999999999, 999999999999, 999999999999, 999999999999, 999999999999);
        $toTime = array(0, 0, 0, 0, 0);

        $intervalFrom = 23;
        $intervalTo = 0;

        $weekMin = 100;
        $weekBound = (int)date("W", time());
        if (isset($_GET['allweeks']) && $_GET['allweeks'] == "1")
            $weekBound = 0;

        foreach ($events as $event)
            if ($weekMin > $event->getWeekFrom())
                $weekMin = $event->getWeekFrom();

        if ($weekMin < $weekBound)
            $weekMin = $weekBound;

        $times = array(array(array(array())));
        for ($k = 0; $k < 52; $k++)
            for ($i = 0; $i < 5; $i++)
                for ($j = 0; $j < 24; $j++)
                    $times[$k][$i][$j] = array();

        $eventMap = array();
        $eventCount = 0;
        $name = null;
        foreach ($events as $event) {
            $dow = $event->getDayOfWeek();

            $fromHours = $event->getFrom();
            $toHours = $event->getTo();

            if ($fromTime[$dow - 1] > $event->getFrom()) {
                $fromTime[$dow - 1] = $event->getFrom();
                if ($intervalFrom > $fromHours)
                    $intervalFrom = $fromHours;

            }
            if ($toTime[$dow - 1] < $event->getTo()) {
                $toTime[$dow - 1] = $event->getTo();
                if ($intervalTo < $toHours)
                    $intervalTo = $toHours;
            }

            for ($i = $fromHours; $i < $toHours; $i++)
                for ($j = $event->getWeekFrom() - $weekMin; $j <= $event->getWeekTo() - $weekMin; $j++) {
                    if (isset($times[$j][$dow - 1][$i]))
                        $times[$j][$dow - 1][$i] = array_merge($times[$j][$dow - 1][$i], array($event));
                    else
                        $times[$j][$dow - 1][$i] = array($event);
                }

            if (!isset($eventMap[$event->getSummary()]) and $event->getWeekTo() > $weekBound) {
                $eventMap[$event->getSummary()] = $eventCount;
                $eventCount++;
            }
        }

        $weekInterval = array();
        $currentIDX = 0;
        while ($currentIDX < 52) {
            $weekInterval[] = $currentIDX;
            $currentIDX++;
            while ($currentIDX < 52 and CalendarEventsPrinter::times_equal($times[$currentIDX], $times[$currentIDX - 1]))
                $currentIDX++;
            $weekInterval[] = $currentIDX - 1;
        }

        for ($i = 0; $i < sizeof($weekInterval); $i += 2)
            $return .= CalendarEventsPrinter::printEventTable($events, $times, $eventMap, $weekInterval[$i], $weekInterval[$i + 1], $weekMin, $en, $multiple);
        return $return;
    }
}