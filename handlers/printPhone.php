<?php

ob_end_clean();
session_start();

require_once dirname(__FILE__) . '/../lib/CommonManagers.php';
$redis = RedisManager::getInstance()->getRedis();

$t_ar = $redis->hGetAll('black_list');
//if (!isset($_SESSION['Logged_StaffId'])) { header("location: /login.html"); die();	}
require_once dirname(__FILE__) . '/../lib/db.php';
$query = "SELECT * FROM staff_order
				WHERE  id IN (" . substr($_GET['id'], 0, strlen($_GET['id']) - 1) . ")
				ORDER BY offer,package ";
//echo $query; die;
$colors = array(
    'Черный' =>
    array('text' => 'white', 'back' => 'black', 'cover' => 'black'),
    'Чёрный' =>
    array('text' => 'white', 'back' => 'black', 'cover' => 'black'),
    'Белый' =>
    array('text' => 'black', 'back' => 'white', 'cover' => 'white'),
    'Розовый' =>
    array('text' => 'black', 'back' => 'pink', 'cover' => 'white'),
);
$rs = mysql_query($query);

$out_html = ' <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
        <title>title</title>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<style>
            .ip5_2d {width: 125mm; height: 60mm;}
            .ip5_2d table .source{width: 91mm; height: 52mm; max-width: 94mm; padding: 0;}
            .ip5_2d table .lBottom{width: 17mm}
            .ip5_2d table .rBottom{width: 5mm}

			.ip6_2d {width: 135mm; height: 65mm;}
            .ip6_2d table .source{width: 112mm; height: 55mm; max-width: 112mm; padding: 0;}
            .ip6_2d table .lBottom{width: 19mm}
            .ip6_2d table .rBottom{width: 5mm}

			.ip6plus_2d {width: 156mm; height: 76mm;}
            .ip6plus_2d table .source{width: 135mm; height: 65mm; max-width: 132mm; padding: 0;}
            .ip6plus_2d table .lBottom{width: 19mm}
            .ip6plus_2d table .rBottom{width: 5mm}

			.smgS4_2d {width: 132mm; height: 65mm;}
            .smgS4_2d table .source{width: 93mm; height: 51mm; max-width: 90mm; padding: 0;}
            .smgS4_2d table .lBottom{width: 26mm}
            .smgS4_2d table .rBottom{width: 5mm}

			.smgS5_2d {width: 132mm; height: 67mm;}
            .smgS5_2d table .source{width: 93mm; height: 55mm; max-width: 90mm; padding: 0;}
            .smgS5_2d table .lBottom{width: 26mm}
            .smgS5_2d table .rBottom{width: 5mm}

			.smgS3_2d {width: 130mm; height: 63mm;}
            .smgS3_2d table .source{width: 93mm; height: 51mm; max-width: 90mm; padding: 0;}
            .smgS3_2d table .lBottom{width: 26mm}
            .smgS3_2d table .rBottom{width: 5mm}

			.ip4_2d {width: 115mm; height: 60mm;}
            .ip4_2d table .source{width: 92mm; height: 53mm; max-width: 90mm; padding: 0;}
            .ip4_2d table .lBottom{width: 17mm}
            .ip4_2d table .rBottom{width: 5mm}
			table img{
                padding: 0;
                width: auto;
                height: inherit;
                max-width: inherit;
            }
            .maket table,
            .itemsColor div{
                background-size: 100%;
                background-position: 50%;
            }

            .maket {
                padding: 1px 0 0 0;
                z-index: 2;
                display: inline-flex;
            }
            .maket > div:first-child{
                margin: 10px 0 15px 10px;
            }
            .maket table{
                text-align: center;
                border-collapse: collapse;
                width: 100%;
                height: 100%;
                margin: 0;
                border: 0;
            }
            .maket table td{
                border: 0;
            }
            .maket table tr{
                border: 0;
            }
            .maket > div:first-child{
                position: relative;
            }
            .maket .cover{
                position: absolute;
                height: inherit;
                width: inherit;
            }
            .maket .source img{
                cursor: move;
                z-index: 100;
            }
            .maket{
                cursor: pointer
            }
			.cover{transform: scale(1, -1);}
			.ui-draggable {transform: scale(1, -1);}
        </style>

		</head><body><div style="width:700px;">';
