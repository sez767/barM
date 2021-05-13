<?php

/**
 * @author dob
 */
class PredictiveMultiplierObj extends CommonObject {

    function __construct($id = null, $withLoad = false) {
        $this->cSetTableName('predictive_multiplier');
//        $this->cSetLoggingState(false);
        parent::__construct($id, $withLoad);
    }

}
