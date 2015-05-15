<?php
/**
 * Created by PhpStorm.
 * User: Lukas
 * Date: 03-03-2015
 * Time: 15:12
 */

class CalendarEvents {

    private $mSummary;
    private $mFrom;
    private $mTo;
    private $mDOW;
    private $mWeekFrom;
    private $mWeekTo;
    private $mDescription;
    private $mLocation;
    private $mName;
    private $mOtherNames;

    public function __construct($summary, $from, $to, $desc, $location, $dow, $weekFrom, $weekTo, $name, $otherNames = array()){
        $this->mSummary = $summary;
        $this->mFrom = $from;
        $this->mTo = $to;
        $this->mDescription = $desc;
        $this->mLocation = $location;
        $this->mDOW = $dow;
        $this->mWeekFrom = $weekFrom;
        $this->mWeekTo = $weekTo;
        $this->mName = $name;
        $this->mOtherNames = $otherNames;
    }

    public static function FromArray($events)
    {
        $retArr = array();
        foreach($events as $event)
        {
            $retArr[] = new CalendarEvents(
                $event->summary,
                $event->from,
                $event->to,
                $event->description,
                $event->location,
                $event->dow,
                $event->weekfrom,
                $event->weekto,
                $event->name,
                $event->othernames
            );
        }
        return $retArr;
    }

    public static function ToJSONArray($events)
    {
        $retArr = array();
        foreach($events as $event)
        {
            /* @var $event CalendarEvents */
            $retArr[] = $event->jsonSerialize();
        }
        return $retArr;
    }

    public function getName() {
        return $this->mName;
    }

    public function getNames() {
        return array_merge(array($this->getName()), $this->mOtherNames);
    }

    public function getSummary() {
        return $this->mSummary;
    }

    public function getFrom() {
        return $this->mFrom;
    }

    public function getTo() {
        return $this->mTo;
    }

    public function getDescription() {
        return $this->mDescription;
    }

    public function getLocation() {
        return $this->mLocation;
    }

    public function getDayOfWeek() {
        return $this->mDOW;
    }

    public function getWeekFrom() {
        return $this->mWeekFrom;
    }

    public function getWeekTo() {
        return $this->mWeekTo;
    }

    public function printEvent(){
        echo "$this->mSummary<br>$this->mFrom - $this->mTo<br>Day of week: $this->mDOW<br>Weeks: $this->mWeekFrom - $this->mWeekTo<br>";
    }

    public function compareTo($other) {
        if($this->getSummary() == $other->getSummary()
        and $this->getWeekFrom() == $other->getWeekFrom()
        and $this->getWeekTo() == $other->getWeekTo()
        and $this->getDayOfWeek() == $other->getDayOfWeek()
        and $this->getDescription() == $other->getDescription())
            return true;
        return false;
    }

    public function mergeWith($other) {
        if($other->getName() == $this->getName())
            return;
        if(is_array($this->mOtherNames))
        {
            foreach($this->getNames() as $name)
            {
                if($name == $other->getName())
                    return;
            }
            $this->mOtherNames[] = $other->getName();
            return;
        }
        $this->mOtherNames = array($other->getName());
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        //return get_object_vars($this);
        return array(
            'summary' => $this->mSummary,
            'from' => $this->mFrom,
            'to' => $this->mTo,
            'dow' => $this->mDOW,
            'weekfrom' => $this->mWeekFrom,
            'weekto' => $this->mWeekTo,
            'description' => $this->mDescription,
            'location' => $this->mLocation,
            'name' => $this->mName,
            'othernames' => $this->mOtherNames
        );
    }
}