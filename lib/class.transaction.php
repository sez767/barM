<?php

class TransactionStatusHistory {

    var $id = '';
    var $transaction = '';
    var $date = '';
    var $by = '';
    var $description = '';
    var $status_id = '';
    var $status_code = '';

    function __construct($id = '', $transaction = '', $by = '', $description = '', $status_id = '') {
        if (!empty($id))
            $this->id = $id;
        if (!empty($transaction))
            $this->transaction = $transaction;
        if (!empty($by))
            $this->by = $by;
        if (!empty($description))
            $this->description = $description;
        if (!empty($status_id))
            $this->status_id = $status_id;
    }

    function load() {
        if (!empty($this->id)) {
            $this->id = (int) $this->id;
            $VUQuery = "SELECT  BonusTransactionStatusHistory_Id
                         ,BonusTransactionStatusHistory_Date
                         ,BonusTransactionStatusHistory_By
                         ,BonusTransactionStatusHistory_Description
                         ,BonusTransactionStatusHistory_Status
                         ,BonusTransactionStatusHistory_BonusTransactionId
                         ,BonusTransactionStatusType_Code
                    FROM BonusTransactionStatusHistory
                    left join BonusTransactionStatusType on (BonusTransactionStatusHistory_Status = BonusTransactionStatusType_Code)
                   WHERE BonusTransactionStatusHistory_Id = '$this->id' ;";
            $VUResult = db_execute_query($VUQuery) or $VUResult = false;
            while ($Row = mysql_fetch_array($VUResult, MYSQL_ASSOC)) {
                $this->date = $Row['BonusTransactionStatusHistory_Date'];
                $this->by = $Row['BonusTransactionStatusHistory_By'];
                $this->description = $Row['BonusTransactionStatusHistory_Description'];
                $this->status_id = $Row['BonusTransactionStatusHistory_Status'];
                $this->transaction = $Row['BonusTransactionStatusHistory_BonusTransactionId'];
                $this->status_code = $Row['BonusTransactionStatusType_Code'];
            }
        }
        return ($this);
    }

    function set() {
        if (empty($this->id)) {
            $VUQuery = "INSERT  BonusTransactionStatusHistory
                     SET  BonusTransactionStatusHistory_By                    = '" . mysql_real_escape_string($this->by) . "'
                         ,BonusTransactionStatusHistory_Description           = '" . mysql_real_escape_string($this->description) . "'
                         ,BonusTransactionStatusHistory_Status                = '" . mysql_real_escape_string($this->status_id) . "'
                         ,BonusTransactionStatusHistory_BonusTransactionId    = '" . mysql_real_escape_string($this->transaction) . "'
                  ;";
            $VUResult = db_execute_query($VUQuery) or $VUResult = false;
            $this->id = mysql_insert_id();
        }
        return ($this->id);
    }

}

/**
 * 	объект для работы с транзакциями
 */
class Transaction {

    var $id = 0;
    var $bonusType = ''; //	тип начисления promotional / earnings - виртуальный / реальный счет пользователя
    var $date = '';
    var $value = 0;
    var $description = '';
    var $status_id = 1; //	1 / 2 / 8 / 9 - set / process / complete / discard
    var $parent = '';
    var $status_code = 'set';
    // доступные типы транзакций смотри в таблице BonusTransactionOperationType
    var $operation_id = ''; // типы транзакций
    var $operation_source = ''; //	источник, сетевые начисления / начисления за поточную работу
    var $memberId = '';
    var $initiator = '';
    //
    var $real_amount = 0;
    var $payed_amount = 0;

    ###############################################################

    function __construct($id = '') {
        $this->id = $id;
        if (!empty($id))
            $this->Load();
    }

