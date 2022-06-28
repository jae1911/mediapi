<?php

require('util/mediapi.php');

// In case of user login / register
if (isset($_POST['user'])) {
    $err = [];

    if (!isset($_POST['username']) || !isset($_POST['password']))
        $err[] = 'Please input a valid username or password<br/>';
    else if (empty($_POST['username']) || empty($_POST['password']))
        $err[] = 'Please input an username or password<br/>';

    if(empty($err)) {
        // Login/Register sequence
        $username = $_POST['username'];
        $password = $_POST['password'];

        $loginApi = new LoginApi();
        $res = $loginApi->userAction($username, $password);
        if(!$res[0]) {
            $res = $loginApi->userAction($username, $password, "login");

            if($res[0]) {
                $_SESSION['token'] = $res[1];
                $res = NULL;
            }
        } else {
            $res = $loginApi->userAction($username, $password, "login");
            if($res[0]) {
                $_SESSION['token'] = $res[1];
                $res = NULL;
            }
        }
    }
} else if (isset($_POST['media'])) {
    $err = [];

    if (!$loggedin)
        $err[] = "Please login.";

    if (empty($_POST['query']))
        $err[] = "Please input a query.";

    if(empty($err)) {
        $mediapi = new Mediapi();

        $want = "movie";
        $res = $mediapi->queryMovies($_SESSION['token'], addslashes($_POST['query']), addslashes($_POST['year']), addslashes($_POST['scriptversion']));
        
        if(isset($res->err) || isset($res->Error)) {
            $want = "book";
            $res = $mediapi->queryBooks($_SESSION['token'], addslashes($_POST['query']));
        }

        if(isset($res->err) ||isset($res->error)) {
            $err[] = "Could not find any book / movie with this query";
            $res = NULL;
        }
    }
}
