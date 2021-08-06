<?php

require_once dirname(__FILE__) . "/../lib/db.php";

/**
 * @author dob
 */
class HandlerHistory extends CrmHandlerBase {

    /**
     * @var HistoryObj
     */
    protected $mainObj;

    function __construct() {

        parent::__construct();

        $this->mainObj = new ActionHistoryObj($_REQUEST['id']);

        $this->doResponse();
    }

    public function read() {
        // initialize variables

        $time_1 = microtime(true);

        $havingFields = array();
        $strongLikeFields = array('object_id', 'property');
        $havingArr = array();

        $whereArr = array("`property` NOT IN ('phone', 'phone_sms')");
        // loop through filters sent by client
        foreach ($this->filters as $filterItem) {
            // assign filter data (location depends if encoded or not)
            if ($this->encoded) {
                $field = $filterItem->field;
                $value = $filterItem->value;
                $compare = isset($filterItem->comparison) ? $filterItem->comparison : null;
                $filterType = $filterItem->type;
            } else {
                $field = $filterItem['field'];
                $value = $filterItem['data']['value'];
                $compare = isset($filterItem['data']['comparison']) ? $filterItem['data']['comparison'] : null;
                $filterType = $filterItem['data']['type'];
            }
            $field = my_mysqli_real_escape_string($field);
            $value = my_mysqli_real_escape_string($value);
            $compare = my_mysqli_real_escape_string($compare);
            $filterType = my_mysqli_real_escape_string($filterType);

            switch ($filterType) {
                case 'string':
                    $value = trim($value);

                    if (in_array($field, $strongLikeFields)) {
                        $tmpIds = explode(' ', $value);
                        $tmpIds[] = -1;
                        $tmpIds = array_diff($tmpIds, array(''));
                        $whereArr[] = "`$field` IN ('" . implode("','", $tmpIds) . "')";
                    } else if (in_array($field, $havingFields)) {
                        $havingArr[] = "$field LIKE '%$value%'";
                    } else if (in_array($field, $strongLikeFields)) {
                        $whereArr[] = "`$field` = '$value'";
                    } else {
                        $whereArr[] = "`$field` LIKE '%$value%'";
                    }
                    break;
                case 'list':
                    $fi = explode(',', $value);
                    foreach ($fi as &$vItem) {
                        $vItem = "'" . $vItem . "'";
                    }
                    $whereArr[] = "`$field` IN (" . implode(', ', $fi) . ')';
                    Break;
                case 'boolean':
                    $whereArr[] = ' AND ' . $field . ' = ' . ($value);
                    Break;
                case 'numeric':
                    switch ($compare) {
                        case 'eq':
                            $whereArr[] = "`" . $field . '` = ' . $value;
                            Break;
                        case 'lt':
                            $whereArr[] = "`" . $field . '` < ' . $value;
                            Break;
                        case 'gt':
                            $whereArr[] = "`" . $field . '` > ' . $value;
                            Break;
                    }
                    Break;
                case 'date':
                    switch ($compare) {
                        case 'eq':
                            $whereArr[] = '`' . $field . "` BETWEEN '" . date('Y-m-d', strtotime($value)) . "' AND '" . date('Y-m-d H:i:s', strtotime($value . ' + 86399 second')) . "'";
                            break;
                        case 'lt':
                            $whereArr[] = '`' . $field . "` <= '" . date('Y-m-d H:i:s', strtotime($value . ' + 86399 second')) . "'";
                            break;
                        case 'gt':
                            $whereArr[] = '`' . $field . "` >= '" . date('Y-m-d', strtotime($value)) . "'";
                            break;
                    }
                    Break;
            }
        }

        if (empty($this->filters)) {
            $whereArr[] = "`date` > NOW() - INTERVAL 1 WEEK";
        }

        $whereArr[] = "`property` != 'Password'";


        $whereStr = $this->mainObj->prepWhereStr($whereArr);
        $whereOldStr = str_replace(array('`worker`', '`object_id`'), array('`from`', '`to`'), $whereStr);

        $query = "  SELECT SQL_CALC_FOUND_ROWS `id`,`date`,`worker`,`object_name`,`object_id`,`type`,`property`,`property` AS property_str,`was`,`set`, `comment`
                    FROM `{$this->mainObj->cGetTableName()}`
                    WHERE $whereStr
#                       UNION
#                    SELECT `id`,`date`,`from`,'' AS `object_name`,`to` AS `object_id`,`type`,`property`,`property` AS property_str,`was`,`set`,`comment`
#                    FROM `ActionHistory`
#                    WHERE $whereOldStr
                    ";
        $queryTotal = 'SELECT FOUND_ROWS() AS handler_history_total';

//        die($query);

        if ($havingArr) {
            $query .= PHP_EOL . ' HAVING ' . implode(' AND ', $havingArr);
        }

        if ($this->sort != '') {
            $query .= PHP_EOL . " ORDER BY $this->sort $this->dir";
        }
        $query .= PHP_EOL . " LIMIT $this->start, $this->count";
        ApiLogger::addLogVarExport($query);

        $arr = DB::query($query);
//        $total = DB::queryFirstField('aaa', 'SELECT FOUND_ROWS()');
        $total = DB::queryFirstField($queryTotal);

        foreach ($arr as &$value) {
            $value['worker_id'] = isset($value['worker']) ? $value['worker'] : $value['from'];
        }

        $ret = array(
            'total' => $total,
            'sql' => $query,
            'executing' => (microtime(true) - $time_1),
            'data' => $arr
        );

        return $ret;
    }

}

new HandlerHistory();
