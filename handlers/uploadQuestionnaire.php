<?php
session_start();
if (!isset($_SESSION['Logged_StaffId'])) { header("location: /login.phtml"); die();	}
require_once (dirname(__FILE__) . "/../lib/db.php");
require_once (dirname(__FILE__) . "/../lib/class.staff.php");


if (
    !isset($_POST['manager']) ||
    strlen($_POST['manager']) < 2
) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Выберите `проект`"
    )));
}

//  проверка файла на ошибки
if (
    $_FILES['document']['error'] != 0
) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Ошибка: #" . $_FILES['document']['error']
    )));
}

//  проверка расширения файла
/* if (
    $_FILES['document']['type'] != "application/vnd.ms-excel" and
	$_FILES['document']['type'] != "application/download"
) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Расширение загружаемого файла " . $_FILES['document']['name'] . " должно быть .xls"
    )));
} */

//  проверка размера файла
if (
    $_FILES['document']['size'] <= 100
) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Слишком малый размер файла " . $_FILES['document']['name'] . ". Проверьте целостность данных!"
    )));
}
if (
    !isset($_FILES['document']['tmp_name']) ||
    empty($_FILES['document']['tmp_name']) ||
    !file_exists($_FILES['document']['tmp_name'])
) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "File not found"
    )));
}

require_once dirname(__FILE__) . '/../lib/excel_reader.php';

//  парсим файл
$data = array();
$data = new Spreadsheet_Excel_Reader($_FILES['document']['tmp_name']);
//echo PHP_EOL; print_r($data); echo PHP_EOL;
//die();
$data = (array) $data;

if (
    !isset($data['sheets'][0]['cells']) ||
    count($data['sheets'][0]['cells']) == 0
) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Ошибка обработки файла " . $_FILES['document']['name']
    )));
}

$deliv_kz = array(
    1    =>'AKSAI',
    2    =>'AKTAU',
    3    =>'AKTOBE',
    4    =>'ALMATA',
    5    =>'ASTANA-KURER',
    6    =>'ATYRAU',
    8    =>'Beineu',
    9    =>'EKIBASTUZ',
    10    =>'KARAGANDA',
    11    =>'KOKSHETAU',
    12    =>'KOSTANAI',
    13    =>'KYLSARY',
    14    =>'KYZYLORDA',
    15    =>'PAVLODAR',
    16    =>'PETROPAVLOVSK',
    18    =>'RUDNYI',
    19    =>'Saryagash',
    20    =>'SATPAEV',
    21    =>'SEMEI',
    22    =>'SHIMKENT',
    23    =>'TALDYKORGAN',
    24    =>'TARAZ',
    25    =>'TEMIRTAU',
    26    =>'TURKESTAN',
    27    =>'URALSK',
    28    =>'UST-KAMENOGORSK',
    29    =>'ZHANAOZEN',
    30    =>'Zhetysai',
    31    =>'ZHEZKAZGAN',
    32    =>'Почта',
    56    => 'KAPSHAGAI',
    86    => 'Hromtau',
    87    => 'Kandagash',
    88    => 'Kaskelen',
    89    => 'Uzynagash',
    90    => 'Talgar',
    91    => 'Balkhash',
    93    => 'Kentau',
    94    => 'Shieli',
    95    => 'Zharkent',
    97    => 'Merke',
    111   => 'Почтой',
    109   => 'Stepnogorsk',
    96    => 'Zhanakorgan',
    146   => 'Shu',
    144   => 'Toretam',
    143	  => 'Esik',
    142   => 'Shamalgan',
    145   => 'Aiyagoz',
    198 => 'Боровое',
    199 => 'Щучинск',
    200 => 'KORDAI',
    201 => 'Atbasar',
    202 => 'Shaxtinsk',
    203 => 'Saran',
    204 => 'Mangishlak',
    205 => 'Aksu',
    206 => 'Stepnogorsk',
    207 => 'RIDDER',
    208 => 'Zaisan',
    209 => 'Aksukent',
    210 => 'Arys',
    211 => 'Arkalyk',
    214 => 'Ushtobe',
    215 => 'Tenge',
    216 => 'Atakent',
    217 => 'Konyrat',
    218 => 'Tulkibas',
    219 => 'Turar',
    220 => 'Lenger',
    221 => 'Abay-Saryagash',
    222 => 'Abay-Zhetysai',
    223 => 'Karabulak',
    224 => 'Asykata',
    225 => 'Shardara',
    226 => 'Zhanaarka',
    227 => 'Lisakovsk',
    228 => 'Zyrianovsk',
    229 => 'Zhanatas',
    230 => 'Karatau',
    231 => 'Shamalgan stanciya'

);

