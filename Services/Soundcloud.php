<?php
require_once 'Soundcloud/Exception.php';
require_once 'Soundcloud/Version.php';

/**
 * SoundCloud API wrapper with support for authentication using OAuth 2.
 *
 * @category Services
 * @package Services_Soundcloud
 * @author Anton Lindqvist <anton@qvister.se>
 * @copyright 2010 Anton Lindqvist <anton@qvister.se>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link http://github.com/mptre/php-soundcloud
 */
class Services_Soundcloud {

    /**
     * Access token returned by the service provider after a successful authentication.
     *
     * @access private
     *
     * @var string
     */
    private $_accessToken;

    /**
     * Version of the API to use.
     *
     * @access private
     *
     * @var integer
     */
    private static $_apiVersion = 1;

    /**
     * Supported audio MIME types.
     *
     * @access private
     *
     * @var array
     */
    private static $_audioMimeTypes = array(
        'aac' => 'video/mp4',
        'aiff' => 'audio/x-aiff',
        'flac' => 'audio/flac',
        'mp3' => 'audio/mpeg',
        'ogg' => 'audio/ogg',
        'wav' => 'audio/x-wav'
    );

    /**
     * OAuth client id.
     *
     * @access private
     *
     * @var string
     */
    private $_clientId;

    /**
     * OAuth client secret.
     *
     * @access private
     *
     * @var string
     */
    private $_clientSecret;

    /**
     * Development mode.
     *
     * @access private
     *
     * @var boolean
     */
     private $_development;

    /**
     * Available API domains.
     *
     * @access private
     *
     * @var array
     */
    private static $_domains = array(
        'development' => 'sandbox-soundcloud.com',
        'production' => 'soundcloud.com'
    );

    /**
     * HTTP response body from the last request.
     *
     * @access private
     *
     * @var string
     */
    private $_lastHttpResponseBody;

    /**
     * HTTP response code from the last request.
     *
     * @access private
     *
     * @var integer
     */
    private $_lastHttpResponseCode;

    /**
     * HTTP response headers from last request.
     *
     * @access private
     *
     * @var array
     */
    private $_lastHttpResponseHeaders;

    /**
     * OAuth paths.
     *
     * @access private
     *
     * @var array
     */
    private static $_paths = array(
        'authorize' => 'connect',
        'access_token' => 'oauth2/token',
    );

    /**
     * OAuth redirect uri.
     *
     * @access private
     *
     * @var string
     */
    private $_redirectUri;

    /**
     * API response format MIME type.
     *
     * @access private
     *
     * @var string
     */
    private $_requestFormat;

    /**
     * Available response formats.
     *
     * @access private
     *
     * @var array
     */
    private static $_responseFormats = array(
        'json' => 'application/json',
        'xml' => 'application/xml'
    );

    /**
     * HTTP user agent.
     *
     * @access private
     *
     * @var string
     */
    private static $_userAgent = 'PHP-SoundCloud';

    /**
     * Class version.
     *
     * @var string
     */
    public $version;

    /**
     * Constructor.
     *
     * @param string $clientId OAuth client id
     * @param string $clientSecret OAuth client secret
     * @param string $redirectUri OAuth redirect uri
     * @param boolean $development Sandbox mode
     *
     * @throws Services_Soundcloud_Missing_Client_Id_Exception when missing client id
     * @return void
     */
    function __construct($clientId, $clientSecret, $redirectUri = null, $development = false) {
        if (empty($clientId)) {
            throw new Services_Soundcloud_Missing_Client_Id_Exception();
        }

        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_redirectUri = $redirectUri;
        $this->_development = $development;
        $this->_responseFormat = self::$_responseFormats['json'];
        $this->version = Services_Soundcloud_Version::get();
    }

    /**
     * Get authorization URL.
     *
     * @param array $params Optional query string parameters
     *
     * @return string
     * @see Soundcloud::_buildUrl()
     */
    function getAuthorizeUrl($options = array()) {
        $params = array(
            'client_id' => $this->_clientId,
            'redirect_uri' => $this->_redirectUri,
            'response_type' => 'code'
        );
        $params = array_merge($params, $options);

        return $this->_buildUrl(self::$_paths['authorize'], $params, false);
    }

    /**
     * Get access token URL.
     *
     * @param array $params Optional query string parameters
     *
     * @return string
     * @see Soundcloud::_buildUrl()
     */
    function getAccessTokenUrl($params = array()) {
        return $this->_buildUrl(self::$_paths['access_token'], $params, false);
    }

    /**
     * Retrieve access token.
     *
     * @param string $code OAuth code returned from the service provider
     * @param array $postData Optional post data
     *
     * @return mixed
     * @see Soundcloud::_getAccessToken()
     */
    function accessToken($code, $postData = array()) {
        $defaultPostData = array(
            'code' => $code,
            'client_id' => $this->_clientId,
            'client_secret' => $this->_clientSecret,
            'redirect_uri' => $this->_redirectUri,
            'grant_type' => 'authorization_code'
        );
        $postData = array_merge($defaultPostData, $postData);

        return $this->_getAccessToken($postData);
    }

