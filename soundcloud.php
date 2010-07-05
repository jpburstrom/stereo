<?php
/**
 * API Wrapper for SoundCloud written in PHP with support for authication using OAuth.
 *
 * @author Anton Lindqvist <anton@qvister.se>
 * @version 1.1
 * @link http://github.com/mptre/php-soundcloud/
 */
 // Extension of Array to allow linked partitioning
 class PartitionedResource extends ArrayObject
 {
   function __construct($string) {
       $data = $this->parse_to_array($string);
       parent::__construct($data);
   }

    public function get_next_partition($soundcloud) {
      $next_partition_url =  $this['@attributes']['next-partition-href'];
      
      if ($next_partition_url != '')
      {
        preg_match("/([a-z]+)\?/", $next_partition_url, $matches);
        $method = $matches[0];

        preg_match("/(\?)(.+)/", $next_partition_url, $matches);
        $param = $matches[2];
        
        $string = $soundcloud->request($method.$param);
        return new PartitionedResource($string);
      }
      else {
        return '';
      }
    }
    
    /// Turns the string that the main PHP API returns into an array that works with our resource.  
    private function parse_to_array($string) {
      // SimpleXMLElement fails due to some of the characters in the query string, so they get stripped out here...
      if (strstr($string, 'next-partition-href'))
      {
        preg_match("/\/([a-z]+\?.*)\"/", $string, $matches);
        $queryparams = $matches[1];   
        $string = str_replace($queryparams, "", $string);       
      }
      
      $data = new SimpleXMLElement($string);
      $data = get_object_vars($data);
      // ...and replaced here.  
      if ($data['@attributes']['next-partition-href'])
      {
        $data['@attributes']['next-partition-href'] = $data['@attributes']['next-partition-href'].$queryparams;
      }
      return $data;
    }
} 
 
 
class Soundcloud {

    const VERSION = '1.1';

    function __construct($consumer_key, $consumer_secret, $oauth_token = null, $oauth_token_secret = null) {      
        # Please add your API host and version information here.
        $web = 'sandbox-soundcloud.com/';
        $api = 'api.'.$web.'v1/'; // Version data can be added here, with a trailing slash.

        # Setting up url data
        $this->api = 'http://'.$api;
        $oauth_access = $this->api.'oauth/access_token';
        $oauth_request = $this->api.'oauth/request_token';
        $oauth_auth = "http://".$web.'oauth/authorize';
        $this->oauth = array('access' => $oauth_access, 'request' => $oauth_request, 'authorize' => $oauth_auth);
      
        if ($consumer_key == null) {
            throw Exception("Error:  Consumer Key required for all requests, even those to public resources.");
        }
        $this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
        $this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);

        if (!empty($oauth_token) && !empty($oauth_token_secret)) {
            $this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
        } else {
            $this->token = null;
        }
    }

    function get_authorize_url($token) {
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }

        return $this->_get_url('authorize') . '?oauth_token=' . $token;
    }

    function get_request_token($oauth_callback) {
        $request = $this->request(
            $this->_get_url('request'),
            'POST',
            array('oauth_callback' => $oauth_callback)
        );
        $token = $this->_parse_response($request);

        $this->token = new OAuthConsumer(
            $token['oauth_token'],
            $token['oauth_token_secret']
        );

        return $token;
    }

    function get_access_token($token) {
        $response = $this->request(
            $this->_get_url('access'),
            'POST',
            array('oauth_verifier' => $token)
        );
        $token = $this->_parse_response($response);
        $this->token = new OAuthConsumer(
            $token['oauth_token'],
            $token['oauth_token_secret']
        );

        return $token;
    }

    function upload_track($post_data, $asset_data_mime = null, $artwork_data_mime = null) {
        $body = '';
        $boundary = '---------------------------' . md5(rand());
        $crlf = "\r\n";
        $headers = array(
            'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
            'Content-Length' => 0
        );

        foreach ($post_data as $key => $val) {
            if (in_array($key, array('track[asset_data]', 'track[artwork_data]'))) {
                $body .= '--' . $boundary . $crlf;
                $body .= 'Content-Disposition: form-data; name="' . $key . '"; filename="' . basename($val) . '"' . $crlf;
                $body .= 'Content-Type: ' . (($key == 'track[asset_data]') ? $asset_data_mime : $artwork_data_mime) . $crlf;
                $body .= $crlf;
                $body .= file_get_contents($val) . $crlf;
            } else {
                $body .= '--' . $boundary . $crlf;
                $body .= 'Content-Disposition: form-data; name="' . $key .'"' . $crlf;
                $body .= $crlf;
                $body .= $val . $crlf;
            }
        }

        $body .= '--' . $boundary . '--' . $crlf;
        $headers['Content-Length'] += strlen($body);

        return $this->request('tracks', 'POST', $body, $headers);
    }

    function request($resource, $method = 'GET', $args = array(), $headers = null) {
        if (!preg_match('/http:\/\//', $resource)) {
            $url = $this->api.$resource;
        } else {
            $url = $resource;
        }

        if (stristr($headers['Content-Type'], 'multipart/form-data')) {
            $body = false;
        } elseif (stristr($headers['Content-Type'], 'application/xml')) {
            $body = false;
        } else {
            $body = true;
        }

        $request = OAuthRequest::from_consumer_and_token(
            $this->consumer,
            $this->token,
            $method,
            $url,
            ($body === true) ? $args : null
        );
        $request->sign_request($this->sha1_method, $this->consumer, $this->token);
        
        // Formerly $url was $request->get_normalized_http_url(), which prevented params from being passed.  
         return $this->_curl($url, $request, $args, $headers);
        
    }

    private function _build_header($headers) {
        $h = array();

        if (count($headers) > 0) {
            foreach ($headers as $key => $val) {
                $h[] = $key . ': ' . $val;
            }

            return $h;
        } else {
            return $headers;
        }
    }

    private function _curl($url, $request, $post_data = null, $headers = null) {
        $ch = curl_init();
        $mime = (stristr($headers['Content-Type'], 'multipart/form-data')) ? true : false;
        $headers['User-Agent'] = (isset($headers['User-Agent']))
            ? $headers['User-Agent']
            : 'PHP SoundCloud/' . self::VERSION;
        $headers['Content-Length'] = (isset($headers['Content-Length']))
            ? $headers['Content-Length']
            : 0;
        $headers = (is_array($headers)) ? $this->_build_header($headers) : array();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true
        );

        if (in_array($request->get_normalized_http_method(), array('DELETE', 'PUT'))) {
            $options[CURLOPT_CUSTOMREQUEST] = $request->get_normalized_http_method();
            $options[CURLOPT_POSTFIELDS] = '';
        }

        if (is_array($post_data) && count($post_data) > 0 || $mime === true) {
            $options[CURLOPT_POSTFIELDS] = (is_array($post_data) && count($post_data) == 1)
                ? ((isset($post_data[key($post_data)])) ? $post_data[key($post_data)] : $post_data)
                : $post_data;
            $options[CURLOPT_POST] = true;
        }

        $headers[] = $request->to_header();
        $options[CURLOPT_HTTPHEADER] = $headers;

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $this->http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $response;
    }

    private function _get_url($type) {
        return (array_key_exists($type, $this->oauth))
            ? $this->oauth[$type]
            : false;
    }

    private function _parse_response($response) {
        parse_str($response, $output);

        return (count($output) > 0) ? $output : false;
    }
}
