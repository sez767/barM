<?php

require_once dirname(__FILE__) . "/excel.inc.php";

Class ExtExcelWriter extends ExcelWriter {

    /**
     * Write line to document
     * @params string/array $line_arr Line data.
     */
    public function writeLine($line_arr) {
        if ($this->state != "OPENED") {
            $this->error = "Error : Please open the file.";
            return false;
        }

        if (!is_array($line_arr)) {
            $this->error = "Error : Argument is not valid. Supply an valid Array.";
            return false;
        }

        $this->content .= "<tr>";

        foreach ($line_arr as $cell) {
            if (
                    is_array($cell) &&
                    isset($cell['bgcolor'])
            ) {
                $bgcolor = isset($cell['bgcolor']) ? $cell['bgcolor'] : "";

                $this->content .= "<td class=x124 width=64 border=1 bgcolor='" . $bgcolor . "'>" . $cell['value'] . "</td>";
            } else {
                $this->content .= "<td class=x124 width=64 border=1>" . $cell . "</td>";
            }
        }

        $this->content .= "</tr>";
    }

}
