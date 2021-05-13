<?php

/**
 * @author dob
 */
class QueueTableObj extends CommonObject {

    function __construct($id = null, $withLoad = false) {
         $this->cSetTableName('queue_table');
        parent::__construct($id, $withLoad);
    }

}
