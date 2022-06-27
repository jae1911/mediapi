<?php

class Mediapi {
    // Query movies
    public function queryMovies($token, $query, $year = "", $version = "") {
        $data = array('title' => $query);
        if (!empty($year))
            $data['year'] = $year;
        if (!empty($version))
            $data['plot_version'] = $version;

        $options = array(
          'http' => array(
            'method'  => 'POST',
            'content' => json_encode( $data ),
            'header'=>  "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n" .
                        "x-access-tokens: " . $token . "\r\n"
            )
        );
        $finalUri = "http://backend:5000/getMovie";
        $context  = stream_context_create( $options );
        $result = file_get_contents($finalUri , false, $context );
        $resultParsed = json_decode($result);

        return $resultParsed;
    }
}

class LoginApi {

    // This function will either login or register a user
    // By default this will login the user and return their JWT
    public function userAction($username, $password, $action = "login") {
        $data = array('username' => $username, 'password' => $password);
        $options = array(
          'http' => array(
            'method'  => 'POST',
            'content' => json_encode( $data ),
            'header'=>  "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n"
            )
        );
        $finalUri = "http://backend:5000/api/" . $action;
        $context  = stream_context_create( $options );
        $result = file_get_contents($finalUri , false, $context );
        $resultParsed = json_decode($result);

        if( strpos($result, "err") ) {
            return array(False, $resultParsed->err);
        } else {
            return array(True, $resultParsed->ok);
        }
    }
}

?>