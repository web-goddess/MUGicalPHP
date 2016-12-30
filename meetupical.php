<?php
require 'meetup.php';
require 'config.php';
$meetup = new Meetup(array(
    'key' => AUTH_KEY
));

// Needed when running locally 
date_default_timezone_set('Australia/Sydney');

function dateToCal($timestamp) {
	return date('Ymd\THis', $timestamp);
}

function escapeString($string) {
	return preg_replace('/([\,;])/','\\\$1', $string);
}

function removeLineBreaks($string)
{
	return preg_replace("/\r|\n/", "", $string);
}

function wrapLines($string, $width=74, $break="\r\n ")
{
	$search = '/(.{1,'.$width.'})(?:\s|$)|(.{'.$width.'})/uS';
    $replace = '$1$2'.$break;
	$wrapped = preg_replace($search, $replace, $string);
	$wrapped = trim($wrapped);
	return $wrapped;
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
    'topic_id' => '48471,17628,15582,3833,84681,79740,21549,21441,18062'
    //'topic_id' => '79740' // Only use internet of things when testing
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
		$description = $event->group->name." - ".date("l, F j \a\\t g:i A", $event->time)." - ";
		if ($event->description) {
			$description .= strip_tags(removeLineBreaks($event->description))." - ";
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
".wrapLines(escapeString("SUMMARY:".$event->name))."
".wrapLines(escapeString("DESCRIPTION:".$description))."
CLASS:PUBLIC
CREATED:".dateToCal($event->created)."
".wrapLines(escapeString("LOCATION:".$location))."
".wrapLines(escapeString("URL:".$event->event_url))."
LAST-MODIFIED:".dateToCal($event->updated)."
UID:event_".$event->id."@meetup.com
END:VEVENT";
	}
	return $details;
}
 
$ical .= "
END:VCALENDAR";

// Write to file
ob_start(); 
echo $ical;

$contents = ob_get_contents();
ob_end_clean();
$cwd = getcwd();
$file = "$cwd" .'/'. "sydneymugs.ics";
@chmod($file,0755);
$fw = fopen($file, "w");
fputs($fw,$contents, strlen($contents));
fclose($fw);
die();

?>