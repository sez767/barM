<?php

/**
 * @author dob
 */
class DeliveryWeekDaysObj extends CommonObject {

    function __construct($id = null, $withLoad = false) {

        $this->cSetTableName('delivery_week_days');

        parent::__construct($id, $withLoad);
    }

}
