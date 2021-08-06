<?php

require_once dirname(__FILE__) . '/../lib/db.php';

header('Content-Type: application/json; charset=utf-8', true);

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        'success' => false,
        'msg' => 'Permission denied'
    )));
}

$ret = array();

$curatorRsponsibleArr = array_merge($GLOBAL_CURATOR_RESPONSIBLE['36710186'], $GLOBAL_CURATOR_RESPONSIBLE['57637454'], $GLOBAL_CURATOR_RESPONSIBLE['44917943']);
$curatorOperatorArr = array();
foreach ($curatorRsponsibleArr as $responsibleItem) {
    if (array_key_exists($responsibleItem, $GLOBAL_RESPONSIBLE_STAFF)) {
        $curatorOperatorArr = array_merge($curatorOperatorArr, $GLOBAL_RESPONSIBLE_STAFF[$responsibleItem]);
    }
}

if (in_array($_SESSION['Logged_StaffId'], $curatorOperatorArr) && ($_SESSION['operatorcold'] + $_SESSION['operatorcold']) > 0 || $_SESSION['Logged_StaffId'] == 11111111) {
    if (!isset($_SESSION['Rank'])) {
        $_SESSION['Rank'] = DB::queryFirstField('SELECT Rank FRPM Staff WHERE id = %i', $_SESSION['Logged_StaffId']);
    }
    $phrasesArr = array(
        'УАУ, как ты это делаешь?',
        'Кажется ты можешь больше!',
        'Е БОЙ, Ты можешь больше, я уверен! ',
        'Красава, план для тебя раз плюнуть!',
        'Вижу цель - не вижу преград, Молодец!',
        'Кажется, я тебя недооценил, Безупречно!'
    );
    switch ($_SESSION['Rank']) {
        case 1:
            $phrasesArr = array(
                'Для Стажера ТЫ КРУТ',
                'Е БОЙ, Ты можешь больше, я уверен!',
            );
            $pohvalaStartDelta = 3;
            break;
        case 2:
            $pohvalaStartDelta = 3;
            break;
        case 3:
            $pohvalaStartDelta = 8;
            break;
        case 4:
            $pohvalaStartDelta = 10;
            break;
        default :
            $pohvalaStartDelta = 0;
            break;
    }
    $phraseRand = $phrasesArr[array_rand($phrasesArr)];

    $qs = " SELECT COUNT(id) as aaa
                FROM staff_order
                WHERE fill_date > CURDATE() AND country IN ('kz', 'kzg') AND status = 'Подтвержден' AND last_edit > 0
                AND last_edit = {$_SESSION['Logged_StaffId']}";
//    die($qs);
    $ret['sql'] = $qs;

    $proressTotal = DB::queryFirstField($qs);


    if ($_SESSION['Logged_StaffId'] == 11111111) {
        $_SESSION['Rank'] = 4;
    }


    $progressPlan = $_SESSION['Rank'] * 5;

    $progessDelta = $progressPlan - $proressTotal;

//    if ($_SESSION['Logged_StaffId'] == 11111111) {
//        $progessDelta = 0;
//    }

    if ($progessDelta > 0) {
        $ret['progressHTML'] = "Осталось: <span style=\"border: 3px solid red; padding: 5px; font-weight: bold;\">$progessDelta</span";

        if ($_SESSION['Rank'] == 1 && $proressTotal == 1) {
            $ret['progressJS'] = "Ext.Msg.alert('ОГО, Круто {$_SESSION['Logged_StaffName']}!!!', 'Ну что Друг мой ТЫ В ИГРЕ');";
        } else {
            if ($progessDelta <= $pohvalaStartDelta) {
                $ret['progressJS'] = "Ext.Msg.alert('ОГО, Круто {$_SESSION['Logged_StaffName']}!!!', '$phraseRand');";
            }
        }
    } elseif ($progessDelta <= 0) {
        $html = '';

        if ($_SESSION['Rank'] > 1) {
            for ($index = 0; $index < abs($progessDelta); $index++) {
                $html .= '<img style="padding: 0 3px;" src="/images/competition/star-32x48.png"/>';
            }
        }
        $html .= '<img src="/images/competition/cubok-48x48.png"/>';

        $ret['progressHTML'] = $html;

        if ($progessDelta == 0) {
            $ret['progressJSExecOnes'] = 'salut';
            $ret['progressJS'] = getFanFarStr();
        }
    }
}

function getFanFarStr() {
    return "Ext.create('Ext.window.Window', {
                    title: 'План выполнен!',
                    autoShow: true,
                    id: 'progressWindow',
                    closable: false,
                    layout: {
                        type: 'vbox',
                        align: 'center'
                    },
                    bodyStyle: {
                        'text-align': 'center'
                    },
                    items: [
                        {
                            xtype: 'text',
                            text: '" . ($_SESSION['Rank'] == 1 ? '' : 'Теперь вы Можете заработать звездочки!!!') . "',
                            style: {
                                'font-size': 'large',
                                'font-weight': 'bold'
                            }
                        }, {
                            xtype: 'image',
                            autoShow : true,
                            src: '/images/competition/new/" . rand(1, 16) . ".gif',
                            height: 330,
                            width : 500
                        }
                    ]
                }).doAutoRender();

                new Ext.util.DelayedTask(function() {
                    Ext.getCmp('progressWindow').close();
                }).delay(16000);

                playSound('sound_08465');
            ";
}

print json_encode($ret);
