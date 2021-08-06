<?php

header('Content-Type: application/javascript; charset=utf-8');

require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

unset($_REQUEST['_dc']);
unset($_REQUEST['page']);
unset($_REQUEST['start']);
unset($_REQUEST['limit']);

echo file_get_contents_curl(GLOBAL_STORE_BASE_URL . '/handlers/get_StoreData.php?' . http_build_query($_REQUEST), 600);
