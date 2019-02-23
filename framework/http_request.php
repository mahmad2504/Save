<?php
/**
* Access the HTTP Request
* 
* Found on http://www.daniweb.com/web-development/php/code/216846/get-http-request-headers-and-body
*/
namespace gypsy;
class http_request {

    /** additional HTTP headers not prefixed with HTTP_ in $_SERVER superglobal */
    public $add_headers = array('CONTENT_TYPE', 'CONTENT_LENGTH');

    /**
    * Construtor
    * Retrieve HTTP Body
    * @param Array Additional Headers to retrieve
    */
    function __construct($add_headers = false) {

        $this->retrieve_headers($add_headers);
        $this->body = @file_get_contents('php://input');
    }

    /**
    * Retrieve the HTTP request headers from the $_SERVER superglobal
    * @param Array Additional Headers to retrieve
    */
    function retrieve_headers($add_headers = false) {

        if ($add_headers) {
            $this->add_headers = array_merge($this->add_headers, $add_headers);
        }
		
		$basepath = pathinfo($_SERVER['SCRIPT_NAME'])['dirname'];
		$this->request_uri = explode('?',$_SERVER['REQUEST_URI'])[0];
		$this->request_uri = explode($basepath,$this->request_uri)[1];
		$this->request_parts = explode("/",$this->request_uri);
		array_shift($this->request_parts);
		if (isset($_SERVER['QUERY_STRING']))
			$this->query_string = $_SERVER['QUERY_STRING'];
        if (isset($_SERVER['HTTP_METHOD'])) {
            $this->method = $_SERVER['HTTP_METHOD'];
            unset($_SERVER['HTTP_METHOD']);
        } else {
            $this->method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : false;
        }
        $this->protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : false;
        $this->request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : false;

        $this->headers = array();
        foreach($_SERVER as $i=>$val) {
            if (strpos($i, 'HTTP_') === 0 || in_array($i, $this->add_headers)) {
                $name = str_replace(array('HTTP_', '_'), array('', '-'), $i);
                $this->headers[$name] = $val;
            }
        }
    }
	function query()
	{
		global $params;
		foreach($_GET as $key=>$value)
			$params->$key = $value;
		foreach($_POST as $key=>$value)
			$params->$key = $value;
		return $params;
	}
	function requesturi()
	{
		return $this->request_uri;
	}
	function requestparts()
	{
		return $this->request_parts;
	}
    /** 
    * Retrieve HTTP Method
    */
    function method() {
        return $this->method;
    }

    /** 
    * Retrieve HTTP Body
    */
    function body() {
        return $this->body;
    }

    /** 
    * Retrieve an HTTP Header
    * @param string Case-Insensitive HTTP Header Name (eg: "User-Agent")
    */
    function header($name) {
        $name = strtoupper($name);
        return isset($this->headers[$name]) ? $this->headers[$name] : false;
    }

    /**
    * Retrieve all HTTP Headers 
    * @return array HTTP Headers
    */
    function headers() {
        return $this->headers;
    }

    /**
    * Return Raw HTTP Request (note: This is incomplete)
    * @param bool ReBuild the Raw HTTP Request
    */
    function raw($refresh = false) {
        if (isset($this->raw) && !$refresh) {
            return $this->raw; // return cached
        }
        $headers = $this->headers();
        $this->raw = "{$this->method} {$_SERVER['REQUEST_URI']} {$this->protocol}\r\n";

        foreach($headers as $i=>$header) {
                $this->raw .= "$i: $header\r\n";
        }
        $this->raw .= "\r\n{$this->body}";
        return $this->raw;
    }

}

/**
* Example Usage
* Echos the HTTP Request back to the client/browser
*/
global $params;
$params =  new \StdClass();
$httprequest = new http_request();
//var_dump($httprequest->headers());
?>