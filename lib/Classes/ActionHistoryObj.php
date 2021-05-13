<?php

/**
 * @author dob
 */
class ActionHistoryObj extends CommonObject {

    var $id = null;
    var $date = null;
    var $from = null;
    var $to = null;
    var $type = null;
    var $comment = null;

    function __construct($id = null, $withLoad = false) {
        $this->cSetTableName('ActionHistoryNew');
        parent::__construct($id, $withLoad);
    }

    /**
     *
     * @param type $objectName
     * @param type $objectId
     * @param type $actionType
     * @param type $property
     * @param type $was
     * @param type $set
     * @param type $comment
     * @param type $commentAdd
     * @return type
     */
    public function save($objectName, $objectId, $actionType, $property = null, $was = null, $set = null, $comment = null, $commentAdd = null) {

        if (empty($objectName)) {
            return;
        }
        $adminId = self::getAdminId();
        $insertArr = array(
            'object_name' => $objectName,
            'object_id' => $objectId,
            'type' => $actionType,
            'worker' => $adminId
        );

        if (!empty($property)) {
            $insertArr['property'] = (string) $property;
        }


        if (!empty($was)) {
            $insertArr['was'] = mb_substr(is_array($was) ? json_encode($was) : $was, 0, 255);
        }
        if (!empty($set)) {
            $insertArr['set'] = mb_substr(is_array($set) ? json_encode($set) : $set, 0, 255);
        }

        if (!empty($comment)) {
            $insertArr['comment'] = (string) $comment;
        } elseif (!empty($_REQUEST['comment'])) {
            $insertArr['comment'] = (string) $_REQUEST['comment'];
        } elseif (($callelFileName = ApiLogger::getCalledFileName())) {
            $insertArr['comment'] = $callelFileName;
        }
        if (!empty($commentAdd)) {
            $insertArr['comment'] .= ": $commentAdd";
        }

        $insertArr['comment'] = mb_substr($insertArr['comment'], 0 , 255);

        return DB::insert($this->cGetTableName(), $insertArr);
    }

    /**
     * @param type $objectName
     * @param type $historyData
     * @param type $comment
     * @param type $commentAdd
     * @return type
     */
    public function saveAll($objectName, $historyData, $comment = null, $commentAdd = null) {
        if (empty($objectName)) {
            return;
        }
        $adminId = self::getAdminId();

        if (!empty($comment)) {
            $comment = (string) $comment;
        } elseif (!empty($_REQUEST['comment'])) {
            $comment = (string) $_REQUEST['comment'];
        } elseif (($callelFileName = ApiLogger::getCalledFileName())) {
            $comment = $callelFileName;
        }
        if (!empty($commentAdd)) {
            $comment .= ": $commentAdd";
        }

        foreach ($historyData as &$histVal) {
            $histVal['object_name'] = $objectName;
            if (empty($histVal['worker'])) {
                $histVal['worker'] = $adminId;
            }
            $histVal['comment'] = $comment;
        }

        return DB::insert($this->cGetTableName(), $historyData);
    }

}
