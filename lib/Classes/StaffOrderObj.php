<?php

/**
 * @author dob
 */
class StaffOrderObj extends StaffOrderCommonObj {

    function __construct($id = null, $withLoad = false) {

        $this->cSetTableName('staff_order');

        parent::__construct($id, $withLoad);
    }

}