$deliv_kg = array(
    34 => 'Бишкек курьер',
    35 => 'Каракол курьер',
    36 => 'Ош курьер',
    37 => 'Нарын курьер',
    38 => 'Кызылкия курьер',
    39 => 'Баткен курьер',
    40 => 'Талас курьер',
    41 => 'Карабалта курьер',
    42 => 'Токмок курьер',
    43 => 'Джалал-Абад курьер',
    44 => 'Узген',
    45 => 'Сокулык',
    46 => 'Базаркоргон',
    47 => 'Кант курьер',
    48 => 'Балыкчи курьер',
    49 => 'Новопокровка курьер',
    50 => 'Ивановка курьер',
    51 => 'Ноокат курьер',
    52 => 'Новопавловка курьер',
    53 => 'Чолпон курьер',
    54 => 'Бостари курьер',
    55 => 'Беловодское',
    85 => 'Майлуу-Суу почта',
    60 => 'Почта Киргизия'
);

$deliv_am = array(
    61 => 'Почта АРМ',
    62 => 'Ереван курьер'
);
$deliv_uz = array(
    154 => 'UZ TASHKENT',
    155 => 'UZ MEGA'
);

$data = $data['sheets'][0]['cells'];
//  удаление первой строки с заголовками
//array_shift($data);
//var_dump($data);
//die;

