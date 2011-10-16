<?php
/*
 * Copyright (c) 2010 Floor Terra <floort@gmail.com>
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

$HOST = "";
$DB = "";
$USER = "";
$PASSWD = "";
$DB_PREFIX = "";

/* Fiddle with offsets to get your timezone. */
$NOW = time()- 60*60*24;
$DATEFORMAT = "Ymd\\THis";
$DTOFFSET = -3600;

function safe($s) {
	$s = strip_tags($s);
	$s = str_replace(array("\n", "\r"), "", $s);
	$s = str_replace(",", "\,", $s);
	$s = str_replace(";", "\;", $s);
	$s = utf8_encode($s);
	return $s;
}

function safedate($dt) {
	if (date("I", $dt) == "1") {
		return date($DATEFORMAT, $dt - 7200);
	} else {
		return date($DATEFORMAT, $dt+$DTOFFSET);
	}
	return $d;
}



/* Connect to the joomla database */
$db = new mysqli($HOST, $USER, $PASSWD, $DB);

header("Content-type: text/calendar;encoding=UTF-8");
header("Content-Disposition: attachment; filename=JD.ical");
/* Write iCal header */
echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//JD/JD//NONSGML v1.0//EN\r\n";
echo "METHOD:PUBLISH\r\n";
echo "X-WR-CALNAME:Jonge Democraten\r\n";
echo "X-WR-TIMEZONE:Europe/Amsterdam\r\n";
echo "X-WR-CALDESC:De agenda van de Jonge Democraten\r\n";

if ($res = $db->query("SELECT * FROM ".$DB_PREFIX."jevents_vevdetail WHERE dtstart > $NOW;")) {
	while ($row = $res->fetch_object()) {
		echo "BEGIN:VEVENT\r\n";
		echo "UID:" . $row->evdet_id . "\r\n";
		if (date("I", $row->dtstart) == "1") {
			echo "DTSTART:" . date($DATEFORMAT, $row->dtstart - 7200) . "Z\r\n";
		} else {
			echo "DTSTART:" . date($DATEFORMAT, $row->dtstart - 3600) . "Z\r\n";
		}
		if ($row->noendtime) {
			/* The event has no end time, assume it lasts untill the end of day. */
			if (date("I", $row->dtstart) == "1") {
				echo "DTEND:" . substr(date($DATEFORMAT, $row->dtstart - 7200), 0, -6) . "215959Z\r\n";
			} else {
				echo "DTEND:" . substr(date($DATEFORMAT, $row->dtstart - 3600), 0, -6) . "225959Z\r\n";
			}
		} else {
			if (date("I", $row->dtend) == "1") {
				echo "DTEND:" . date($DATEFORMAT, $row->dtend - 7200) . "Z\r\n";
			} else {
				echo "DTEND:" . date($DATEFORMAT, $row->dtend - 3600) . "Z\r\n";
			}
		}
		echo "DESCRIPTION:" . safe($row->description) . "\r\n";
		echo "SUMMARY:" . safe($row->summary) . "\r\n";
		echo "LOCATION:" . safe($row->location) . "\r\n";
		echo "STATUS:CONFIRMED\r\n";
		echo "END:VEVENT\r\n";
	}
}

echo "END:VCALENDAR";

?>
