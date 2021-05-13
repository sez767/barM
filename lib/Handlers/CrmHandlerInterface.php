<?php

/**
 * @author dob
 */
interface CrmHandlerInterface {

    public function doResponse();

    public function read();

    public function update($attributes);

    public function insert($attributes);

    public function delete($attributes);
}