    /**
     * Refresh access token.
     *
     * @param string $refreshToken
     * @param array $postData Optional post data
     *
     * @return mixed
     * @see Soundcloud::_getAccessToken()
     */
    function accessTokenRefresh($refreshToken, $postData = array()) {
        $defaultPostData = array(
            'refresh_token' => $refreshToken,
            'client_id' => $this->_clientId,
            'client_secret' => $this->_clientSecret,
            'redirect_uri' => $this->_redirectUri,
            'grant_type' => 'refresh_token'
        );
        $postData = array_merge($defaultPostData, $postData);

        return $this->_getAccessToken($postData);
    }

    /**
     * Get access token.
     *
     * @return mixed
     */
    function getAccessToken() {
        return $this->_accessToken;
    }

    /**
     * Get API version.
     *
     * @return integer
     */
    function getApiVersion() {
        return self::$_apiVersion;
    }

    /**
     * Get the corresponding MIME type for a given file extension.
     *
     * @param string $extension
     *
     * @return string
     * @throws Services_Soundcloud_Unsupported_Audio_Format_Exception if the format is unsupported
     */
    function getAudioMimeType($extension) {
        if (array_key_exists($extension, self::$_audioMimeTypes)) {
            return self::$_audioMimeTypes[$extension];
        } else {
            throw new Services_Soundcloud_Unsupported_Audio_Format_Exception();
        }
    }

    /**
     * Get development mode.
     *
     * @return boolean
     */
    function getDevelopment() {
        return $this->_development;
    }

    /**
     * Get HTTP response header.
     *
     * @param string $header Name of the header
     *
     * @return mixed
     */
    function getHttpHeader($header) {
        if (is_array($this->_lastHttpResponseHeaders)) {
            return (array_key_exists($header, $this->_lastHttpResponseHeaders))
                ? $this->_lastHttpResponseHeaders[$header]
                : false;
        } else {
            return false;
        }
    }

    /**
     * Get redirect uri.
     *
     * @return mixed
     */
    function getRedirectUri() {
        return $this->_redirectUri;
    }

    /**
     * Get response format.
     *
     * @return string
     */
    function getResponseFormat() {
        return $this->_responseFormat;
    }

    /**
     * Set access token.
     *
     * @param string $accessToken
     *
     * @return object
     */
    function setAccessToken($accessToken) {
        $this->_accessToken = $accessToken;

        return $this;
    }

    /**
     * Set redirect uri.
     *
     * @param string $redirectUri
     *
     * @return object
     */
    function setRedirectUri($redirectUri) {
        $this->_redirectUri = $redirectUri;

        return $this;
    }

    /**
     * Set response format.
     *
     * @param string $format Could either be xml or json
     *
     * @throws Services_Soundcloud_Unsupported_Response_Format_Exception if the given response format isn't supported
     * @return object
     */
    function setResponseFormat($format) {
        if (array_key_exists($format, self::$_responseFormats)) {
            $this->_responseFormat = self::$_responseFormats[$format];
        } else {
            throw new Services_Soundcloud_Unsupported_Response_Format_Exception();
        }

        return $this;
    }

    /**
     * Set development mode.
     *
     * @param boolean $development
     *
     * @return object
     */
    function setDevelopment($development) {
        $this->_development = $development;

        return $this;
    }

    /**
     * Send a GET HTTP request.
     *
     * @param string $path URI to request
     * @param array $params Optional query string parameters
     * @param array $options Optional cURL options
     *
     * @return mixed
     * @see Soundcloud::_request()
     */
    function get($path, $params = array(), $options = array()) {
        $url = $this->_buildUrl($path, $params);

        return $this->_request($url, $options);
    }

    /**
     * Send a POST HTTP request.
     *
     * @param string $path URI to request
     * @param array $postData Optional post data
     * @param array $options Optional cURL options
     *
     * @return mixed
     * @see Soundcloud::_request()
     */
    function post($path, $postData = array(), $options = array()) {
        $url = $this->_buildUrl($path);
        $defaultOptions = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData
        );

