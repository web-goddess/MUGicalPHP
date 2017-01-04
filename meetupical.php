<?php
require 'meetup.php';
require 'config.php';
$meetup = new Meetup(array(
    'key' => AUTH_KEY
));

// get location, sanitise, and set variables
$input_location = filter_input(INPUT_GET, 'location', FILTER_SANITIZE_SPECIAL_CHARS);

switch ($input_location) {
	case "brisbane":
		$ical_name = "Brisbane";
		$ical_timezone ="TZID:Australia/Brisbane
X-LIC-LOCATION:Australia/Brisbane
BEGIN:STANDARD
TZOFFSETFROM:+1000
TZOFFSETTO:+1000
TZNAME:EST
DTSTART:19700101T000000
END:STANDARD";
		break;
	case "hobart":
		$ical_name = "Hobart";
		$ical_timezone ="TZID:Australia/Hobart
X-LIC-LOCATION:Australia/Hobart
BEGIN:DAYLIGHT
TZOFFSETFROM:+1000
TZOFFSETTO:+1100
TZNAME:EST
DTSTART:19701004T020000
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=1SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+1000
TZOFFSETTO:+1000
TZNAME:EST
DTSTART:19700405T020000
RRULE:FREQ=YEARLY;BYMONTH=4;BYDAY=1SU
END:STANDARD";
		break;
	case "melbourne":
		$ical_name = "Melbourne";
		$ical_timezone ="TZID:Australia/Melbourne
X-LIC-LOCATION:Australia/Melbourne
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
END:DAYLIGHT";
		break;
	case "perth":
		$ical_name = "Perth";
		$ical_timezone ="TZID:Australia/Perth
X-LIC-LOCATION:Australia/Perth
BEGIN:STANDARD
TZOFFSETFROM:+0800
TZOFFSETTO:+0800
TZNAME:WST
DTSTART:19700101T000000
END:STANDARD";
		break;
	default:
		$ical_name = "Sydney";
		$ical_timezone ="TZID:Australia/Sydney
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
END:DAYLIGHT";
}

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

function wrapLines($string, $width=73, $break="\r\n ")
{
	$search = '/(.{1,'.$width.'})(\s|$)|(.{'.$width.'})/uS';
    $replace = '$1$2$3'.$break;
	$wrapped = preg_replace($search, $replace, $string);
	$wrapped = trim($wrapped);
	return $wrapped;
}

$ical = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//YOW Conferences - ".$ical_name."//EN
NAME:".$ical_name." Meetups
X-WR-CALNAME:".$ical_name." Meetups
BEGIN:VTIMEZONE
".$ical_timezone ."
END:VTIMEZONE";

// Find groups
$response = $meetup->getGroups(array(
    'country' => 'AU',
    'upcoming_events' => 'true',
    'location' => $ical_name.', Australia',
    'topic_id' => '48471,17628,15582,3833,84681,79740,21549,21441,18062,15167'
    //'topic_id' => '79740' // testing
));

foreach ($response as $group) 
{
	$ical .= getEvents($group->id,$ical_name,$meetup);
	//echo "group:" . $group->name . "<br>";
}

function getEvents($group_id,$ical_name,$meetup) {
	$details = "";
	// search for events in group
	$response = $meetup->getEvents(array(
	    'group_id' => $group_id
	));

	foreach ($response->results as $event) 
	{
		$description = "";
		if ($event->status == "cancelled") {
			$description .= "CANCELLED! ";
		}
		if ($event->description) {
			$description .= substr(strip_tags(removeLineBreaks($event->name . ' - ' . $event->description)),0,250).'...\n\n';
		}
		$description .= "Event URL: ".$event->event_url;
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
DTSTAMP;TZID=Australia/".$ical_name.":".dateToCal(time())."
DTSTART;TZID=Australia/".$ical_name.":".dateToCal(($event->time)/1000)."
DTEND;TZID=Australia/".$ical_name.":".datetoCal(($event->time + $event->duration)/1000)."
".wrapLines(escapeString("SUMMARY:".$event->group->name))."
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
$file = "$cwd" .'/'.strtolower($ical_name)."mugs.ics";
@chmod($file,0755);
$fw = fopen($file, "w");
fputs($fw,$contents, strlen($contents));
fclose($fw);
die();
?>