<?php

/**
 * @author dob
 */
class OprosnikObj extends CommonObject {

    function __construct($id = null, $withLoad = false) {
        parent::__construct($id, $withLoad);
    }

    public function getQuestions($orderId) {
        global $GLOBAL_OFFER_GROUP;
        ApiLogger::addLogVarExport('||||||||||||||||||||||||| getQuestions');

        $ret = array();

        $qs = ' SELECT o.id AS question_id, o.question, s.offer, o.offer_group
                FROM ' . $this->cGetTableName() . ' AS o
                    JOIN staff_order AS s ON
                        (s.country = o.country OR o.country = "") AND
                        (s.status = o.status OR o.status = "") AND
                        (s.offer = o.offer OR o.offer = "") AND
                        s.id = %i
                WHERE ' . $this->prepWhereStr(array('active > 0'), 'o');

        if (($rawData = DB::queryAssArray('question_id', $qs, $orderId))) {
            foreach ($rawData as $questId => $value) {
                if (empty($value['offer_group']) || trim($GLOBAL_OFFER_GROUP[$value['offer']]) == trim($value['offer_group'])) {
                    $ret[$questId] = $value;
                }
            }
        }

        ApiLogger::addLogVarExport($ret);

        return $ret;
    }

    public function buidOprosnik($orderId) {
        ApiLogger::addLogVarExport('||||||||||||||||||||||||| buidOprosnik');
        $answers = $this->getAnswers($orderId);
        ApiLogger::addLogVarExport($answers);
        $ret = array();

        foreach ($answers as $value) {
            $retItemArr = array();
            $retItemArr[] = "    xtype: 'textfield'";
            $retItemArr[] = "    fieldLabel: '{$value['question']}'";
            if (empty($value['answer_id'])) {
                $retItemArr[] = "    name: 'question_{$value['question_id']}'";
                $retItemArr[] = "    value: ''";
            } else {
                $retItemArr[] = "    name: 'question_{$value['question_id']}_{$value['answer_id']}'";
                $retItemArr[] = "    value: '{$value['answer']}'";
            }
            $ret[] = implode(',' . PHP_EOL, $retItemArr);
        }

        if (empty($ret)) {
            $ret = false;
        } else {
            $ret = '[{' . PHP_EOL . implode(PHP_EOL . '}, {' . PHP_EOL, $ret) . PHP_EOL . '}]';
        }

        return $ret;
    }

    public function getAnswers($orderId) {
        ApiLogger::addLogVarExport('||||||||||||||||||||||||| getAnswers');
        $oprosnikAnswersObj = new OprosnikAnswersObj();

        $questions = $this->getQuestions($orderId);
        $answerData = DB::queryAssArray('question_id', "SELECT * FROM {$oprosnikAnswersObj->cGetTableName()} WHERE order_id = %i", $orderId);
        foreach ($questions as $q_id => &$q_value) {
            $q_value['answer_id'] = empty($answerData[$q_id]) ? '' : $answerData[$q_id]['id'];
            $q_value['answer'] = empty($answerData[$q_id]) ? '' : $answerData[$q_id]['answer'];
        }
        return $questions;
    }

}
