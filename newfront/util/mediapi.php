<?php

class ApiWrapper {
    private const $API_BASEURL = "http://backend:5000";

    public function DoRequest($endpoint, $data, $token = "") {
        if(empty($endpoint) || empty($data))
            return NULL;

        $headers = "Content-Type: application/json\r\nAccept: application/json\r\n";

        if(!empty($token))
            $headers = $headers . "x-access-tokens: " . $token . "\r\n";

        $options = array(
            'http' => array(
                'method' => 'POST',
                'content' => json_encode( $data ),
                'header'=>  $headers
            )
        );

        $finalUri = $API_BASEURL . $endpoint;
        $context = stream_context_create($options);
        $result = file_get_contents($finalUri, false, $context);

        return $result;
    }
}

class Mediapi {
    // Query movies
    public function queryMovies($token, $query, $year = "", $version = "") {
        $data = array('title' => $query);
        if (!empty($year))
            $data['year'] = $year;
        if (!empty($version))
            $data['plot_version'] = $version;

        $data = json_encode(data);

        $apiWrapper = new ApiWrapper();
        $resultParsed = json_decode($apiWrapper.DoRequest('/getMovie', $data, $token));

        return $resultParsed;
    }

    // Query books
    public function queryBooks($token, $isbn) {
        $data = json_encode(array('isbn' => $isbn));

        $apiWrapper = new ApiWrapper();
        $resultParsed = json_decode($apiWrapper.DoRequest('/getBook', $data, $token));

        return $resultParsed;
    }
}

class LoginApi {

    // This function will either login or register a user
    // By default this will login the user and return their JWT
    public function userAction($username, $password, $action = "login") {
        $data = json_encode(array('username' => $username, 'password' => $password));

        $apiWrapper = new ApiWrapper();
        $resultParsed = json_decode($apiWrapper.DoRequest('/api/' . $action, $data));

        if( strpos($result, "err") ) {
            return array(False, $resultParsed->err);
        } else {
            return array(True, $resultParsed->ok);
        }
    }
}
