<?php
/*
 * Persian (Jalali) Date Functions
 * Based on standard Jalali calendar conversion
 */

// Persian months
$persian_months = [
    1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
    'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
];

// Persian weekdays
$persian_weekdays = [
    'شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه'
];

function jdate($format, $timestamp = null, $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa') {
    $T_sec = 0;
    if ($timestamp === null) {
        $timestamp = time();
    } elseif ($timestamp === '') {
        return '';
    } else {
        $T_sec = $timestamp % 60;
        $timestamp = $timestamp - $T_sec;
    }
    
    $date = explode('_', date('H_i_j_n_Y', $timestamp));
    list($j_y, $j_m, $j_d) = gregorian_to_jalali($date[4], $date[3], $date[2]);
    
    $doy = ($j_m < 7) ? (($j_m - 1) * 31) + $j_d : (($j_m - 7) * 30) + $j_d + 186;
    $kab = (((($j_y % 33) % 4) - 1 == (int)(($j_y % 33) * 0.05)) ? 1 : 0);
    $kam = ((($j_y % 128) > 29) ? 1 : 0);
    $kab = (($kam == 1 && $kab == 1) ? 0 : $kab);
    $days = ($j_m > 6) ? $j_d : $j_d + (($j_m - 1) * 31);
    $sal_a = ($j_m > 6) ? 186 + (($j_m - 6) * 30) : (($j_m - 1) * 31);
    
    $out = '';
    for ($i = 0; $i < strlen($format); $i++) {
        $sub = substr($format, $i, 1);
        switch ($sub) {
            case 'Y': case 'y':
                $out .= ($sub == 'Y') ? $j_y : substr($j_y, 2, 2);
                break;
            case 'm': case 'n':
                $out .= ($sub == 'm') ? sprintf('%02d', $j_m) : $j_m;
                break;
            case 'd': case 'j':
                $out .= ($sub == 'd') ? sprintf('%02d', $j_d) : $j_d;
                break;
            case 'g': case 'G': case 'h': case 'H':
                $out .= $date[0 + ($sub == 'g' || $sub == 'h' ? 1 : 0)];
                break;
            case 'i':
                $out .= $date[1];
                break;
            case 's':
                $out .= sprintf('%02d', $T_sec);
                break;
            case 'a': case 'A':
                $out .= (intval($date[0]) < 12) ? (($sub == 'a') ? 'am' : 'AM') : (($sub == 'a') ? 'pm' : 'PM');
                break;
            case 'w':
                $out .= ((date('w', $timestamp) + 1) % 7);
                break;
            case 'N':
                $out .= date('w', $timestamp) + 1;
                break;
            case 'S':
                $out .= 'ام';
                break;
            case 'F':
                global $persian_months;
                $out .= $persian_months[$j_m];
                break;
            case 'M':
                global $persian_months;
                $out .= substr($persian_months[$j_m], 0, 6);
                break;
            case 'l':
                global $persian_weekdays;
                $out .= $persian_weekdays[date('w', $timestamp)];
                break;
            case 'D':
                global $persian_weekdays;
                $out .= substr($persian_weekdays[date('w', $timestamp)], 0, 5);
                break;
            case 'z':
                $out .= $doy;
                break;
            case 't':
                $out .= ($j_m <= 6) ? 31 : (($j_m == 12 && $kab == 0) ? 29 : 30);
                break;
            case 'L':
                $out .= $kab;
                break;
            case 'o': case 'B': case 'u': case 'I': case 'O': case 'P': case 'T': case 'Z': case 'c': case 'r': case 'U':
                $out .= date($sub, $timestamp);
                break;
            default:
                $out .= $sub;
        }
    }
    
    return ($tr_num != 'en') ? farsi_num($out, $tr_num) : $out;
}

function jstrtotime($str_date, $timestamp = null) {
    if ($timestamp === null) {
        $timestamp = time();
    }
    
    $str_date = trim($str_date, ' ');
    $str_date = str_replace(['/', '-', ' '], ['/', '/', '/'], $str_date);
    
    if (strpos($str_date, '/') !== false) {
        $date_parts = explode('/', $str_date);
        if (count($date_parts) == 3) {
            list($j_y, $j_m, $j_d) = $date_parts;
            list($g_y, $g_m, $g_d) = jalali_to_gregorian($j_y, $j_m, $j_d);
            return mktime(0, 0, 0, $g_m, $g_d, $g_y);
        }
    }
    
    return strtotime($str_date, $timestamp);
}

function gregorian_to_jalali($gy, $gm, $gd) {
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $jy = ($gy <= 1600) ? 0 : 979;
    $gy -= ($gy <= 1600) ? 621 : 1600;
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = (365 * $gy) + ((int)(($gy2 + 3) / 4)) + ((int)(($gy2 + 99) / 100)) - ((int)(($gy2 + 399) / 400)) - 80 + $gd + $g_d_m[$gm - 1];
    $jy += 33 * ((int)($days / 12053));
    $days %= 12053;
    $jy += 4 * ((int)($days / 1461));
    $days %= 1461;
    $jy += (int)(($days - 1) / 365);
    if ($days > 365) $days = ($days - 1) % 365;
    $jp = 0;
    for ($i = 0; $i < 6 && $days >= 31; $i++) {
        $days -= 31;
        $jp++;
    }
    for ($i = 6; $i < 12 && $days >= 30; $i++) {
        $days -= 30;
        $jp++;
    }
    $jd = $days + 1;
    return [$jy, ++$jp, $jd];
}

function jalali_to_gregorian($jy, $jm, $jd) {
    $jy += 1595;
    $days = 365 * $jy + ((int)($jy / 33)) * 8 + (int)((($jy % 33) + 3) / 4) + 78 + $jd + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);
    $gy = 400 * ((int)($days / 146097));
    $days %= 146097;
    $leap = true;
    if ($days >= 36525) {
        $days--;
        $gy += 100 * ((int)($days / 36524));
        $days %= 36524;
        if ($days >= 365) $days++;
        else $leap = false;
    }
    $gy += 4 * ((int)($days / 1461));
    $days %= 1461;
    $gy += (int)(($days - 1) / 365);
    if ($days > 365) $days = ($days - 1) % 365;
    $gd = $days + 1;
    $sal_a = [0, 31, (($leap && (($gy % 100) != 0)) || (($gy % 400) == 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $gm = 0;
    for ($gm = 0; $gm < 13 && $gd > $sal_a[$gm]; $gm++) $gd -= $sal_a[$gm];
    return [$gy, $gm, $gd];
}

function farsi_num($str, $mod = 'fa') {
    $num_a = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $key_a = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return ($mod == 'fa') ? str_replace($num_a, $key_a, $str) : str_replace($key_a, $num_a, $str);
}

// Helper functions for easier use
function persian_date($format = 'Y/m/d', $timestamp = null) {
    return jdate($format, $timestamp);
}

function persian_to_gregorian($persian_date) {
    $parts = explode('/', $persian_date);
    if (count($parts) != 3) return date('Y-m-d');
    
    list($j_y, $j_m, $j_d) = $parts;
    list($g_y, $g_m, $g_d) = jalali_to_gregorian($j_y, $j_m, $j_d);
    
    return sprintf('%04d-%02d-%02d', $g_y, $g_m, $g_d);
}

function gregorian_to_persian($gregorian_date) {
    $timestamp = strtotime($gregorian_date);
    return jdate('Y/m/d', $timestamp);
}
?>