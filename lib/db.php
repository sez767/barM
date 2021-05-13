<?php

ini_set("display_errors", 0);
error_reporting(E_ERROR);

ini_set('ignore_repeated_errors', 'On');

session_start();

if (in_array($_SESSION['Logged_StaffId'], array(11111111, 11119999)) && false) {
    error_reporting(E_ALL & ~(E_NOTICE | E_WARNING | E_STRICT));
}

// require_once dirname(__FILE__) . '/../ini/Application.php';

function db_connect() {
    global $db_link_ref;
    if (empty($db_link_ref)) {
        $Result = true;
        $db_link_ref = mysql_connect(Application::getAppConfig()->db->hostname, Application::getAppConfig()->db->username, Application::getAppConfig()->db->password);

        if ($db_link_ref) {
            mysql_query("set character_set_client='" . Application::getAppConfig()->db->encoding . "'");
            mysql_query("set character_set_results='" . Application::getAppConfig()->db->encoding . "'");
            mysql_query("set collation_connection='utf8_general_ci'");
            if (mysql_select_db(Application::getAppConfig()->db->database, $db_link_ref)) {
                // get User locale
                $_SESSION['UserLocale'] = 'ru';

                if (($tzOffset = getDBTimeZoneOfsset())) {
                    db_execute_query('SET time_zone = "' . $tzOffset . '"');
                    DB::query('SET time_zone = "' . $tzOffset . '"');
                }
            } else {
                print ("Can't open database. Ask your administrator.<BR>");
            }
        } else {
            $Result = false;
            print ("Can't connect to DB server. Ask your administrator.<BR>");
        }
    } else {
        $Result = true;
    }
    return $Result;
}

function db_close() {
    global $db_link_ref;
    if (!empty($db_link_ref)) {
        mysql_close();
    }
}

function db_execute_query($sql) {
    $ret = true;
    db_connect();
    $ret = mysql_query($sql) or $ret = false;
    return ($ret);
}

function asterisk_base() {
    global $ext_link;

    //$host = '92.46.122.98';
    $host = '45.8.116.20';
    $port = '3306';
    $dbname = 'asterisk';
    $username = 'crm';
    $password = 'offroad159753';
    $sqlcharset = 'utf8';

    $ext_link = mysql_connect("$host:$port", $username, $password);
    if ($ext_link) {
        mysql_query("set character_set_client='$sqlcharset'");
        mysql_query("set character_set_results='$sqlcharset'");
        mysql_query("set collation_connection='utf8_general_ci'");
    }
    $ext_db = mysql_select_db($dbname, $ext_link);

    DB::disconnect();
    DB::setEmptyMDB();
    DB::$host = $host;
    DB::$dbName = $dbname;
    DB::$user = $username;
    DB::$password = $password;
    DB::$encoding = $sqlcharset;

    if (($tzOffset = getDBTimeZoneOfsset())) {
        db_execute_query('SET time_zone = "' . $tzOffset . '"');
        DB::query('SET time_zone = "' . $tzOffset . '"');
    }

    return $ext_db;
}

function ket_asterisk_base() {
    global $ext3_link;

    $host = '192.168.0.111';
    $port = '3306';
    $dbname = 'coffee';
    $username = 'brdweb';
    $password = 'hjfHHyqomBVfgys2113g';
    $sqlcharset = 'utf8';

    if (($ret = checkHostPort($host, $port)) && $ret === true) {
        $ext3_link = mysql_connect("$host:$port", $username, $password);
        if ($ext3_link) {
            mysql_query("set character_set_client='$sqlcharset'");
            mysql_query("set character_set_results='$sqlcharset'");
            mysql_query("set collation_connection='utf8_general_ci'");
        }
        mysql_select_db($dbname, $ext3_link);
    }

    DB::disconnect();
    DB::setEmptyMDB();
    DB::$host = $host;
    DB::$port = $port;
    DB::$dbName = $dbname;
    DB::$user = $username;
    DB::$password = $password;
    DB::$encoding = $sqlcharset;

    return $ret;
}

function bari_base() {
    global $db_link_ref;
    $db_link_ref = mysql_connect(Application::getAppConfig()->db->hostname, Application::getAppConfig()->db->username, Application::getAppConfig()->db->password);
    if ($db_link_ref) {
        mysql_query("set character_set_client='" . Application::getAppConfig()->db->encoding . "'");
        mysql_query("set character_set_results='" . Application::getAppConfig()->db->encoding . "'");
        mysql_query("set collation_connection='utf8_general_ci'");
        $link_ref_db = mysql_select_db(database, $db_link_ref);
    }

    DB::disconnect();
    DB::setEmptyMDB();
    DB::$host = Application::getAppConfig()->db->hostname;
    DB::$dbName = Application::getAppConfig()->db->database;
    DB::$user = Application::getAppConfig()->db->username;
    DB::$password = Application::getAppConfig()->db->password;
    DB::$encoding = Application::getAppConfig()->db->encoding;

    return $link_ref_db;
}

if (!function_exists('my_mysqli_real_escape_string')) {

    function my_mysqli_real_escape_string($sql) {
        global $db_link_ref;
        return mysql_real_escape_string($sql, $db_link_ref);
    }

}

db_connect();
initGlobalVars();
