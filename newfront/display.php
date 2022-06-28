<?php

// Manage display of page

$page = 'index'; // default

$HTML = '';

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
} else {
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
            $_RES = $res;
        } else {
            $_RES = $res;
        }

        if(isset($res->err) ||isset($res->error)) {
            $err[] = "Could not find any book / movie with this query";
            $res = NULL;
        }
    }
}

// Determine content
if(!$loggedin) {
    $HTML .= "<p>The easy media finder.</p><br/>" . PHP_EOL;
    $HTML .= "<p>Register an account or login to use the database.</p><br/>" . PHP_EOL;
    $HTML .= "<form action=\"index.php\" method=\"post\" name=\"user\">" . PHP_EOL;

    // Display any errors
    if(isset($err)) {
        foreach($err as $error) {
            $HTML .= "<p>" . $error . "</p></br>" . PHP_EOL;
        }
    }

    // Build user form
    $HTML .= "<p>Username: <input type=\"text\" name=\"username\" /></p>" . PHP_EOL;
    $HTML .= "<p>Password: <input type=\"password\" name=\"password\" /></p>" . PHP_EOL;
    $HTML .= "<p><input type=\"submit\" name=\"user\" /></p>" . PHP_EOL;

    $HTML .= "</form>" . PHP_EOL;
} else if($loggedin && empty($_RES)) {
    $HTML .= "<p>Input your query and search.</p><br/>" . PHP_EOL;
    $HTML .= "<p><a href=\"logout.php\">Log out</a></p>" . PHP_EOL;
    $HTML .= "<form action=\"index.php\" method=\"post\" name=\"media\">" . PHP_EOL;

    // Display any errors
    if(isset($err)) {
        foreach($err as $error) {
            $HTML .= "<p>" . $error . "</p></br>" . PHP_EOL;
        }
    }

    // Build search form
    $HTML .= "<p>Query (movie/ISBN): <input type=\"text\" name=\"query\" /></p>" . PHP_EOL;
    $HTML .= "<p>Year (for movies): <input type=\"text\" name=\"year\" /></p>" . PHP_EOL;
    $HTML .= "<p>Version (for movies): <input type=\"text\" name=\"scriptversion\" /></p>" . PHP_EOL;
    $HTML .= "<p><input type=\"submit\" name=\"media\" /></p>" . PHP_EOL;

    $HTML .= "</form>";

} else if($loggedin && !empty($_RES)) {
    $HTML .= "<p>Query results.</p><br/>" . PHP_EOL;
}

// Display page
$content = $HTML;

//require('./required/postman.php');
require('./public/index.php');
