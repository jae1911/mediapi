<?php

// Session setup
session_start();

// Detect login
$loggedin = isset($_SESSION['token']);
