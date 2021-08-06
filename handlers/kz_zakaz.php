<?php
include_once ("/../lib/db.php");
include_once ("excel.inc.php");

$excel = new ExcelWriter("dostavka.xls");

$excel->writeLine(array('<table border="1">
      <tr>
        <td>&nbsp;</td>
        <td colspan=21 >КВИТАНЦИЯ №</td>
      </tr>
      <tr>
        <td>img</td>
        <td colspan=21>на оплату наличными</td>
      </tr>
      <tr>
        <td>Код платежа</td>
        <td colspan=21>&nbsp;&nbsp;&nbsp;&nbsp;<b>Плательщик(ФИО)</b></td>
      </tr>
      <tr>
        <td align="right">4718</td>
        <td colspan=21>ИИНПлательщикаКОд</td>
      </tr>
      <tr>
        <td>Номер посылки</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td colspan="3">&nbsp;</td>
        <td>&nbsp;</td>
        <td colspan="4">&nbsp;</td>
      </tr>
    </table>'));
$excel->close();
?>