$model = array(
    'iPhone 4 / 4s' => 'ip4',
    'iPhone 5 / 5s' => 'ip5',
    'iPhone 5c' => 'ip5c',
    'iPhone 6' => 'ip6',
    'iPhone 6+' => 'ip6plus',
    'Samsung Galaxy s3' => 'smgS3',
    'Samsung Galaxy s4' => 'smgS4',
    'Samsung Galaxy s4 mini' => 'smgS4mini',
    'Samsung Galaxy s5' => 'smgS5',
    'Samsung Galaxy s6' => 'smgS6',
    'Samsung note 2' => 'smgNot2',
    'Sony xperia Z2' => 'sonyXperiaZ2',
    'Sony xperia Z3' => 'sonyXperiaZ3',
    'Sony Xperia C4' => 'sonyXperiaC4',
    'LG G2' => 'lgG2',
    'LG G3' => 'lgG3',
    'LG Optimus L5' => 'lgL5',
    'LG Optimus L70' => 'lgL70',
    'HTS desire M8' => 'htcM8',
    'HTS desire M9' => 'htcM9',
    'Nokia lumia X2' => 'nokiaLumiaX2',
    'Lenovo S 850' => 'lenovoS850',
);
while ($obj = mysql_fetch_assoc($rs)) {
//var_dump($obj['vendor']); //die;
//                                <img id="yw1" class="ui-draggable" src="http://baribarda.com/maket/img.php?color='.$colors[@$tmp_dop['color'][$ke]]['text'].'&text='.@$tmp_dop['name'][$ke].'&background='.$colors[@$tmp_dop['color'][$ke]]['back'].'&font=arial_bold&rotate=0" style="position: relative;">
    if (isJson($obj['dop_tovar'])) {
        $tmp_dop = (array) json_decode($obj['dop_tovar']);
        foreach ($tmp_dop['dop_tovar'] as $ke => $va) {
            if ($va == 'luxury-case') {
                $out_html .= '<br> ' . $obj['id'] . ' ' . @$tmp_dop['vendor'][$ke] . ' - ' . @$tmp_dop['color'][$ke] . '<br>
	<div class="maket" >
            <div class="' . $model[@$tmp_dop['vendor'][$ke]] . '_2d" style="background-color:' . $colors[@$tmp_dop['color'][$ke]]['back'] . ';">
                <img class="cover" src="http://baribarda.com/images/covers/' . $model[@$tmp_dop['vendor'][$ke]] . '_2d' . $colors[@$tmp_dop['color'][$ke]]['cover'] . '.png">
                <table class="Bczemfira">
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="lBottom"></td>
                            <td class="source ">
                                <img id="yw1" class="ui-draggable" src="http://baribarda.com/maket/img.php?color=' . $colors[@$tmp_dop['color'][$ke]]['text'] . '&text=' . @$tmp_dop['name'][$ke] . '&font=arial_bold&rotate=0" style="position: relative;">
                            </td>
                            <td class="rBottom"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
';
            }
        }
    }
    if (!isJson($obj['other_data']))
        continue;
//                                <img id="yw1" class="ui-draggable" src="http://baribarda.com/maket/img?color='.$colors[$attr_ar['color']]['text'].'&text='.$attr_ar['name'].'&background='.$colors[$attr_ar['color']]['back'].'&font=arial_bold&rotate=0" style="position: relative;">
    $attr_ar = (array) json_decode($obj['other_data']);
    if (!isset($attr_ar['vendor']))
        continue;
//var_dump($attr_ar); die;
    $out_html .= ' <br> ' . $obj['id'] . ' ' . @$attr_ar['vendor'] . ' - ' . @$attr_ar['color'] . '<br>
	<div class="maket" >
            <div class="' . $model[@$attr_ar['vendor']] . '_2d" style="background-color:' . $colors[$attr_ar['color']]['back'] . ';">
                <img class="cover" src="http://baribarda.com/images/covers/' . $model[@$attr_ar['vendor']] . '_2d' . $colors[$attr_ar['color']]['cover'] . '.png">
                <table class="Bczemfira">
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="lBottom"></td>
                            <td class="source ">
                                <img id="yw1" class="ui-draggable" src="http://baribarda.com/maket/img.php?color=' . $colors[$attr_ar['color']]['text'] . '&text=' . $attr_ar['name'] . '&font=arial_bold&rotate=0" style="position: relative;">
                            </td>
                            <td class="rBottom"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
';
}
$out_html .= '</body>';
echo $out_html;
