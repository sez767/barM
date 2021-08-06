<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

$clientsObj = new ClientsObj();
$staffOrderObj = new StaffOrderObj();

if (!empty($_REQUEST['uuid'])) {

    $clientsObj->cSetId($_REQUEST['uuid']);
    $data = $clientsObj->cGetValues();
//    if (mb_strtoupper($data['country']) == 'KZG') {
//        $uuid = crc32(mb_strtoupper($data['country']) . mb_substr($data['phone'], -9));
//    } else {
//        $uuid = crc32(mb_strtoupper($data['country']) . mb_substr($data['phone'], -9));
//    }

    $data['orders'] = DB::query("SELECT * FROM {$staffOrderObj->cGetTableName()} WHERE uuid = %s", $_REQUEST['uuid']);

    $data['payed_count'] = $data['payed_total'] = 0;
    foreach ($data['orders'] as $orderItem) {
        if (array_key_exists($orderItem['offer'], $GLOBAL_OFFER_GROUP) /* && $orderItem['status'] == 'Подтвержден' && $orderItem['send_status'] == 'Оплачен' */) {
            $data['offer_group["' . $GLOBAL_OFFER_GROUP[$orderItem['offer']] . '"]'] = 1;
        }
        if ($orderItem['status'] == 'Подтвержден' && $orderItem['send_status'] == 'Оплачен') {
            $data['payed_count'] ++;
            $data['payed_total'] += $orderItem['total_price'];
        }
    }

    if (!empty($data['updated_by'])) {
        if (empty($GLOBAL_STAFF_RESPONSIBLE[$data['updated_by']])) {
            $data['responsible'] = 0;
        } else {
            $data['responsible'] = $GLOBAL_STAFF_RESPONSIBLE[$data['updated_by']];
        }

        if (empty($GLOBAL_RESPONSIBLE_CURATOR[$data['updated_by']])) {
            $data['curator'] = 0;
        } else {
            $data['curator'] = $GLOBAL_RESPONSIBLE_CURATOR[$data['updated_by']];
        }
    }

    $data['phone_orig'] = $data['phone'];
    if ($data['country'] == 'kz') {
        $data['phone'] = hide_phone($data['phone']);
    } elseif ($data['country'] == 'kzg') {
        $data['phone'] = '9' . hide_phone(substr($data['phone'], 1));
    }

    if (!empty($data['client_recall_date']) && $data['client_recall_date'] != '0000-00-00 00:00:00') {
        $dateExplode = explode(' ', $data['client_recall_date']);
        $data['client_recall_date'] = $dateExplode[0];
        $data['client_recall_time'] = $dateExplode[1];
    }

    $qs = 'SELECT COUNT(id) FROM staff_order WHERE status = "Подтвержден" AND send_status IN ("Отправлен", "Предоплата") AND uuid = %s';
    $data['have_orders'] = DB::queryFirstField($qs, $_REQUEST['uuid']);

    $ret = array(
        'success' => true,
        'data' => $data
    );
} else {

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
    $where = ' visible > 0 ';
    $where = ' 1 = 1 AND client_group IN (1, 2, 3, 4, 5, 6)';
    $where = ' 1 = 1';
//    $where = ' client_group IS NOT NULL ';

    if ($_SESSION['Logged_StaffId'] == 11111111) {
//        $where .= ' AND uuid  IN (69486, 3876, 111102)';
    }

    if (empty($_SESSION['admin']) &&
            ($_SESSION['operatorcold'] || $_SESSION['adminsales'] || array_key_exists($_SESSION['Logged_StaffId'], $GLOBAL_CURATOR_STAFF) || array_key_exists($_SESSION['Logged_StaffId'], $GLOBAL_RESPONSIBLE_STAFF))
    ) {

        $where .= ' AND client_group IS NOT NULL ';
        if (($settings = getClientsSettings())) {
            $where .= " AND country = '{$settings['country']}' AND client_group = {$settings['client_group']}";

            if (!array_key_exists($_SESSION['Logged_StaffId'], $GLOBAL_CURATOR_STAFF)) {

                $where .= " AND client_deal_status != 7";

                if (array_key_exists($_SESSION['Logged_StaffId'], $GLOBAL_RESPONSIBLE_STAFF)) {
                    $where .= " AND client_team = {$GLOBAL_STAFF_TEAM[$_SESSION['Logged_StaffId']]}";
                } elseif (($responsibeId = $GLOBAL_STAFF_RESPONSIBLE[$_SESSION['Logged_StaffId']])) {
                    $where .= " AND (client_team = {$GLOBAL_STAFF_TEAM[$responsibeId]})";
                    if (!empty($_SESSION['operatorcold'])) {
                        $where .= " AND (oper_use = {$_SESSION['staff_oper_use']})";
                        $where .= ' AND client_deal_status NOT IN (1, 3, 4, 5, 7)';
                    }
                }
            }
        }
    } else if (!empty($_SESSION['country'])) {
        $where .= " AND country IN (" . $_SESSION['country'] . ")";
    }

    $qs = '';
    $uuidsSearchAss = array();
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

            if ($field == 'responsible') {
                $fi = explode(',', $value);
                $tmpStaffArr = array();
                foreach ($fi as $fiItem) {

                    if ($fiItem < 0 && array_key_exists(abs($fiItem), $GLOBAL_CURATOR_STAFF)) {
                        $tmpStaffArr = array_merge($tmpStaffArr, $GLOBAL_CURATOR_STAFF[abs($fiItem)]);
                    } elseif (array_key_exists($fiItem, $GLOBAL_RESPONSIBLE_STAFF)) {
                        $tmpStaffArr = array_merge($tmpStaffArr, $GLOBAL_RESPONSIBLE_STAFF[$fiItem]);
                    }
                }
                if (!empty($tmpStaffArr)) {
                    $qs .= ' AND updated_by IN (' . implode(',', $tmpStaffArr) . ') ';
                } else {
                    $qs .= ' AND updated_by IN (-1) ';
                }
                continue;
            }

            if ($field == 'order_all_group') {
                $ofGroupsArr = explode(',', $value);
                $ofGroupInArr = array();
                foreach ($ofGroupsArr as $groupItem) {
                    $ofGroupInArr = array_merge($ofGroupInArr, $GLOBAL_GROUP_OFFER[$groupItem]);
                    if (empty($groupItem)) {
                        $ofGroupInArr[] = '0';
                    }
                }
                $where .= " AND MATCH (order_all_offer) AGAINST ('" . implode(" ", $ofGroupInArr) . "' IN BOOLEAN MODE)";
                continue;
            }

            if ($field == 'order_first_group') {
                $ofGroupsArr = explode(',', $value);
                $ofGroupInArr = array();
                foreach ($ofGroupsArr as $groupItem) {
                    $ofGroupInArr = array_merge($ofGroupInArr, $GLOBAL_GROUP_OFFER[$groupItem]);
                    if (empty($groupItem)) {
                        $ofGroupInArr[] = '0';
                    }
                }
                $where .= " AND BINARY order_first_offer IN ('" . implode("', '", $ofGroupInArr) . "')";
                continue;
            }
            if ($field == 'order_last_group') {
                $ofGroupsArr = explode(',', $value);
                $ofGroupInArr = array();
                foreach ($ofGroupsArr as $groupItem) {
                    $ofGroupInArr = array_merge($ofGroupInArr, $GLOBAL_GROUP_OFFER[$groupItem]);
                    if (empty($groupItem)) {
                        $ofGroupInArr[] = '0';
                    }
                }
                $where .= " AND BINARY order_last_offer IN ('" . implode("', '", $ofGroupInArr) . "')";
                continue;
            }

            switch ($filterType) {
                case 'string':
                    if (in_array($field, array('order_id'))) {
                        $fi = explode(' ', $value);

                        $valar = array();
                        foreach ($fi as $fiv) {
                            $valar[] = trim($fiv);
                        }
                        if (!empty($valar)) {
                            if (($uuidsSearchAss = DB::queryAssData('uuid', 'id', 'SELECT id, uuid FROM staff_order WHERE id IN %li', $valar))) {
                                $qs .= ' AND `uuid` IN ("' . implode('", "', array_keys($uuidsSearchAss)) . '")';
                            }
                        }
                    } else if (in_array($field, array('uuid'))) {
                        $fi = explode(' ', trim($value));

                        $valar = array();
                        foreach ($fi as $fiv) {
                            $valar[] = trim($fiv);
                        }
                        if (!empty($valar)) {
                            $qs .= ' AND `uuid` IN ("' . implode('", "', array_diff($valar, array(''))) . '")';
                        }
                    } else if ($field == 'phone') {
                        $phoneUuidStr = array();
                        foreach ($GLOBAL_COUNTRIES_ARR as $countryItem) {
                            $phoneUuidStr[] = mb_substr(md5(mb_strtoupper($countryItem) . mb_substr($value, -9)), 0, 16);
//                            $phoneUuidStr[] = mb_substr(md5(mb_strtoupper($countryItem) . mb_substr(hide_phone($value, 1), -9)), 0, 16);
                        }
                        $qs .= ' AND uuid IN ("' . implode('","', $phoneUuidStr) . '") ';
                    } else {
                        $qs .= " AND `$field` LIKE '" . $value . "%'";
                    }
                    Break;
                case 'list':
                    if ($value == 'Вся курьерка') {
                        $qs .= " AND order_kz_delivery != 'Почта' ";
                        continue;
                    }
                    $fi = explode(',', $value);

                    foreach ($fi as $fik => &$fiv) {
                        if (in_array($field, array('oper_use')) && $fiv == -1) {
                            $qs .= " AND `$field` > 0 ";
                            unset($fi[$fik]);
                        } elseif (in_array($field, array('updated_by')) && $fiv == -1) {
                            $qs .= " AND `$field` IS NULL ";
                            unset($fi[$fik]);
                        } elseif (in_array($field, array('client_group', 'client_team')) && $fiv == -1) {
                            $qs .= " AND (`$field` IS NULL OR `$field` = 0) ";
                            unset($fi[$fik]);
                        } else {
                            $fiv = "'$fiv'";
                        }
                    }
                    if (!empty($fi)) {
                        $qs .= " AND `$field` IN (" . implode(',', $fi) . ")";
                    }
                    Break;
                case 'boolean':
                    $qs .= " AND `$field` = " . ($value);
                    Break;
                case 'numeric':
                    switch ($compare) {
                        case 'eq':
                            $qs .= " AND `$field` = " . $value;
                            Break;
                        case 'lt':
                            $qs .= " AND `$field` < " . $value;
                            Break;
                        case 'gt':
                            $qs .= " AND `$field` > " . $value;
                            Break;
                    }
                    Break;
                case 'date':
                    switch ($compare) {
                        case 'eq':
                            $where .= " AND `$field` BETWEEN '" . date('Y-m-d', strtotime($value)) . "' AND '" . date('Y-m-d H:i:s', strtotime($value . ' + 86399 second')) . "'";
                            break;
                        case 'lt':
                            $where .= " AND `$field` <= '" . date('Y-m-d H:i:s', strtotime($value . ' + 86399 second')) . "'";
                            break;
                        case 'gt':
                            $where .= " AND `$field` >= '" . date('Y-m-d', strtotime($value)) . "'";
                            break;
                    }
                    Break;
            }
        }
        $where .= $qs;
    }
    //SQL_CALC_FOUND_ROWS
    $query = "  SELECT
                        *,
                        '' AS order_id,
                        phone AS phone_orig,
                        order_all_offer AS order_all_group,
                        order_first_offer AS order_first_group,
                        order_last_offer AS order_last_group
                        #IF (order_last_status NOT IN ('Отменён', 'Оплачен', 'Отказ') AND updated_by != {$_SESSION['Logged_StaffId']}, 7, client_deal_status) AS client_deal_status
                FROM {$clientsObj->cGetTableName()} WHERE $where";
    $queryTotal = "SELECT COUNT(uuid) FROM {$clientsObj->cGetTableName()} WHERE $where";

    if ($sort != '') {
        $query .= " ORDER BY $sort $dir";
    } else {
        $query .= ' ORDER BY created_at DESC';
    }
    $query .= " LIMIT $start, $count";
