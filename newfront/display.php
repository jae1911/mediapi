<?php

// Manage display of page

$page = 'index'; // default

$HTML = '';

// Determine content
if(!$loggedin) {
    $HTML .= "<p>The easy media finder.</p><br/>" . PHP_EOL;
    $HTML .= "<p>Register an account or login to use the database.</p><br/>" . PHP_EOL;
    $HTML .= "<form action=\"index.php\" method=\"post\">" . PHP_EOL;

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
} else {
    $HTML .= "<p>Input your query and search.</p>" . PHP_EOL;
}

// Display page
$content = $HTML;

require('./required/postman.php');
require('./public/index.php');