        foreach ($defaultOptions as $key => $val) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = $val;
            }
        }

        return $this->_request($url, $options);
    }

    /**
     * Send a PUT HTTP request.
     *
     * @param string $path URI to request
     * @param array $postData Optional post data
     * @param array $options Optional cURL options
     *
     * @return mixed
     * @see Soundcloud::_request()
     */
    function put($path, $postData, $options) {
        $url = $this->_buildUrl($path);
        $defaultOptions = array(
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $postData
        
        );

        foreach ($defaultOptions as $key => $val) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = $val;
            }
        }

        return $this->_request($url, $options);
    }

    /**
     * Send a DELETE HTTP request.
     *
     * @param string $path URI to request
     * @param array $params Optional query string parameters
     * @param array $options Optional cURL options
     *
     * @return mixed
     * @see Soundcloud::_request()
     */
    function delete($path, $params = array(), $options = array()) {
        $url = $this->_buildUrl($path, $params);
        $defaultOptions = array(CURLOPT_CUSTOMREQUEST => 'DELETE');

        foreach ($defaultOptions as $key => $val) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = $val;
            }
        }

        return $this->_request($url, $options);
    }

    /**
     * Construct default HTTP headers including response format and authorization.
     *
     * @return array $headers
     */
    protected function _buildDefaultHeaders() {
        $headers = array();

        if ($this->_responseFormat) {
            array_push($headers, 'Accept: ' . $this->_responseFormat);
        }

        if ($this->_accessToken) {
            array_push($headers, 'Authorization: OAuth ' . $this->_accessToken);
        }

        return $headers;
    }

    /**
     * Construct a URL.
     *
     * @param string $path Relative or absolute URI
     * @param array $params Optional query string parameters
     * @param boolean $includeVersion Include API version
     *
     * @return string $url
     */
    protected function _buildUrl($path, $params = null, $includeVersion = true) {
        if (preg_match('/^https?\:\/\//', $path)) {
            $url = $path;
        } else {
            $url = 'https://';
            $url .= (!preg_match('/connect/', $path)) ? 'api.' : '';
            $url .= ($this->_development)
                ? self::$_domains['development']
                : self::$_domains['production'];
            $url .= '/';
            $url .= ($includeVersion) ? 'v' . self::$_apiVersion . '/' : '';
            $url .= $path;
        }

        $url .= (count($params)) ? '?' . http_build_query($params) : '';

        return $url;
    }

    /**
     * Retrieve access token.
     *
     * @param array $postData Post data
     *
     * @return mixed
     */
    protected function _getAccessToken($postData) {
        $response = $this->_request(
            $this->getAccessTokenUrl(),
            array(CURLOPT_POST => true, CURLOPT_POSTFIELDS => $postData)
        );
        $response = json_decode($response, true);

        if (array_key_exists('access_token', $response)) {
            $this->_accessToken = $response['access_token'];

            return $response;
        } else {
            return false;
        }
    }

    /**
     * Get HTTP user agent.
     *
     * @access protected
     *
     * @return string
     */
    protected function _getUserAgent() {
        return self::$_userAgent . '/' . $this->version;
    }

    /**
     * Parse HTTP response headers.
     *
     * @param string $headers
     *
     * @return array
     */
    protected function _parseHttpHeaders($headers) {
        $headers = preg_split('/\n/', trim($headers));
        $parsedHeaders = array();

        foreach ($headers as $header) {
            if (!preg_match('/\:\s/', $header)) {
                continue;
            }

            list($key, $val) = preg_split('/\:\s/', $header, 2);
            $key = str_replace('-', '_', strtolower($key));
            $val = trim($val);

            $parsedHeaders[$key] = $val;
        }

        return $parsedHeaders;
    }

    /**
     * Validates HTTP response code.
     *
     * @access protected
     *
     * @return boolean
     */
    protected function _validResponseCode($code) {
        return (bool)preg_match('/^20[0-9]{1}$/', $code);
    }

    /**
     * Performs the actual HTTP request using curl. Can be overwritten by extending classes.
     *
     * @access protected
     *
     * @param string $url
     * @param array $options Optional curl options
     *
     * @throws Services_Soundcloud_Invalid_Http_Response_Code_Exception if the response code isn't valid
     * @return mixed
     */
    protected function _request($url, $options = array()) {
        $ch = curl_init();
        $defaultOptions = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => $this->_getUserAgent()
        );

        // Can't use array_merge here since it messes up the array keys.
        foreach ($defaultOptions as $key => $val) {
            $options[$key] = $val;
        }

        if (array_key_exists(CURLOPT_HTTPHEADER, $options)) {
            $options[CURLOPT_HTTPHEADER] = array_merge(
                $this->_buildDefaultHeaders(),
                $options[CURLOPT_HTTPHEADER]
            );
        } else {
            $options[CURLOPT_HTTPHEADER] = $this->_buildDefaultHeaders();
        }

        curl_setopt_array($ch, $options);

        $data = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        $this->_lastHttpResponseHeaders = $this->_parseHttpHeaders(
            substr($data, 0, $info['header_size'])
        );
        $this->_lastHttpResponseBody = substr($data, $info['header_size']);
        $this->_lastHttpResponseCode = $info['http_code'];

        if ($this->_validResponseCode($this->_lastHttpResponseCode)) {
            return $this->_lastHttpResponseBody;
        } else {
            throw new Services_Soundcloud_Invalid_Http_Response_Code_Exception(
                null,
                0,
                $this->_lastHttpResponseBody,
                $this->_lastHttpResponseCode
            );
        }
    }

}
