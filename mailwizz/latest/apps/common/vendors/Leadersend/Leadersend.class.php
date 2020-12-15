<?php

class Leadersend {
    private $version = '1.0';
    var $errorMessage;
    var $errorCode;
    
    private $apiUrl;
    private $secure = false;

    private $apikey;

    var $output = 'json';

    public function __construct($apikey = false){
        if($apikey) $this->apikey = $apikey;
        $this->apiUrl = ($this->secure ? 'https' : 'http').'://api.leadersend.com/'.$this->version.'/';
    }

    public function __call($method, $args) {

        // params
        $params = (sizeof($args) > 0) ? $args[0] : array();

        // request method
        $request = isset($params["method"]) ? strtoupper($params["method"]) : 'POST';

        // unset useless params
        if(isset($params["method"])) unset($params["method"]);

        // Make request
        $result = $this->sendRequest($method, $params, $request);

        // Return result
        $return = ($result === true) ? $this->_response : false;

        return $return;
    }

    public function requestUrlBuilder($method, $params=array(), $request) {

        $query_string = array(
            'output' => $this->output,
            'method' => $method
        );

//       foreach($params as $key=>$value) {
//            if($request == "GET" || in_array($key,array('apikey','output'))) $query_string[$key] = $key.'='.urlencode($value);
//            if($key == "output") $this->output = $value;
//        }

        $this->call_url = $this->apiUrl.'?'.http_build_query($query_string, '', '&');

        return $this->call_url;
    }


    public function sendRequest($method = false, $params = array(), $request = 'POST'){
        $this->_method = $method;
        $this->_request = $request;
        $this->errorMessage = '';
        $this->errorCode = '';

        if($this->apikey) $params['apikey'] = $this->apikey;

        $url = $this->requestUrlBuilder($method,$params,$request);

        // Set up and execute the curl process  
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Leadersend/'.$this->version);

        $this->_request_post = false;
        if($request == 'POST') :
            curl_setopt($curl_handle, CURLOPT_POST, count($params));
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
            $this->_request_post = $params;
        endif;

        if(($buffer = curl_exec($curl_handle)) === false){
            $this->errorCode = -99;
            $this->errorMessage = "Could not connect (ERR ".curl_errno($curl_handle).": ".curl_error($curl_handle).")";
        }

        // Response code
        $this->_response_code = curl_getinfo($curl_handle,CURLINFO_HTTP_CODE);

        if($this->_response_code == 0 && !empty($buffer)){
            $this->_response_code = 200;
        }

        // Close curl process
        curl_close($curl_handle);

        // RESPONSE
        $this->_response = ($this->output == 'json') ? json_decode($buffer, true) : $buffer;
		
		if(isset($this->_response['error']) && isset($this->_response['code'])){
			$this->errorCode = $this->_response['code'];
            $this->errorMessage = $this->_response['error'];
			$this->_response_code = 400;
		}

        return ($this->_response_code == 200) ? true : false;
    }
}
