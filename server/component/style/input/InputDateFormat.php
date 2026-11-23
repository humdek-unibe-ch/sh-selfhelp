<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * Display and storage formats of the date, datetime and time inputs.
 *
 * The stored value is always the storage format of the input type (date
 * `Y-m-d`, datetime `Y-m-d H:i`, time `H:i`), so conditions, sorting and
 * exports can rely on it. The field `date_format` only sets what the user
 * sees. Its options are the lookups `dateInputFormats`; this list must match
 * them. The tokens are flatpickr tokens, which PHP reads the same way.
 */
class InputDateFormat
{
    /** Date display formats => pattern for humans and the LLM. */
    const DATE_FORMATS = array(
        'Y-m-d' => 'YYYY-MM-DD',
        'd.m.Y' => 'DD.MM.YYYY',
        'd/m/Y' => 'DD/MM/YYYY',
        'm/d/Y' => 'MM/DD/YYYY',
        'j. F Y' => 'D. MMMM YYYY',
        'j F Y' => 'D MMMM YYYY',
        'F j, Y' => 'MMMM D, YYYY',
    );

    const DEFAULT_DATE_FORMAT = 'Y-m-d';

    /** German month names (full and short) => English, which PHP parses. */
    const MONTH_NAMES = array(
        'januar' => 'January', 'jänner' => 'January', 'jaenner' => 'January',
        'februar' => 'February', 'märz' => 'March', 'maerz' => 'March', 'marz' => 'March',
        'mai' => 'May', 'juni' => 'June', 'juli' => 'July', 'oktober' => 'October',
        'dezember' => 'December', 'jän' => 'Jan', 'mär' => 'Mar', 'okt' => 'Oct', 'dez' => 'Dec',
    );

    /**
     * @param string $type The `type_input` of the input.
     * @return bool True for the types with a date picker.
     */
    public static function is_date_type($type)
    {
        return $type === 'date' || $type === 'datetime' || $type === 'time';
    }

    /**
     * @param string $type date, datetime or time
     * @return string The format of the stored value.
     */
    public static function storage_format($type)
    {
        if ($type === 'datetime') return 'Y-m-d H:i';
        if ($type === 'time') return 'H:i';
        return 'Y-m-d';
    }

    /**
     * @param string $date_format The field `date_format`; unknown values
     *  fall back to `Y-m-d`.
     * @return string A valid date display format.
     */
    private static function date_part($date_format)
    {
        return isset(self::DATE_FORMATS[$date_format]) ? $date_format : self::DEFAULT_DATE_FORMAT;
    }

    /**
     * @param string $type date, datetime or time
     * @param string $date_format The field `date_format`.
     * @return string The display format (flatpickr tokens).
     */
    public static function display_format($type, $date_format)
    {
        if ($type === 'time') return 'H:i';
        $date = self::date_part($date_format);
        return $type === 'datetime' ? $date . ' H:i' : $date;
    }

    /**
     * @param string $type date, datetime or time
     * @param string $date_format The field `date_format`.
     * @return string The display format as a pattern for humans, e.g. `DD.MM.YYYY`.
     */
    public static function display_pattern($type, $date_format)
    {
        if ($type === 'time') return 'HH:mm';
        $pattern = self::DATE_FORMATS[self::date_part($date_format)];
        return $type === 'datetime' ? $pattern . ' HH:mm' : $pattern;
    }

    /**
     * Convert a value to the storage format. Accepts the storage format,
     * ISO variants (`T` separator, seconds, offset; the offset is ignored,
     * the wall time is kept) and the display format of the field, with month
     * names in English or German.
     *
     * @param string $type date, datetime or time
     * @param string $date_format The field `date_format`.
     * @param mixed $value
     * @return string|null The stored value, '' for an empty value, or null
     *  if the value cannot be read.
     */
    public static function to_storage($type, $date_format, $value)
    {
        if ($value === null || is_array($value)) return null;
        $value = trim(preg_replace('/\s+/u', ' ', (string)$value));
        if ($value === '') return '';
        $storage = self::storage_format($type);
        if ($type === 'time') {
            $formats = array('H:i', 'H:i:s');
        } else if ($type === 'datetime') {
            $formats = array('Y-m-d H:i', 'Y-m-d H:i:s', 'Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i:sP',
                'Y-m-d\TH:i:s.vP', 'Y-m-d\TH:i:s.v\Z', 'Y-m-d\TH:i:s\Z');
        } else {
            // ISO with a time (e.g. from the mobile app): the time is dropped
            $formats = array('Y-m-d', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i:sP', 'Y-m-d\TH:i:s.vP',
                'Y-m-d\TH:i:s.v\Z', 'Y-m-d\TH:i:s\Z');
        }
        $formats[] = self::display_format($type, $date_format);
        $english = self::english_month_names($value);
        foreach ($formats as $format) {
            // `!` resets the fields that are not in the format (no "now" parts).
            $date = DateTime::createFromFormat('!' . $format, $english);
            $errors = DateTime::getLastErrors();
            if ($date !== false && ($errors === false
                || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date->format($storage);
            }
        }
        return null;
    }

    /**
     * A value in the display format of the field, e.g. for lists. Month names
     * are German for the language `de`, else English (as the date picker).
     *
     * @param string $type date, datetime or time
     * @param string $date_format The field `date_format`.
     * @param mixed $value Stored value (old display-format values are read too).
     * @param string $language Two-letter language code of the user.
     * @return string|null The display text, '' for an empty value, or null
     *  if the value cannot be read.
     */
    public static function to_display($type, $date_format, $value, $language)
    {
        $stored = self::to_storage($type, $date_format, $value);
        if ($stored === null || $stored === '') return $stored;
        $date = DateTime::createFromFormat('!' . self::storage_format($type), $stored);
        $text = $date->format(self::display_format($type, $date_format));
        if ($language === 'de') {
            $text = strtr($text, array(
                'January' => 'Januar', 'February' => 'Februar', 'March' => 'März',
                'May' => 'Mai', 'June' => 'Juni', 'July' => 'Juli',
                'October' => 'Oktober', 'December' => 'Dezember',
            ));
        }
        return $text;
    }

    /**
     * Replace German month names by English ones and drop the dot of a short
     * month name ("12. Dez. 2026" => "12. Dec 2026").
     */
    private static function english_month_names($value)
    {
        return preg_replace_callback('/\p{L}+\.?/u', function ($m) {
            $word = rtrim($m[0], '.');
            $key = mb_strtolower($word, 'UTF-8');
            if (isset(self::MONTH_NAMES[$key])) {
                return self::MONTH_NAMES[$key];
            }
            // "Dec." => "Dec" (a dot after a letter is never part of a format)
            return $word;
        }, $value);
    }
}
?>
