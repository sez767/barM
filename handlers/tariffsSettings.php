<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

// _bodyData
$bodyData = file_get_contents('php://input');
$bodyData = $bodyData ? json_decode($bodyData) : array();
if (is_object($bodyData)) {
    $bodyVars = get_object_vars($bodyData);
    $bodyData = array($bodyVars);
    $_REQUEST = array_merge($bodyVars, $_REQUEST);
}

//var_dump($_REQUEST);
//die;

$ret = array('success' => false);

switch ($_REQUEST['method']) {
    case 'read':
        $ret = readTariff();
        break;
    case 'insert':
        $ret = insertTariff();
        break;
    case 'update':
        $ret = updateTariff();
        break;
    case 'delete':
        $ret = deleteTariff();
        break;
}

function readTariff() {

// collect request parameters
    $start = (int) isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
    $count = (int) isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 20;
    $sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : '';
    $dir = isset($_REQUEST['dir']) ? $_REQUEST['dir'] : 'DESC';
    $filters = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
    $sort = mysql_real_escape_string($sort);
    $dir = mysql_real_escape_string($dir);
// GridFilters sends filters as an Array if not json encoded
    if (is_array($filters)) {
        $encoded = false;
    } else {
        $encoded = true;
        $filters = json_decode($filters);
    }
// initialize variables
    $where = ' deleted_at IS NULL ';
    $qs = '';
// loop through filters sent by client
    if (is_array($filters)) {
        for ($i = 0; $i < count($filters); $i++) {
            $filter = $filters[$i];
            // assign filter data (location depends if encoded or not)
            if ($encoded) {
                $field = $filter->field;
                $value = $filter->value;
                $compare = isset($filter->comparison) ? $filter->comparison : null;
                $filterType = $filter->type;
            } else {
                $field = $filter['field'];
                $value = $filter['data']['value'];
                $compare = isset($filter['data']['comparison']) ? $filter['data']['comparison'] : null;
                $filterType = $filter['data']['type'];
            }
            $field = mysql_real_escape_string($field);
            $value = mysql_real_escape_string($value);
            $compare = mysql_real_escape_string($compare);
            $filterType = mysql_real_escape_string($filterType);

            switch ($filterType) {
                case 'string':
                    $qs .= " AND `$field` LIKE '" . $value . "%'";
                    break;
                case 'list':
                    if (strstr($value, ',')) {
                        $fi = explode(',', $value);
                        for ($q = 0; $q < count($fi); $q++) {
                            $fi[$q] = "'" . $fi[$q] . "'";
                        }
                        $value = implode(',', $fi);
                        $qs .= " AND `$field` IN (" . $value . ")";
                    } else {
                        $qs .= " AND `$field` = '" . $value . "'";
                    }
                    break;
                case 'boolean':
                    $qs .= " AND `$field` = " . ($value);
                    break;
                case 'numeric':
                    switch ($compare) {
                        case 'eq':
                            $qs .= " AND `$field` = " . $value;
                            break;
                        case 'lt':
                            $qs .= " AND `$field` < " . $value;
                            break;
                        case 'gt':
                            $qs .= " AND `$field` > " . $value;
                            break;
                    }
                    break;
                case 'date':
                    switch ($compare) {
                        case 'eq':
                            $qs .= " AND `$field` >= '" . strtotime($value) . "' AND `$field` <= '" . substr($value, 0, 9) . " 23:59:59'";
                            break;
                        case 'lt':
                            $qs .= " AND `$field` < '" . strtotime($value) . "'";
                            break;
                        case 'gt':
                            $qs .= " AND `$field` > '" . strtotime($value) . "'";
                            break;
                    }
                    break;
            }
        }
        $where .= $qs;
    }
    $query = "SELECT SQL_CALC_FOUND_ROWS * FROM tariff_settings WHERE $where";

    if (empty($sort)) {
        $query .= " ORDER BY id DESC ";
    } else {
        $query .= " ORDER BY $sort $dir";
    }
    $query .= " LIMIT $start, $count";

    $ret = array(
        'success' => true,
        'data' => DB::query($query),
        'total' => DB::queryFirstField('SELECT FOUND_ROWS()'),
    );
    return $ret;
}

function insertTariff() {
    $updateArr = array(
        'date_start' => DB::sqlEval('NOW()')
    );
    DB::insert('tariff_settings', $updateArr);
    return array(
        'success' => true,
        'message' => 'Все четенько',
        'data' => DB::queryOneRow('SELECT * FROM tariff_settings ORDER BY id DESC')
    );
}

function updateTariff() {
    $updateArr = array(
        'country' => $_REQUEST['country'],
        'deliv_type' => $_REQUEST['deliv_type'],
        'staff_id' => $_REQUEST['staff_id'],
        'date_start' => $_REQUEST['date_start'],
        'column' => $_REQUEST['column'],
        'percent' => $_REQUEST['percent']
    );
    DB::update('tariff_settings', $updateArr, 'id = %i', $_REQUEST['id']);

    $memcache = MemcacheManager::getInstance()->getMemcache();
    $memcache->delete(md5('GLOBAL_TARIFF_SETTINGS'));

    return array('success' => true);
}

function deleteTariff() {
    $updateArr = array(
        'deleted_at' => DB::sqlEval('NOW()'),
        'deleted_by' => $_SESSION['Logged_StaffId']
    );
    DB::update('tariff_settings', $updateArr, 'id = %i', $_REQUEST['id']);

    $memcache = MemcacheManager::getInstance()->getMemcache();
    $memcache->delete(md5('GLOBAL_TARIFF_SETTINGS'));

    return array('success' => true);
}

echo json_encode($ret);
