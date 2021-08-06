<?php

require_once dirname(__FILE__) . "/../lib/db.php";

/**
 * @author dob
 */
class HandlerClients extends CrmHandlerBase {

    /**
     * @var ClientsObj
     */
    protected $mainObj;

    function __construct() {

        parent::__construct();

        $this->mainObj = new ClientsObj($_REQUEST['uuid']);

        $this->doResponse();
    }

    function read() {

    }

    function update_worries() {
        ApiLogger::addLogVarExport($_REQUEST);

        $ret = true;
        if ($this->mainObj->cGetId()) {
            $ret = $this->mainObj->cSave(array('client_worries' => $_REQUEST['client_worries']));
        }

        return $ret;
    }

}

new HandlerClients();