    function Load() {
        $Result = false;
        $VUQuery = "SELECT
                       BonusTransaction_Id,
                       BonusTransaction_Parent,
                       BonusTransaction_BonusType,
                       BonusTransaction_Source,
                       BonusTransaction_Date,
                       BonusTransaction_Value,
                       BonusTransaction_Status,
                       BonusTransaction_Operation,
                       BonusTransaction_Description,
                       BonusTransactionStatusType_Code,
                       BonusTransactionOperationType_Code,
                       BonusTransaction_Initiator,
                       BonusTransaction_MemberId,
                       BonusTransaction_RealAmount,
                       BonusTransaction_PayedAmount
                  FROM BonusTransaction
                  left join BonusTransactionStatusType on BonusTransactionStatusType_Id = BonusTransaction_Status
                  left join BonusTransactionOperationType on BonusTransactionOperationType_Id = BonusTransaction_Operation
                 WHERE BonusTransaction_Id = '" . $this->id . "'";
        $VUResult = db_execute_query($VUQuery) or $Result = false;
        while ($VUResultRow = mysql_fetch_array($VUResult, MYSQL_ASSOC)) {
            $this->bonusType = $VUResultRow["BonusTransaction_BonusType"];
            $this->date = $VUResultRow["BonusTransaction_Date"];
            $this->value = $VUResultRow["BonusTransaction_Value"];
            $this->description = $VUResultRow["BonusTransaction_Description"];
            $this->status_id = $VUResultRow["BonusTransaction_Status"];
            $this->operation_id = $VUResultRow["BonusTransaction_Operation"];
            $this->parent = $VUResultRow["BonusTransaction_Parent"];
            $this->status_code = $VUResultRow["BonusTransactionStatusType_Code"];
            $this->operation_type = $VUResultRow["BonusTransactionOperationType_Code"];
            $this->memberId = $VUResultRow["BonusTransaction_MemberId"];
            $this->initiator = $VUResultRow["BonusTransaction_Initiator"];
            $this->real_amount = $VUResultRow["BonusTransaction_RealAmount"];
            $this->payed_amount = $VUResultRow["BonusTransaction_PayedAmount"];
            $Result = true;
        }
        return $Result;
    }

    /**
     * 	выборка транзакций указаного пользователя
     * 	@param array $options - массив вида:
     * 		$options = array(
     * 			"uid" => 1,
     * 			"limit" => 20,
     * 			"from" => NULL,
     * 			"to" => NULL
     * 		);
     * 	@return array - массив транзакций
     */
    public function userTransactions($options) {
        if (
                !isset($options['uid']) ||
                !is_int($options['uid']) ||
                $options['uid'] < 0
        ) {
            $result = array(
                "success" => FALSE,
                "statuscode" => 404,
                "error" => "User not found"
            );

            return $result;
        }

        if (!isset($options['limit'])) {
            $options['limit'] = 10;
        }

        if (!isset($options['from'])) {
            $options['from'] = date('Y-m-d') . " 00:00:00";
        }

        if (!isset($options['to'])) {
            $options['to'] = date('Y-m-d') . " 23:59:59";
        }

        $VUQuery = "
			SELECT
				BonusTransaction_Id,
				BonusTransaction_Parent,
				BonusTransaction_BonusType,
				BonusTransaction_Source,
				BonusTransaction_Date,
				BonusTransaction_Value,
				BonusTransaction_Status,
				BonusTransaction_Operation,
				BonusTransaction_Description,
				BonusTransactionStatusType_Code,
				BonusTransactionOperationType_Code,
				BonusTransaction_Initiator,
				BonusTransaction_MemberId,
				BonusTransaction_RealAmount,
				BonusTransaction_PayedAmount
			FROM
				BonusTransaction
			LEFT JOIN
				BonusTransactionStatusType ON BonusTransactionStatusType_Id = BonusTransaction_Status
			LEFT JOIN
				BonusTransactionOperationType ON BonusTransactionOperationType_Id = BonusTransaction_Operation
			WHERE
				BonusTransaction_MemberId = '" . $options['uid'] . "' AND
				BonusTransaction_Date BETWEEN '" . $options['from'] . "' AND '" . $options['to'] . "'
			LIMIT
				" . $options['limit'] . "
		";

        $VUResult = db_execute_query($VUQuery);

        $result = array();

        while ($VUResultRow = mysql_fetch_array($VUResult, MYSQL_ASSOC)) {
            $result[] = $VUResultRow;
        }

        $result = array(
            "success" => TRUE,
            "statuscode" => 200,
            "result" => $result
        );

        return $result;
    }

