<?php

/**
 * @author dob
 */
class OfferPropertyObj extends CommonObject {

    function __construct($id = null, $withLoad = false) {

        $this->cSetTableName('offer_property');

        parent::__construct($id, $withLoad);
    }

}