$cou = 0;
$db_form = array();
$item = 0;
$cur_ar = array('KAZ'=>'kz','RUS'=>'ru','KGZ'=>'kzg');
switch ($_POST['manager']) {
    case 'hotpartner':
    case 'obzvon':
        foreach ($data AS $dkey => $data_ar) {
            $item++;
            if($item==1 || strlen($data_ar[1]) == 0) continue;
            $db_form[$item]['ext_id'] =  mysql_real_escape_string($data_ar[1]);
            $db_form[$item]['fio'] =  mysql_real_escape_string($data_ar[2]);
            $db_form[$item]['offer'] = mysql_real_escape_string($data_ar[3]);
            $db_form[$item]['addr'] = mysql_real_escape_string($data_ar[9]).' '.mysql_real_escape_string($data_ar[4]);
            $db_form[$item]['phone'] = eregi_replace("([^0-9])","",$data_ar[5]);
            $db_form[$item]['price'] = "".(ceil((int)$data_ar[6]/10) * 10)."";
            $db_form[$item]['total_price'] = "".(ceil((int)$data_ar[6]/10) * 10)."";
            if(isset($data_ar[7]) && strlen($data_ar[7])>5) $db_form[$item]['date_delivery'] = date("Y-m-d H:i:s",strtotime($data_ar[7]));
            if(isset($data_ar[7]) && strlen($data_ar[7])>5) $db_form[$item]['date_delivery_first'] = date("Y-m-d H:i:s",strtotime($data_ar[7]));
            $db_form[$item]['index'] = isset($data_ar[8]) ? eregi_replace("([^0-9])","",$data_ar[8]) : null;
            $db_form[$item]['city'] =  isset($data_ar[9]) ? mysql_real_escape_string($data_ar[9]) : null;
            $db_form[$item]['kz_code'] =  isset($data_ar[10]) ? mysql_real_escape_string($data_ar[10]) : null;
            $db_form[$item]['status_kz'] =  'Свежий';
            if($_POST['manager']=='obzvon')  $db_form[$item]['status'] =  'новая';
            $db_form[$item]['fill_date'] =  isset($data_ar[11]) ? mysql_real_escape_string($data_ar[11]) : null;
            $db_form[$item]['kz_delivery'] =  isset($data_ar[13]) ? trim(mysql_real_escape_string($data_ar[13])) : null;
            $db_form[$item]['staff_id'] =  isset($data_ar[12]) ? mysql_real_escape_string($data_ar[12]) : null;
            $db_form[$item]['deliv_desc'] =  isset($data_ar[14]) ? mysql_real_escape_string($data_ar[14]) : null;
            $db_form[$item]['send_status'] =  'На отправку';
            $db_form[$item]['api_order'] =  2;
            $db_form[$item]['sale_option'] =  isset($data_ar[16]) ? mysql_real_escape_string($data_ar[16]) : null;
            if(in_array($db_form[$item]['kz_delivery'],$deliv_kz)) $db_form[$item]['country'] = 'kz';
            elseif(strlen($db_form[$item]['kz_delivery'])<2) $db_form[$item]['country'] = 'kz';
            elseif(in_array($db_form[$item]['kz_delivery'],$deliv_am)) $db_form[$item]['country'] = 'am';
            elseif(in_array($db_form[$item]['kz_delivery'],$deliv_uz)) $db_form[$item]['country'] = 'uz';
            else  $db_form[$item]['country'] = 'kg';
        }
        break;
    case 'new111':
        //var_dump($data); die;
        db_connect_baribarda();
        /*$is_q = mysql_query("SELECT phone FROM staff_order WHERE date> NOW() - INTERVAL 1 MONTH AND country = 'kz' GROUP BY phone",$db_baribarda);
        $phones = array();
        while($rezp = mysql_fetch_assoc($is_q)){
            $phones[] = $rezp['phone'];
        }*/
        //error_log(json_encode($phones)); die;
        foreach ($data AS $dkey => $data_ar) {

            $item++;
            //if($item==1 || strlen($data_ar[3]) == 0) continue;
            //if(in_array($data_ar[5],$phones)) continue;
            $db_form[$item]['ext_id'] =  rand(10000000,99999999);
            $db_form[$item]['fio'] =  mysql_real_escape_string($data_ar[1]);
            $db_form[$item]['offer'] = 'medical';
            $db_form[$item]['addr'] = '';
            $db_form[$item]['phone'] = eregi_replace("([^0-9])","",$data_ar[4]);
            $db_form[$item]['price'] = 1;
            $db_form[$item]['total_price'] = 1;
            //$db_form[$item]['index'] = isset($data_ar[8]) ? eregi_replace("([^0-9])","",$data_ar[8]) : null;
            //$db_form[$item]['city'] =  isset($data_ar[9]) ? mysql_real_escape_string($data_ar[9]) : null;
            //$db_form[$item]['kz_code'] =  isset($data_ar[10]) ? mysql_real_escape_string($data_ar[10]) : null;
            $db_form[$item]['status_kz'] =  'Свежий';
            $db_form[$item]['status'] =  'новая';
            //$db_form[$item]['fill_date'] =  isset($data_ar[11]) ? mysql_real_escape_string($data_ar[11]) : null;
            //$db_form[$item]['kz_delivery'] =  isset($data_ar[13]) ? mysql_real_escape_string($data_ar[13]) : null;
            $db_form[$item]['staff_id'] =  '47192915';
            //$db_form[$item]['birthday'] =  isset($data_ar[2]) ? date_format(strtotime($data_ar[2]),"Y-m-d") : null;
            $db_form[$item]['description'] = mysql_real_escape_string($data_ar[5]);
            //    $db_form[$item]['deliv_desc'] =  isset($data_ar[14]) ? mysql_real_escape_string($data_ar[14]) : null;
            $db_form[$item]['send_status'] =  'На отправку';
            //$db_form[$item]['api_order'] =  2;
            //$db_form[$item]['sale_option'] =  isset($data_ar[16]) ? mysql_real_escape_string($data_ar[16]) : null;
            $db_form[$item]['country'] = 'kz';
        }
        // die;
        break;
    case 'new111':
        db_connect_baribarda();

        foreach ($data AS $dkey => $data_ar) {

            $item++;
            if($item==1) continue;
            //if(in_array($data_ar[5],$phones)) continue;
            $db_form[$item]['ext_id'] =  mysql_real_escape_string($item);
            $db_form[$item]['fio'] =  mysql_real_escape_string($data_ar[1]);
            $db_form[$item]['offer'] = 'offer';
            $db_form[$item]['addr'] = mysql_real_escape_string('');
            $db_form[$item]['phone'] = '7'.substr(eregi_replace("([^0-9])","",$data_ar[2]),1,strlen(eregi_replace("([^0-9])","",$data_ar[2])));
            $db_form[$item]['price'] = 1;
            $db_form[$item]['total_price'] = 1;
            $db_form[$item]['index'] = isset($data_ar[18]) ? eregi_replace("([^0-9])","",$data_ar[18]) : null;
            $db_form[$item]['kz_code'] =  isset($data_ar[100]) ? mysql_real_escape_string($data_ar[100]) : null;
            $db_form[$item]['description'] = mysql_real_escape_string($data_ar[4]);
            $db_form[$item]['status_kz'] =  'Свежий';
            $db_form[$item]['status'] =  'Отменён';
            //$db_form[$item]['sex'] =  (($data_ar[6]=='Мужской') ? 1 : 2);
            // $db_form[$item]['birthday'] =  isset($data_ar[1]) ? date_format(strtotime($data_ar[1]),"Y-m-d") : null;
            $db_form[$item]['fill_date'] =  isset($data_ar[111]) ? mysql_real_escape_string($data_ar[11]) : null;
            $db_form[$item]['kz_delivery'] =  isset($data_ar[113]) ? mysql_real_escape_string($data_ar[13]) : null;
            $db_form[$item]['staff_id'] =  '57549493';
            $db_form[$item]['send_status'] =  'На отправку';
            $db_form[$item]['country'] = 'kz';
        }
        // die;
        break;
    case 'new':
        //db_connect_baribarda();
        foreach ($data AS $dkey => $data_ar) {

            $item++;
            if($item==1) continue;
            //var_dump($data_ar); die;
            //if(in_array($data_ar[5],$phones)) continue;
            $db_form[$item]['ext_id'] =  $item;
            $db_form[$item]['fio'] =  mysql_real_escape_string($data_ar[2]);
            $db_form[$item]['offer'] = 'offer';
            $db_form[$item]['addr'] = '';
            $db_form[$item]['description'] = mysql_real_escape_string($data_ar[4]);
            $db_form[$item]['phone'] = eregi_replace("([^0-9])","",$data_ar[1]);
            $db_form[$item]['price'] = 1;
            $db_form[$item]['total_price'] = 1;
            $db_form[$item]['index'] = isset($data_ar[6]) ? eregi_replace("([^0-9])","",$data_ar[6]) : null;
            $db_form[$item]['city'] =  isset($data_ar[3]) ? mysql_real_escape_string($data_ar[3]) : null;
            $db_form[$item]['kz_code'] =  isset($data_ar[10]) ? mysql_real_escape_string($data_ar[10]) : null;
            $db_form[$item]['status_kz'] =  'Свежий';
            $db_form[$item]['status'] =  'Отменён';
            $db_form[$item]['fill_date'] =  isset($data_ar[8]) ? mysql_real_escape_string($data_ar[8]) : null;
            $db_form[$item]['kz_delivery'] =  isset($data_ar[13]) ? trim(mysql_real_escape_string($data_ar[13])) : null;
            $db_form[$item]['staff_id'] =  '36710186';
            $db_form[$item]['send_status'] =  'На отправку';
            $db_form[$item]['country'] = 'kzg';
        }
        // die;
        break;
    case 'abc':
        foreach ($data AS $dkey => $data_ar) {
            $item++;
            //if($item<2) continue;
            $db_form[$item]['ext_id'] =  mysql_real_escape_string($data_ar[2]);
            $db_form[$item]['fio'] =  mysql_real_escape_string($data_ar[4]);
            $db_form[$item]['offer'] = mysql_real_escape_string($data_ar[12]);
            $db_form[$item]['addr'] = mysql_real_escape_string($data_ar[8]);
            $db_form[$item]['phone'] = '7'.substr(eregi_replace("([^0-9])","",$data_ar[10]),1,strlen(eregi_replace("([^0-9])","",$data_ar[10])));
            $db_form[$item]['price'] = "".(ceil((int)$data_ar[13]/10) * 10)."";
            $db_form[$item]['total_price'] = "".(ceil((int)$data_ar[13]/10) * 10)."";
            if(strlen($data_ar[10])>5) $db_form[$item]['date_delivery'] = date("Y-m-d H:i:s",strtotime($data_ar[11]));
            if(strlen($data_ar[10])>5) $db_form[$item]['date_delivery_first'] = date("Y-m-d H:i:s",strtotime($data_ar[11]));
            $db_form[$item]['index'] = eregi_replace("([^0-9])","",$data_ar[6]);
            $db_form[$item]['kz_code'] =  mysql_real_escape_string($data_ar[7]);
            $db_form[$item]['city_region'] =  mysql_real_escape_string($data_ar[9]);
            $db_form[$item]['status_kz'] =  'Упакован';
            $db_form[$item]['status_cur'] =  'УТОЧНИТЬ';
            $db_form[$item]['fill_date'] =  mysql_real_escape_string($data_ar[3]);
            $db_form[$item]['kz_delivery'] =  trim(mysql_real_escape_string(str_replace(array('г ','г.',' '),"",$data_ar[5])));
            $db_form[$item]['staff_id'] =  mysql_real_escape_string($data_ar[14]);
            $db_form[$item]['deliv_desc'] =  mysql_real_escape_string($data_ar[15]);
            $db_form[$item]['api_order'] =  2;
            //$db_form[$item]['country'] = 'kz';
            if(in_array($db_form[$item]['kz_delivery'],$deliv_kz)) $db_form[$item]['country'] = 'kz';
            elseif(in_array($db_form[$item]['kz_delivery'],$deliv_am)) $db_form[$item]['country'] = 'am';
            elseif(in_array($db_form[$item]['kz_delivery'],$deliv_uz)) $db_form[$item]['country'] = 'uz';
            else  $db_form[$item]['country'] = 'kg';
        }
        break;
    case 'obzvon_cold':
        foreach ($data AS $dkey => $data_ar) {
            $item++;
            if($item==1 || strlen($data_ar[1]) == 0) continue;
            $db_form[$item]['ext_id'] =  mysql_real_escape_string($data_ar[1]);
            $db_form[$item]['old_ext_id'] =  mysql_real_escape_string($data_ar[1]);
            $db_form[$item]['fio'] =  mysql_real_escape_string($data_ar[2]);
            $db_form[$item]['offer'] = mysql_real_escape_string($data_ar[3]);
            $db_form[$item]['addr'] = mysql_real_escape_string($data_ar[9]).' '.mysql_real_escape_string($data_ar[4]);
            $db_form[$item]['phone'] = eregi_replace("([^0-9])","",$data_ar[5]);
            $db_form[$item]['price'] = "".(ceil((int)$data_ar[6]/10) * 10)."";
            $db_form[$item]['total_price'] = "".(ceil((int)$data_ar[6]/10) * 10)."";
            if(isset($data_ar[7]) && strlen($data_ar[7])>5) $db_form[$item]['date_delivery'] = date("Y-m-d H:i:s",strtotime($data_ar[7]));
            if(isset($data_ar[7]) && strlen($data_ar[7])>5) $db_form[$item]['date_delivery_first'] = date("Y-m-d H:i:s",strtotime($data_ar[7]));
            $db_form[$item]['index'] = isset($data_ar[8]) ? eregi_replace("([^0-9])","",$data_ar[8]) : null;
            $db_form[$item]['city'] =  isset($data_ar[9]) ? mysql_real_escape_string($data_ar[9]) : null;
            $db_form[$item]['kz_code'] =  isset($data_ar[10]) ? mysql_real_escape_string($data_ar[10]) : null;
            $db_form[$item]['status_kz'] =  'Свежий';
            $db_form[$item]['status'] =  'новая';
            $db_form[$item]['fill_date'] =  isset($data_ar[11]) ? mysql_real_escape_string($data_ar[11]) : null;
            $db_form[$item]['kz_delivery'] =  isset($data_ar[13]) ? trim(mysql_real_escape_string($data_ar[13])) : null;
            $db_form[$item]['staff_id'] =  isset($data_ar[12]) ? mysql_real_escape_string($data_ar[12]) : null;
            $db_form[$item]['deliv_desc'] =  isset($data_ar[14]) ? mysql_real_escape_string($data_ar[14]) : null;
            $db_form[$item]['send_status'] =  'На отправку';
            $db_form[$item]['api_order'] =  2;
            $db_form[$item]['is_cold'] =  1;
            $db_form[$item]['sale_option'] =  isset($data_ar[16]) ? mysql_real_escape_string($data_ar[16]) : null;
            if(in_array($db_form[$item]['kz_delivery'],$deliv_kz)) $db_form[$item]['country'] = 'kz';
            elseif(strlen($db_form[$item]['kz_delivery'])<2) $db_form[$item]['country'] = 'kz';
            elseif(in_array($db_form[$item]['kz_delivery'],$deliv_am)) $db_form[$item]['country'] = 'am';
            elseif(in_array($db_form[$item]['kz_delivery'],$deliv_uz)) $db_form[$item]['country'] = 'uz';
            else  $db_form[$item]['country'] = 'kg';
        }
        break;
}

