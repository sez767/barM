<?php

/**
 * @author dob
 */
class ResponsiblePlansColdObj extends ResponsiblePlansCommonObj {

    function __construct($id = null, $withLoad = false) {
         $this->cSetTableName('ResponsiblePlansCommon');
        $this->cSetAutoFieldsValues('type', 'cold');
        parent::__construct($id, $withLoad);
    }

}
