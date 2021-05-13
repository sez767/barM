<?php

/**
 * @author dob
 */
class StaffOrderCommonObj extends CommonObject {

    public function toArchive() {
        $ret = false;
        if (($id = $this->cGetId()) && $this->cGetLoadedValues()) {
            $staffOrderArchObj = new StaffOrderArchObj($id);
            if ($staffOrderArchObj->cGetLoadedValues()) {
                $ret = $staffOrderArchObj->cSave($this->cGetValues());
                $this->cDelete();
            }
        }
        return $ret;
    }

    public function fromArchive() {
        $ret = false;
        if (($id = $this->cGetId()) && $this->cGetLoadedValues()) {
            $staffOrderObj = new StaffOrderObj($id);
            $ret = $staffOrderObj->cSave($this->cGetValues());

            $this->cDelete();
        }
        return $ret;
    }

}
