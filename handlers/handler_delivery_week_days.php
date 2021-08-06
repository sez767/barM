<?php

require_once dirname(__FILE__) . "/../lib/db.php";

/**
 * @author dob
 */
class HandlerDeliveryWeekDays extends CrmHandlerBase {

    /**
     * @var DeliveryWeekDaysObj
     */
    protected $mainObj;

    function __construct() {

        parent::__construct();

        $this->mainObj = new DeliveryWeekDaysObj($_REQUEST['id']);

        $this->doResponse();
    }

    public function read() {
        // initialize variables
        $where = $this->mainObj->prepWhereStr();

        $havingFields = array('creator_name');
        $havingArr = array();

        $qs = '';
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
                    if (in_array($field, $havingFields)) {
                        $havingArr[] = "$field LIKE '%$value%'";
                    } else {
                        $qs .= " AND $field LIKE '%$value%'";
                    }
                    break;
                case 'list':
                    $fi = explode(',', $value);
                    foreach ($fi as &$vItem) {
                        $vItem = "'" . $vItem . "'";
                    }
                    $qs .= " AND $field IN (" . implode(', ', $fi) . ')';
                    Break;
                case 'boolean':
                    $qs .= ' AND ' . $field . ' = ' . ($value);
                    Break;
                case 'numeric':
                    switch ($compare) {
                        case 'eq':
                            $qs .= " AND " . $field . ' = ' . $value;
                            Break;
                        case 'lt':
                            $qs .= " AND " . $field . ' < ' . $value;
                            Break;
                        case 'gt':
                            $qs .= " AND " . $field . ' > ' . $value;
                            Break;
                    }
                    Break;
                case 'date':
                    switch ($compare) {
                        case 'eq':
                            $where .= ' AND ' . $field . " BETWEEN '" . date('Y-m-d', strtotime($value)) . "' AND '" . date('Y-m-d H:i:s', strtotime($value . ' + 86399 second')) . "'";
                            break;
                        case 'lt':
                            $where .= ' AND ' . $field . " <= '" . date('Y-m-d H:i:s', strtotime($value . ' + 86399 second')) . "'";
                            break;
                        case 'gt':
                            $where .= ' AND ' . $field . " >= '" . date('Y-m-d', strtotime($value)) . "'";
                            break;
                    }
                    Break;
            }
        }
        $where .= $qs;

        $query = "  SELECT SQL_CALC_FOUND_ROWS *
                    FROM `{$this->mainObj->cGetTableName()}`
                    WHERE $where";

        if ($havingArr) {
            $query .= ' HAVING ' . implode(' AND ', $havingArr);
        }

        if ($this->sort != '') {
            $query .= " ORDER BY $this->sort $this->dir";
        }
        $query .= " LIMIT $this->start, $this->count";

//        die($query);
        $arr = DB::query($query);
        $total = DB::queryFirstField('SELECT FOUND_ROWS()');

        $ret = array(
            'data' => $arr,
            'total' => $total
        );

        return $ret;
    }

    public function delete($attributes) {
//        print_r($attributes);
//        var_dump($this->mainObj);
        return empty($this->mainObj) ? false : $this->mainObj->delete($attributes);
    }

}

new HandlerDeliveryWeekDays();