$queryс = " SELECT MAX(load_id) as max
			FROM staff_order
		WHERE 1" ;

$rsc = mysql_query($queryс);
$maxb = mysql_fetch_array($rsc);
$maxid = 1 + $maxb['max'];
$debug = array();

foreach ($db_form as $keys => $v_ar) {
    $sql = 'INSERT INTO `staff_order` SET ';

    foreach ($v_ar as $field => $fval) {
        $sql .= "`" . mysql_real_escape_string($field) . "` = '" . mysql_real_escape_string($fval) . "', ";
    }
    if (!isset($v_ar['send_status']))
        $sql .= "`send_status` = 'Отправлен', ";

    if($_POST['manager']=='new') {
        //$sql .= "`city` = '' ,";
        $sql .= " `Group` = '0' ";
        //echo($sql); die;
        $query = mysql_query($sql);
        continue;
        //die;
    }
    else $sql .= "`load_id` = '" . mysql_real_escape_string($maxid) . "' ";
    //continue;
    //die;
    if ($_POST['manager'] == 'abc') {
        $sql .= " ON DUPLICATE KEY UPDATE price='".$v_ar['price']."',  offer='".$v_ar['offer']."', date_delivery = CURDATE()+INTERVAL 1 day, status_kz = 'Упакован', status_cur = 'УТОЧНИТЬ' , status_check = '' ";
    }
    //echo $sql; die;
    if (mysql_query($sql)) {
        $cou++;
    }

    $debug[] = mysql_error();
}
if ($_POST['manager'] == 'obzvon_cold') {
    $query = 'update staff_order set ext_id = id where is_cold = 1 and `status` = \'новая\'';
    mysql_query($query);
}

print json_encode(array(
    "success" => TRUE,
    "msg" => 'Загружено ' . $cou . ' заказов',
    "debug" => $debug
));