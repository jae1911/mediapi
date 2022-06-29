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
        $res = $loginApi->userAction($username, $password, "register");
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
        header('location: index.php');
        exit();
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
} else if($loggedin && empty($res)) {
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

} else if($loggedin && !empty($res)) {
    $HTML .= "<p>Query results.</p><br/>" . PHP_EOL;
    $HTML .= "<p><a href=\"index.php\">Search again</a></p>" . PHP_EOL;

    if($want == "movie") {
        // Movie details
        $HTML .= "<h4>" . $res->Title . " by " . $res->Writer . "</h4>" . PHP_EOL;
        $HTML .= "<p>Featuring: " . $res->Actors . " and released on " . $res->Released . "</p><br/>" . PHP_EOL;
        $HTML .= "<p><i>" . $res->Plot . "</p></i><br/>" . PHP_EOL;
        $HTML .= "<p>Ratings:</p>" . PHP_EOL;
        $HTML .= "<ul>" . PHP_EOL;

        // Ratings
        foreach ($res->Ratings as $rating) {
            $HTML .= "<li>" . $rating->Source . ": " . $rating->Value . PHP_EOL;
        }

        $HTML .= "</ul>" . PHP_EOL;
        $HTML .= "<img src=\"" . $res->Poster . "\"/>" . PHP_EOL;
    } else {
        // Book details
        $title = $res->title;

        if (isset($res->edition_name))
            $title .= " " . $res->edition_name;
        if (isset($res->by_statement))
            $title .= " by " . $res->by_statement;

        $HTML .= "<h4>" . $title . "</h4>" . PHP_EOL;

        if (isset($res->subtitle))
            $HTML .= "<h5>" . $res->subtitle . "</h5>" . PHP_EOL;

        $HTML .= "<p>Published in ". $res->publish_date . ".</p><br/>" . PHP_EOL;

        if(isset($res->first_sentence)) {
            $fsen = '';
            if(is_array($res->first_sentence))
                $fsen = $res->first_sentence;
            else
                $fsen = $res->first_sentence->value;

            $HTML .= "<p>First sentence: <i>" . $fsen . "</i></p><br/>" . PHP_EOL;
        }

        if(isset($res->notes))
            $HTML .= "<p>Notes: " . $res->notes->value . "</p><br/>" . PHP_EOL;

        if(isset($res->subjects)) {
            $subString = '';
            foreach($res->subjects as $subject)
                $subString .= " " . $subject . ",";

            $HTML .= "<p>Subjects:" . $subString . "</p><br/>" . PHP_EOL;
        }

        if(isset($res->table_of_contents)) {
            $HTML .= "<p>Table of contents:</p><ul>" . PHP_EOL;
            foreach($res->table_of_contents as $content) {
                $HTML .= "<li>" . $content->title . "</li>" . PHP_EOL;
            }
            $HTML .= "</ul>" . PHP_EOL;
        }

    }

    $res = NULL;
}

// Display page
$content = $HTML;

require('./public/index.php');
