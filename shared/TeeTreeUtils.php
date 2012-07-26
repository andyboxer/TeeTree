<?php
/**
 * Copyright Anobii.com ltd 2011
 * No part of this software may be used, reproduced, copied or modified
 * for any purpose whatsoever without the express written consent of
 * the copyright holder. All rights reserved.
 *
 */

class TeeTreeUtils
{
    public static $conf = null;
    public static $pdo = null;
    public static $dbConfigName = null;
    public static $isbn_exclusions = null;
    public static $country_map = array(
						"AD"=>1,
						"AE"=>2,
						"AF"=>3,
						"AG"=>4,
						"AI"=>5,
						"AL"=>6,
						"AM"=>7,
						"AN"=>8,
						"AO"=>9,
						"AQ"=>10,
						"AR"=>11,
						"AS"=>12,
						"AT"=>13,
						"AU"=>14,
						"AW"=>15,
						"AX"=>16,
						"AZ"=>17,
						"BA"=>18,
						"BB"=>19,
						"BD"=>20,
						"BE"=>21,
						"BF"=>22,
						"BG"=>23,
						"BH"=>24,
						"BI"=>25,
						"BJ"=>26,
						"BL"=>27,
						"BM"=>28,
						"BN"=>29,
						"BO"=>30,
						"BQ"=>31,
						"BR"=>32,
						"BS"=>33,
						"BT"=>34,
						"BV"=>35,
						"BW"=>36,
						"BY"=>37,
						"BZ"=>38,
						"CA"=>39,
						"CC"=>40,
						"CD"=>41,
						"CF"=>42,
						"CG"=>43,
						"CH"=>44,
						"CI"=>45,
						"CK"=>46,
						"CL"=>47,
						"CM"=>48,
						"CN"=>49,
						"CO"=>50,
						"CR"=>51,
						"CS"=>52,
						"CU"=>53,
						"CV"=>54,
						"CW"=>55,
						"CX"=>56,
						"CY"=>57,
						"CZ"=>58,
						"DE"=>59,
						"DJ"=>60,
						"DK"=>61,
						"DM"=>62,
						"DO"=>63,
						"DZ"=>64,
						"EC"=>65,
						"EE"=>66,
						"EG"=>67,
						"EH"=>68,
						"ER"=>69,
						"ES"=>70,
						"ET"=>71,
						"FI"=>72,
						"FJ"=>73,
						"FK"=>74,
						"FM"=>75,
						"FO"=>76,
						"FR"=>77,
						"GA"=>78,
						"GB"=>79,
						"GD"=>80,
						"GE"=>81,
						"GF"=>82,
						"GG"=>83,
						"GH"=>84,
						"GI"=>85,
						"GL"=>86,
						"GM"=>87,
						"GN"=>88,
						"GP"=>89,
						"GQ"=>90,
						"GR"=>91,
						"GS"=>92,
						"GT"=>93,
						"GU"=>94,
						"GW"=>95,
						"GY"=>96,
						"HK"=>97,
						"HM"=>98,
						"HN"=>99,
						"HR"=>100,
						"HT"=>101,
						"HU"=>102,
						"ID"=>103,
						"IE"=>104,
						"IL"=>105,
						"IM"=>106,
						"IN"=>107,
						"IO"=>108,
						"IQ"=>109,
						"IR"=>110,
						"IS"=>111,
						"IT"=>112,
						"JE"=>113,
						"JM"=>114,
						"JO"=>115,
						"JP"=>116,
						"KE"=>117,
						"KG"=>118,
						"KH"=>119,
						"KI"=>120,
						"KM"=>121,
						"KN"=>122,
						"KP"=>123,
						"KR"=>124,
						"KW"=>125,
						"KY"=>126,
						"KZ"=>127,
						"LA"=>128,
						"LB"=>129,
						"LC"=>130,
						"LI"=>131,
						"LK"=>132,
						"LR"=>133,
						"LS"=>134,
						"LT"=>135,
						"LU"=>136,
						"LV"=>137,
						"LY"=>138,
						"MA"=>139,
						"MC"=>140,
						"MD"=>141,
						"ME"=>142,
						"MF"=>143,
						"MG"=>144,
						"MH"=>145,
						"MK"=>146,
						"ML"=>147,
						"MM"=>148,
						"MN"=>149,
						"MO"=>150,
						"MP"=>151,
						"MQ"=>152,
						"MR"=>153,
						"MS"=>154,
						"MT"=>155,
						"MU"=>156,
						"MV"=>157,
						"MW"=>158,
						"MX"=>159,
						"MY"=>160,
						"MZ"=>161,
						"NA"=>162,
						"NC"=>163,
						"NE"=>164,
						"NF"=>165,
						"NG"=>166,
						"NI"=>167,
						"NL"=>168,
						"NO"=>169,
						"NP"=>170,
						"NR"=>171,
						"NU"=>172,
						"NZ"=>173,
						"OM"=>174,
						"PA"=>175,
						"PE"=>176,
						"PF"=>177,
						"PG"=>178,
						"PH"=>179,
						"PK"=>180,
						"PL"=>181,
						"PM"=>182,
						"PN"=>183,
						"PR"=>184,
						"PS"=>185,
						"PT"=>186,
						"PW"=>187,
						"PY"=>188,
						"QA"=>189,
						"RE"=>190,
						"RO"=>191,
						"RS"=>192,
						"RU"=>193,
						"RW"=>194,
						"SA"=>195,
						"SB"=>196,
						"SC"=>197,
						"SD"=>198,
						"SE"=>199,
						"SG"=>200,
						"SH"=>201,
						"SI"=>202,
						"SJ"=>203,
						"SK"=>204,
						"SL"=>205,
						"SM"=>206,
						"SN"=>207,
						"SO"=>208,
						"SR"=>209,
						"ST"=>210,
						"SV"=>211,
						"SX"=>212,
						"SY"=>213,
						"SZ"=>214,
						"TC"=>215,
						"TD"=>216,
						"TF"=>217,
						"TG"=>218,
						"TH"=>219,
						"TJ"=>220,
						"TK"=>221,
						"TL"=>222,
						"TM"=>223,
						"TN"=>224,
						"TO"=>225,
						"TR"=>226,
						"TT"=>227,
						"TV"=>228,
						"TW"=>229,
						"TZ"=>230,
						"UA"=>231,
						"UG"=>232,
						"UM"=>233,
						"US"=>234,
						"UY"=>235,
						"UZ"=>236,
						"VA"=>237,
						"VC"=>238,
						"VE"=>239,
						"VG"=>240,
						"VI"=>241,
						"VN"=>242,
						"VU"=>243,
						"WF"=>244,
						"WS"=>245,
						"YE"=>246,
						"YT"=>247,
						"YU"=>248,
						"ZA"=>249,
						"ZM"=>250,
						"ZW"=>251);