    /**
     * 	инициализация транзакции
     *
     */
    function set($by = '') {
        if (empty($this->memberId)) {
            return false;
        }

        if (empty($by)) {
            $by = $this->memberId;
        }

        $InsertQuery = "
			INSERT
				BonusTransaction
            SET
            	BonusTransaction_BonusType = '" . mysql_real_escape_string($this->bonusType) . "',
				BonusTransaction_Source = '" . mysql_real_escape_string($this->operation_source) . "',
				BonusTransaction_Value = '" . mysql_real_escape_string($this->value) . "',
				BonusTransaction_Description = '" . mysql_real_escape_string($this->description) . "',
				BonusTransaction_Status = '" . mysql_real_escape_string($this->status_id) . "',
				BonusTransaction_Operation = '" . mysql_real_escape_string($this->operation_id) . "',
				BonusTransaction_MemberId = '" . mysql_real_escape_string($this->memberId) . "',
				BonusTransaction_Parent = '" . mysql_real_escape_string($this->parent) . "',
				BonusTransaction_Initiator = '" . mysql_real_escape_string($by) . "',
				BonusTransaction_RealAmount = '" . mysql_real_escape_string($this->real_amount) . "',
				BonusTransaction_PayedAmount = '" . mysql_real_escape_string($this->payed_amount) . "'
        ";

        db_execute_query($InsertQuery) or $Result = false;
        //error_log('<TRANS>'.$InsertQuery);
        $this->id = mysql_insert_id();

        // fix save history
        $history = new TransactionStatusHistory('', $this->id, $by, $this->description, $this->status_id);
        $history->set();

        /*
          $member = new Member($this->memberId);
          if ($this->bonusType == 'injected') {
          $member->setBonuses_injected($member->Bonuses_injected + $this->value);
          } elseif ($this->bonusType == 'promotional') {
          $member->setBonuses_promotional($member->Bonuses_promotional + $this->value);
          } elseif ($this->bonusType == 'earnings') {
          $member->setBonuses_earnings($member->Bonuses_earnings + $this->value);
          } elseif ($this->bonusType == 'remove') {
          $member->setBonuses_injected($member->Bonuses_injected + $this->value);
          }
          $member->save();
         */

        return $this->id;
    }

    function discard($by = '', $reason = '') {
        $reason = mysql_real_escape_string($reason);
        $this->status_id = 9;
        $this->status_code = 'discard';
        $InsertQuery = "UPDATE BonusTransaction
                       SET BonusTransaction_Status = 9
                     where BonusTransaction_Id = '$this->id'
                       ; ";
        db_execute_query($InsertQuery) or $Result = false;
        // fix save history
        $history = new TransactionStatusHistory('', $this->id, $by, $reason, $this->status_id);
        $history->set();

        $Transaction = new Transaction();
        $Transaction->bonusType = $this->bonusType;
        $Transaction->value = - 1 * $this->value;
        $Transaction->description = 'Refund from: ' . $this->description;
        $Transaction->memberId = $this->memberId;
        $Transaction->parent = $this->id;
        $Transaction->operation_id = 14;
        $Transaction->set($by);
        $Transaction->complete('', 'Refunded');

        $member = new Member($this->memberId);
        if ($this->bonusType == 'injected') {
            $member->setBonuses_injected($member->Bonuses_injected - $this->value);
        } elseif ($this->bonusType == 'promotional') {
            $member->setBonuses_promotional($member->Bonuses_promotional - $this->value);
        } elseif ($this->bonusType == 'earnings') {
            $member->setBonuses_earnings($member->Bonuses_earnings - $this->value);
        }
        $member->save();

        return $this->id;
    }

    /**
     * 	завершение выполнения транзакции
     * 	@param $by - ID пользователя выполняющий действие
     * 	@param $reason - причина
     */
    function complete($by = '', $reason = '') {
        $reason = mysql_real_escape_string($reason);
        $this->status_id = 8; // завершено
        $this->status_code = 'complete';

        db_execute_query("
			UPDATE
				BonusTransaction
            SET
            	BonusTransaction_Status = 8,
            	BonusTransaction_finished = '" . date('Y-m-d H:i:s') . "'
            WHERE
            	BonusTransaction_Id = '" . $this->id . "'
        ") OR $Result = false;

        //	зачисление средств за показы и клики
        switch ($this->operation_id) {
            //	income_from_clicks_and_shows
            case 16:
            case 8:
                $member = new Member($this->memberId);
                if ($this->bonusType == 'injected') {
                    $member->setBonuses_injected($member->Bonuses_injected + $this->value);
                } elseif ($this->bonusType == 'promotional') {
                    $member->setBonuses_promotional($member->Bonuses_promotional + $this->value);
                } elseif ($this->bonusType == 'earnings') {
                    $member->setBonuses_earnings($member->Bonuses_earnings + $this->value);
                } elseif ($this->bonusType == 'remove') {
                    $member->setBonuses_injected($member->Bonuses_injected + $this->value);
                }
                $member->save();

                $member->updateBalances();
                break;
        }

        // fix save history
        $history = new TransactionStatusHistory('', $this->id, $by, $reason, $this->status_id);
        $history->set();

        //
        return $this->id;
    }

    function process($by = '', $reason = '') {
        $reason = mysql_real_escape_string($reason);
        $this->status_id = 2;
        $this->status_code = 'process';
        $InsertQuery = "UPDATE BonusTransaction
                       SET BonusTransaction_Status = 2
                     where BonusTransaction_Id = '$this->id'
                       ; ";
        db_execute_query($InsertQuery) or $Result = false;
        // fix save history
        $history = new TransactionStatusHistory('', $this->id, $by, $reason, $this->status_id);
        $history->set();
        return $this->id;
    }

}
