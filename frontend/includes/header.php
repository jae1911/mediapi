<?php
    session_start();
    require('utils/mediapi.php');
    $loggedin = false;
    if(isset($_SESSION['token'])) {
        $loggedin = true;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MediaPI<?php if(isset($pagetitle)) echo " - " . $pagetitle; ?></title>

    <link rel="stylesheet" type="text/css" href="public/main.css?v=<?php echo time(); ?>">
</head>
<body>