    /**
     *
     * Helper function to translate a UTF-8 date into an SQL date
     * @param unknown_type $date - a UTF-8 formate date to translate
     */
    public static function convert_date_xml_to_sql($date)
    {
        $out_date = date_create_from_format('Ymd', substr($date, 0, 8));
        if(!$out_date) $out_date = date_create_from_format('Ym', substr($date, 0, 6));
        if($out_date) return $out_date->format('Y-m-d');
        return null;
    }

    public static function port_in_use($port)
    {
        $cmd = "netstat -nl -A inet | awk 'BEGIN {FS=\"[ :]+\"}{print $5}' | grep ". $port;
        $result = shell_exec($cmd);
        return strlen($result) > 0;
    }

    public static function get_date($date_string)
    {
        $date = date_create_from_format('Y-m-d', substr($date_string, 0, 10));
        if(!$date) $date = date_create_from_format('Ymd', substr($date_string, 0, 8));
        return $date;
    }

    public static function get_utf8_date(DateTime $date = null)
    {
        if($date === null)
        {
            $date = new DateTime();
        }
        $xml_date = $date->format('Ymd\THis');
        return $xml_date;
    }

    public static function sql_date_now()
    {
        $date = new DateTime();
        return $date->format('Y-m-d');
    }

    public static function sql_date_first()
    {
        return '0001-01-01';
    }

    public static function flatten_xml_values(&$xml, $flatten_node, $separator = ' ')
    {
        $flat_values = '';
        $nodes = $xml->xpath($flatten_node);
        foreach($nodes as $node)
        {
            $flat_values .= (string)$node. ' ';
        }
        return trim($flat_values, $separator);
    }

    public static function &fetch_node_xml(&$root, $node_name)
    {
        $node_xml = '';
        $nodes = $root->xpath($node_name);
        if(count($nodes) > 0)
        {
            $node_xml = '<COLLECTION>';
            foreach($nodes as &$node)
            {
                $node_xml .= $node->asXML();
            }
            $node_xml .= '</COLLECTION>';
        }
        return $node_xml;
    }

    public static function is_isbn_excluded($isbn)
    {
        $exclusions = self::isbn_exclusions();
        $excluded = $exclusions->xpath('Edition[ISBN= "'. trim($isbn). '"]');
        if(count($excluded) === 0)
        {
            return false;
        }
        else
        {
            return (string)$excluded[0]->ExclusionType;
        }
    }

    public static function sqlDate($timestamp = null)
    {
        if(is_nan($timestamp) || is_null($timestamp))
        $date = new DateTime($timestamp);
        else
        $date = new DateTime(date('Y-m-d H:i:s', $timestamp));
        $sqlDate = $date->format('Y-m-d H:i:s');
        return $sqlDate;
    }

    public static function currentLongDate($timestamp = null)
    {
        $date = new DateTime($timestamp);
        $date = $date->format('l jS F Y');
        return $date;
    }

    public static function indexed_copy($from, $to)
    {
        if(file_exists($to))
        {
            $index = 0;
            while(file_exists($to. "_". $index)) $index++;
        }
        return copy($from, $to);
    }

    public static function file_prepend($string, $filename, $temp_dir = './')
    {
        $context = stream_context_create();
        $fp = fopen($filename, 'r', 1, $context);
        $tmpname = $temp_dir. md5($string);
        file_put_contents($tmpname, $string);
        file_put_contents($tmpname, $fp, FILE_APPEND);
        fclose($fp);
        unlink($filename);
        rename($tmpname, $filename);
    }
}