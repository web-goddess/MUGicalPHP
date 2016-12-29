<?php
require 'meetup.php';
require 'config.php';
$meetup = new Meetup(array(
    'key' => $key;
));

// Needed when running locally 
// date_default_timezone_set('Australia/Sydney');

function dateToCal($timestamp) {
	return date('Ymd\THis', $timestamp);
}

function escapeString($string) {
	return preg_replace('/([\,;])/','\\\$1', $string);
}

function removeEmptyLines($string)
{
	return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\r\n", $string);
}

$ical = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//YOW Conferences - Sydney//EN
NAME:Sydney Meetups
X-WR-CALNAME:Sydney Meetups
BEGIN:VTIMEZONE
TZID:Australia/Sydney
X-LIC-LOCATION:Australia/Sydney
BEGIN:STANDARD
TZOFFSETFROM:+1100
TZOFFSETTO:+1000
TZNAME:AEST
DTSTART:19700405T030000
RRULE:FREQ=YEARLY;BYMONTH=4;BYDAY=1SU
END:STANDARD
BEGIN:DAYLIGHT
TZOFFSETFROM:+1000
TZOFFSETTO:+1100
TZNAME:AEDT
DTSTART:19701004T020000
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=1SU
END:DAYLIGHT
END:VTIMEZONE";

// Find groups
$response = $meetup->getGroups(array(
    'country' => 'AU',
    'upcoming_events' => 'true',
    'location' => 'Sydney, Australia',
    'topic_id' => '48471,17628,15582,3833,84681,79740'
));

foreach ($response as $group) 
{
	$ical .= getEvents($group->id,$meetup);
	//echo "group:" . $group->name . "<br>";
}

function getEvents($group_id,$meetup) {
	$details = "";
	// search for events in group
	$response = $meetup->getEvents(array(
	    'group_id' => $group_id
	));

	foreach ($response->results as $event) 
	{
		$description = $event->group->name."\r\n".date("l, F j \a\\t g:i A", $event->time)."\r\n";
		if ($event->description) {
			$description .= strip_tags(removeEmptyLines($event->description))."\r\n";
		}
		$description .= $event->event_url;
		if ($event->venue->name) {
			$location = $event->venue->name;
			if ($event->venue->address_1) {
				$location .= " (".$event->venue->address_1.", ".$event->venue->city.", ".$event->venue->localized_country_name.")";
			};
		} else {
			$location = "TBC";
		}
		$details .= "
BEGIN:VEVENT
DTSTAMP;TZID=Australia/Sydney:".dateToCal(time())."
DTSTART;TZID=Australia/Sydney:".dateToCal(($event->time)/1000)."
DTEND;TZID=Australia/Sydney:".datetoCal(($event->time + $event->duration)/1000)."
SUMMARY:".str_replace("\r\n", "\r\n ", wordwrap(escapeString($event->name), 67, "\r\n", true))."
DESCRIPTION:".str_replace("\r\n", "\r\n ", wordwrap(escapeString($description), 63, "\r\n",true))."
CLASS:PUBLIC
CREATED:".dateToCal($event->created)."
LOCATION:".str_replace("\r\n", "\r\n ", wordwrap(escapeString($location), 66, "\r\n",true))."
URL:".str_replace("\r\n", "\r\n ", wordwrap(escapeString($event->event_url), 71, "\r\n",true))."
LAST-MODIFIED:".dateToCal($event->updated)."
UID:event_".$event->id."@meetup.com
END:VEVENT";
	}
	return $details;
}
 
$ical .= "
END:VCALENDAR";
 
header("Content-type: text/calendar; charset=utf-8");
header("Content-Disposition: inline; filename=calendar.ics");
echo $ical;

?>