<?php

class CalendarEvent {
    /**
	 *
	 * The event ID
	 * @var string
	 */
    private $uid;
    /**
	 * The event start date
	 * @var DateTime
	 */
    private $start;
    /**
	 * The event end date
	 * @var DateTime
	 */
    private $end;
    /**
	 *
	 * The event title
	 * @var string
	 */
    private $summary;
    /**
	 * The event description
	 * @var string
	 */
    private $description;
    /**
	 * The event location
	 * @var string
	 */
    private $location;
    public function __construct($parameters) {
        $parameters += array(
          'description' => 'Cours à IFIDE Sup\'Formation',
          'location' => 'IFIDE Sup\'Formation'
        );
        if (isset($parameters['uid'])) {
            $this->uid = $parameters['uid'];
        } else {
            $this->uid = uniqid(rand(0, getmypid()));
        }
        $this->start = $parameters['start'];
        $this->end = $parameters['end'];
        $this->summary = $parameters['summary'];
        $this->description = $parameters['description'];
        $this->location = $parameters['location'];
		return $this;
    }
    /**
	 * Get the start time set for the even
	 * @return string
	 */
    private function formatDate($date) {
        return $date->format("Ymd\THis");
    }
    /* Escape commas, semi-colons, backslashes.
	http://stackoverflow.com/questions/1590368/should-a-colon-character-be-escaped-in-text-values-in-icalendar-rfc2445
	 */
    private function formatValue($str) {
        return addcslashes($str, ",\\;");
    }
    public function generateString() {
        $created = new DateTime();
        $content = '';
        $content = "BEGIN:VEVENT\r\n"
                 . "UID:{$this->uid}\r\n"
                 . "DTSTAMP:{$this->formatDate($this->start)}\r\n"
                 . "DTSTART;TZID=Europe/Paris:{$this->formatDate($this->start)}\r\n"
                 . "DTEND;TZID=Europe/Paris:{$this->formatDate($this->end)}\r\n"
         #        . "CREATED:{$this->formatDate($created)}\r\n"
                 . "DESCRIPTION:{$this->formatValue($this->description)}\r\n"
         #        . "LAST-MODIFIED:{$this->formatDate($this->start)}\r\n"
                 . "LOCATION:{$this->location}\r\n"
                 . "SUMMARY:{$this->formatValue($this->summary)}\r\n"
         #        . "SEQUENCE:0\r\n"
         #        . "STATUS:CONFIRMED\r\n"
         #        . "TRANSP:OPAQUE\r\n"
                 . "END:VEVENT\r\n"
                 . "";
        return $content;
    }
}
class Calendar {
    protected $events;
    protected $title;
    protected $author;
    public function __construct($parameters) {
        $parameters += array(
          'events' => array(),
          'title' => 'GPCI IFIDE Sup\'Formation',
          'author' => 'IFIDE Sup\'Formation'
        );
        $this->events = $parameters['events'];
        $this->title  = $parameters['title'];
        $this->author = $parameters['author'];
    }
    /**
	 *
	 * Call this function to download the invite.
         * Content-Type envoyé dans enseignant.php
         * Content-Length ajouté par le serveur Apache
	 */
    public function generateDownload() {
        $generated = $this->generateString();
        #header('Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); //date in the past
        header('Content-Type: text/calendar; charset=utf-8');
        header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); //tell it we just updated
        header('Cache-Control: no-store, no-cache, must-revalidate' ); //force revaidation
        header('Cache-Control: post-check=0, pre-check=0', false );
        header('Pragma: no-cache' );
        header('Content-Disposition: inline; filename="calendar.ics"');
        header('Content-Description: File Transfer');
        #header("Content-Transfer-Encoding: binary");
        #header("Content-Length: " . strlen($generated));
        print $generated;
    }


    public function generateStringTest() {
       return "BEGIN:VCALENDAR\r\n"
                . "VERSION:2.0\r\n"
                . "PRODID:-//Test//NONSGML kigkonsult.se iCalcreator 2.20.2//\r\n" 
                . "CALSCALE:GREGORIAN\r\n"
                . "METHOD:PUBLISH\r\n"
                . "X-WR-CALNAME:Test\r\n"
                . "X-WR-CALDESC:Planning Test\r\n"
                . "X-WR-TIMEZONE:Europe/Paris\r\n"
                . "BEGIN:VEVENT\r\n"
                . "UID:20200926T201917CEST-715732jEKh@Test\r\n"
                . "DTSTAMP:20201003T110000Z\r\n"
                . "DESCRIPTION:SIO 2 option SLAM\r\n"
                . "DTSTART:20201003T110000Z\r\n"
                . "DTEND:20201003T150000Z\r\n"
                . "LOCATION:SALLE A\r\n"
                . "SUMMARY:SLAM4 option\r\n" 
                . "END:VEVENT\r\n"
                . "BEGIN:VEVENT\r\n"
                . "UID:20200926T201917CEST-715733kEKh@Test\r\n"
                . "DTSTAMP:20201004T110000Z\r\n"
                . "DESCRIPTION:SIO 2 option SLAM\r\n"
                . "DTSTART:20201004T110000Z\r\n"
                . "DTEND:20201004T150000Z\r\n"
                . "LOCATION:B039\r\n"
                . "SUMMARY:SLAM4 option\r\n" 
                . "END:VEVENT\r\n"
                . "END:VCALENDAR\r\n"
                . "";
    }


    /**
	 *
	 * The function generates the actual content of the ICS
	 * file and returns it.
	 *
	 * @return string|bool
	 */
    public function generateString() {
        if(count($this->events)===0)
           return $this->generateStringTest();
        $content = "BEGIN:VCALENDAR\r\n"
                 . "VERSION:2.0\r\n"
                 . "PRODID:-//" . $this->author . "//GPCI//FR\r\n"
                 . "CALSCALE:GREGORIAN\r\n"
                 . "METHOD:PUBLISH\r\n"
                 . "X-WR-CALNAME:" . $this->title . "\r\n"
                 . "X-WR-CALDESC:" . "Planning IFIDE SUP'FORMATION" . "\r\n"
                 . "X-WR-TIMEZONE:Europe/Paris\r\n"
                 . "BEGIN:VTIMEZONE" . "\r\n"
                 . "TZID:Europe/Paris" . "\r\n"
                 . "X-LIC-LOCATION:Europe/Paris" . "\r\n"
                 . "BEGIN:DAYLIGHT" . "\r\n"
                 . "TZOFFSETFROM:+0100" . "\r\n"
                 . "TZOFFSETTO:+0200" . "\r\n"
                 . "TZNAME:CEST" . "\r\n"
                 . "DTSTART:19700329T020000" . "\r\n"
                 . "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU" . "\r\n"
                 . "END:DAYLIGHT" . "\r\n"
                 . "BEGIN:STANDARD" . "\r\n"
                 . "TZOFFSETFROM:+0200" . "\r\n"
                 . "TZOFFSETTO:+0100" . "\r\n"
                 . "TZNAME:CET" . "\r\n"
                 . "DTSTART:19701025T030000" . "\r\n"
                 . "RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU" . "\r\n"
                 . "END:STANDARD" . "\r\n"
                 . "END:VTIMEZONE" . "\r\n"
                 . "";
        foreach($this->events as $event) {
            $content .= $event->generateString();
        } 
	    $content .= "END:VCALENDAR";
        return $content;
	}
}
