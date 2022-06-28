<?php
    session_start();
    require('utils/mediapi.php');
    $loggedin = false;
    if(isset($_SESSION['token'])) {
        $loggedin = true;
    }
?>