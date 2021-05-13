<?php

/**
 * @author dob
 */
class StaffOrderArchObj extends StaffOrderCommonObj {

    function __construct($id = null, $withLoad = false) {

        $this->cSetTableName('staff_order_arch');

        parent::__construct($id, $withLoad);
    }

}
