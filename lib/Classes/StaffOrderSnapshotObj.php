<?php

/**
 * @author dob
 */
class StaffOrderSnapshotObj extends StaffOrderCommonObj {

    function __construct($id = null, $withLoad = false) {

        $this->cSetTableName('staff_order_snapshot');

        parent::__construct($id, $withLoad);
    }

}
