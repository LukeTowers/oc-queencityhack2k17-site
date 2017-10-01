<?php namespace Look\Essentials\Classes;

use Config;
use Carbon\Carbon;

class DateFormat
{
    protected static function parseDate($date = null)
    {
	    $date = $date ?: time();
	    if ($date instanceof Carbon) {
		    return $date;
	    } else {
		    return is_string($date) ? Carbon::parse($date) : Carbon::createFromTimestamp($date);
	    }
    }

    public static function longDate($date = null)
    {
        return self::parseDate($date)->format(Config::get('look.essentials::long_date_format', 'F j Y'));
    }

    public static function longDateTime($date = null)
    {
        return self::parseDate($date)->format(Config::get('look.essentials::long_datetime_format', 'F j Y, h:ia'));
    }

    public static function condensedDate($date = null)
    {
        return self::parseDate($date)->format(Config::get('look.essentials::condensed_date_format', 'Y-m-d'));
    }

    public static function condensedDateTime($date = null)
    {
        return self::parseDate($date)->format(Config::get('look.essentials::condensed_datetime_format', 'Y-m-d H:i'));
    }
}
