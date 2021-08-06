<?php
session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
include_once (dirname(__FILE__) . "/../lib/db.php");
$project = $_GET['project'];
$offer = $_GET['offer'];
if ((int) @$_GET['log']) {
    $field = 'offer_logdesc';
} elseif ((int) $_GET['nocall']) {
    $field = 'offer_nocall';
} elseif ((int) $_GET['recall']) {
    $field = 'offer_recall';
} else {
    $field = 'offer_info';
}
$query = "SELECT offer_info, " . $field . " as offer_logdesc
            FROM offerInfo
            WHERE offer_name = '" . mysql_real_escape_string($offer) . "'
			AND country = '" . mysql_real_escape_string($project) . "'";
//echo $query; die;
$rs = mysql_query($query);
if ((int) $_GET['inf']) {
    $redis = RedisManager::getInstance()->getRedis();
    if (($promote = $redis->hGet('Promote', 0))) {
        $allpr = json_decode($promote, true);
        echo 'С этим товаром предлагают: <br>';
        asort($allpr[$offer]);
        foreach ($allpr[$offer] as $ak => $av) {
            echo @$_SESSION['offer_' . $ak] . '-> ' . $av . '<br>';
        }
    }
}
echo '<br>';
if ($rs) {
    $text = mysql_fetch_array($rs);
    if ((int) @$_GET['log']) {
        echo $text['offer_logdesc'];
    } elseif ((int) @$_GET['nocall']) {
        echo $text['offer_logdesc'];
    } elseif ((int) @$_GET['recall']) {
        echo $text['offer_logdesc'];
    } else {
        echo $text['offer_info'];
    }
} else {
    echo '';
}
