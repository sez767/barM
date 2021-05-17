<?php

header("Content-Type: text/html; charset=utf-8");

// session_set_cookie_params(10800);
// session_start();
// if (!isset($_SESSION['Logged_StaffId'])) {
//     header('location: /login.html');
//     die();
// }
// require_once dirname(__FILE__) . '/lib/db.php';
?>
<html>
    <head>
        <title>BaribardaLite cabinet</title>

        <link rel="stylesheet" type="text/css" href="css/dx.common.css">
        <link rel="stylesheet" type="text/css" href="css/dx.material.teal.light.css">
        <link rel="stylesheet" type="text/css" href="css/index.css">

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/dx.all.js"></script>
        <script type="text/javascript" src="js/mainpage.js"></script>
        <script type="text/javascript" src="js/dx.messages.ru.js"></script>
    </head>
    <body class="dx-viewport">
        <div class="demo-container">
        <div id="toolbar"></div>
        <div id="drawer">
            <div id="view" class="dx-theme-background-color"></div>
        </div>
        </div>
    </body>
</html>