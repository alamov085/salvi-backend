<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 09.07.2020
 * Time: 14:28
 */

namespace api\services;


use XLSXWriter;

class XLSXWriterRewrite extends XLSXWriter
{
    public function __construct($tempdir = '')
    {
        $this->tempdir = $tempdir;
        defined('ENT_XML1') or define('ENT_XML1', 16);//for php 5.3, avoid fatal error
        date_default_timezone_get() or date_default_timezone_set('UTC');//php.ini missing tz, avoid warning
        is_writeable($this->tempFilename()) or self::log("Warning: tempdir " . sys_get_temp_dir() . " not writeable, use ->setTempDir()");
        class_exists('ZipArchive') or self::log("Error: ZipArchive class does not exist");
        $this->addCellStyle($number_format = 'GENERAL', $style_string = null);
    }

    private function addCellStyle($number_format, $cell_style_string)
    {
        $number_format_idx = self::add_to_list_get_index($this->number_formats, $number_format);
        $lookup_string = $number_format_idx . ";" . $cell_style_string;
        $cell_style_idx = self::add_to_list_get_index($this->cell_styles, $lookup_string);
        return $cell_style_idx;
    }

    private function initializeColumnTypes($header_types)
    {
        $column_types = array();
        foreach ($header_types as $v) {
            $number_format = self::numberFormatStandardized($v);
            $number_format_type = self::determineNumberFormatType($number_format);
            $cell_style_idx = $this->addCellStyle($number_format, $style_string = null);
            $column_types[] = array('number_format'      => $number_format,//contains excel format like 'YYYY-MM-DD HH:MM:SS'
                                    'number_format_type' => $number_format_type, //contains friendly format like 'datetime'
                                    'default_cell_style' => $cell_style_idx,
            );
        }
        return $column_types;
    }

    private static function determineNumberFormatType($num_format)
    {
        $num_format = preg_replace("/\[(Black|Blue|Cyan|Green|Magenta|Red|White|Yellow)\]/i", "", $num_format);
        if ($num_format == 'GENERAL') return 'n_auto';
        if ($num_format == '@') return 'n_string';
        if ($num_format == '0') return 'n_numeric';
        if (preg_match('/[H]{1,2}:[M]{1,2}(?![^"]*+")/i', $num_format)) return 'n_datetime';
        if (preg_match('/[M]{1,2}:[S]{1,2}(?![^"]*+")/i', $num_format)) return 'n_datetime';
        if (preg_match('/[Y]{2,4}(?![^"]*+")/i', $num_format)) return 'n_date';
        if (preg_match('/[D]{1,2}(?![^"]*+")/i', $num_format)) return 'n_date';
        if (preg_match('/[M]{1,2}(?![^"]*+")/i', $num_format)) return 'n_date';
        if (preg_match('/$(?![^"]*+")/', $num_format)) return 'n_numeric';
        if (preg_match('/%(?![^"]*+")/', $num_format)) return 'n_numeric';
        if (preg_match('/0(?![^"]*+")/', $num_format)) return 'n_numeric';
        return 'n_auto';
    }

    //------------------------------------------------------------------
    private static function numberFormatStandardized($num_format)
    {
        if ($num_format == 'money') {
            $num_format = 'dollar';
        }
        if ($num_format == 'number') {
            $num_format = 'integer';
        }

        if ($num_format == 'string') $num_format = '@';
        else if ($num_format == 'integer') $num_format = '0';
        else if ($num_format == 'date') $num_format = 'YYYY-MM-DD';
        else if ($num_format == 'datetime') $num_format = 'YYYY-MM-DD HH:MM:SS';
        else if ($num_format == 'time') $num_format = 'HH:MM:SS';
        else if ($num_format == 'price') $num_format = '#,##0.00';
        else if ($num_format == 'dollar') $num_format = '[$$-1009]#,##0.00;[RED]-[$$-1009]#,##0.00';
        else if ($num_format == 'euro') $num_format = '#,##0.00 [$€-407];[RED]-#,##0.00 [$€-407]';
        $ignore_until = '';
        $escaped = '';
        for ($i = 0, $ix = strlen($num_format); $i < $ix; $i++) {
            $c = $num_format[$i];
            if ($ignore_until == '' && $c == '[')
                $ignore_until = ']';
            else if ($ignore_until == '' && $c == '"')
                $ignore_until = '"';
            else if ($ignore_until == $c)
                $ignore_until = '';
            if ($ignore_until == '' && ($c == ' ' || $c == '-' || $c == '(' || $c == ')') && ($i == 0 || $num_format[$i - 1] != '_'))
                $escaped .= "\\" . $c;
            else
                $escaped .= $c;
        }
        return $escaped;
    }
}
