<?php
/**
 * Soundcloud_Missing_Client_Id_Exception
 */
class Soundcloud_Missing_Client_Id_Exception extends Exception {

    protected $message = 'All requests must include a consumer key. Referred to as client_id in OAuth2.';

}

/**
 * Soundcloud_Invalid_Http_Response_Code_Exception
 */
class Soundcloud_Invalid_Http_Response_Code_Exception extends Exception {

    protected $httpBody;
    protected $httpCode;
    protected $message = 'The requested URL responded with HTTP code %d.';

    public function __construct($message = null, $code = 0, $httpBody = null, $httpCode = 0) {
        $this->httpBody = $httpBody;
        $this->httpCode = $httpCode;
        $message = sprintf($this->message, $httpCode);

        parent::__construct($message, $code);
    }

    public function getHttpBody() {
        return $this->httpBody;
    }

    public function getHttpCode() {
        return $this->httpCode;
    }

}

class Soundcloud_Invalid_Response_Format_Exception extends Exception {

    protected $message = 'Invalid response format given. Currently the supported response formats are either JSON or XML.';

}