//   die($query);

    $data = DB::query($query);

    foreach ($data as &$dataItem) {
        if (!empty($dataItem['updated_by'])) {
            if (empty($GLOBAL_STAFF_RESPONSIBLE[$dataItem['updated_by']])) {
                $dataItem['responsible'] = 0;
            } else {
                $dataItem['responsible'] = $GLOBAL_STAFF_RESPONSIBLE[$dataItem['updated_by']];
            }

            if (empty($GLOBAL_RESPONSIBLE_CURATOR[$dataItem['responsible']])) {
                $dataItem['curator'] = 0;
            } else {
                $dataItem['curator'] = $GLOBAL_RESPONSIBLE_CURATOR[$dataItem['responsible']];
            }
        }

        if (!empty($dataItem['order_all_group'])) {
            $offerArr = explode(',', $dataItem['order_all_group']);
            $offerGroupsArr = array_diff(array_map('mOfferGroup', $offerArr), array(''));
            $dataItem['order_all_group'] = implode(', ', $offerGroupsArr);
        }
        if (!empty($dataItem['order_first_group'])) {
            $offerGroupsArr = array_diff(array_map('mOfferGroup', array($dataItem['order_first_group'])), array(''));
            $dataItem['order_first_group'] = implode(', ', $offerGroupsArr);
        }
        if (!empty($dataItem['order_last_group'])) {
            $offerGroupsArr = array_diff(array_map('mOfferGroup', array($dataItem['order_last_group'])), array(''));
            $dataItem['order_last_group'] = implode(', ', $offerGroupsArr);
        }

        $dataItem['phone_orig'] = preg_replace('/\D/', '', $dataItem['phone_orig']);
        if ($dataItem['country'] == 'kz') {
            $dataItem['phone'] = hide_phone($dataItem['phone']);
        } elseif ($dataItem['country'] == 'kzg') {
            $dataItem['phone'] = '9' . hide_phone(substr($dataItem['phone'], 1));
        }

        if (array_key_exists($dataItem['uuid'], $uuidsSearchAss)) {
            $dataItem['order_id'] = $uuidsSearchAss[$dataItem['uuid']];
        }

        $dataItem['rings'] = '<span id="call_' . $dataItem['uuid'] . '"><a href="#" onclick="showRingsGrid(\'' . $dataItem['uuid'] . '\',\'' . $dataItem['country'] . '\'); return false;">Показать</a></span>';
    }



    $ret = array(
        'data' => $data,
        'total' => '1000',
        'total' => DB::queryFirstField($queryTotal),
        'sql' => $query,
        'sqlTotal' => $queryTotal
    );
}

function mOfferGroup($n) {
    global $GLOBAL_OFFER_GROUP;
//    return array_key_exists($n, $GLOBAL_OFFER_GROUP) ? $GLOBAL_OFFER_GROUP[$n] : null;
    return empty($GLOBAL_OFFER_GROUP[$n]) ? null : $GLOBAL_OFFER_GROUP[$n];
}

echo json_encode($ret);
