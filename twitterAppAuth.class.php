<?php
/**
* PHP class that implements Twitter's Application-only authentication model
*
* This class implements Twitter's Application-only authentication model described at {@link hhttps://dev.twitter.com/docs/auth/application-only-auth}
*
* @version 0.01
* @author Sinan Taga
* @license MIT http://opensource.org/licenses/MIT
*/
class twitterAppAuth {

    public $consumerKey = 'YOUR_CONSUMER_KEY';
    public $consumerSecret = 'YOUR CONSUMER_KEY';
    public $debug = false;

    // url to send data to for authentication
    private $_tokenUrl = 'https://api.twitter.com/oauth2/token';

    // 
    private $_apiUrl   = 'https://api.twitter.com/1.1/';

    private $_bearerToken = '';

    const USER_AGENT = 'TwitterAppAuth application-only Auth Class';


    public function __construct()
    {
        // TODO:
        // A user token could be already saved in our DB
        // check if it exists. A token will be valid until
        // invalidated by an invalidate request
    }

    /**
    * Sample method to test the Class
    * Gets the information of a user
    * 
    * @param String $username Twitter username 
    * @return Array returns the JSON decoded respone
    */
    public function getUserInfo($username)
    {
        $url = $this->_apiUrl . 'users/show.json?screen_name=' . $username;

        $params = array( 
            'GET /1.1/users/show.json?screen_name' . $username . ' HTTP/1.1',
            'Host: api.twitter.com',
            'User-Agent: ' . self::USER_AGENT,
            "Authorization: Bearer ".$this->_bearerToken."",
        );

        return json_decode($this->_makeRequest($params, $url));
    }

    /**
    *   Gets the "Bearer access token from twitter"
    */
    private function _getBearerToken()
    {
        // if there is a token already 
        // we dont need to ask Twitter for a new token.
        // The token will be valid until, it is invalidated
        if ($this->bearerToken) {
            return;
        }

        // From Twitter
        // URL encode the consumer key and the consumer secret according to RFC 1738. 
        // Note that at the time of writing, this will not actually change the consumer key and secret, 
        // but this step should still be performed in case the format of those values changes in the future.
        $encodedConsumerKey    = urlencode($this->consumerKey);
        $encodedConsumerSecret = urlencode($this->consumerSecret);

        // Concatenate the encoded consumer key, a colon character ":", 
        // and the encoded consumer secret into a single string.
        // Base64 encode the string from the previous step.
        $bearerToken = base64_encode($encodedConsumerKey.':'.$encodedConsumerSecret);

        // Parameters for cURL 
        $curlParams = array( 
            'POST /oauth2/token HTTP/1.1',
            'Host: api.twitter.com',
            'User-Agent: ' . self::USER_AGENT,
            'Authorization: Basic ' . $bearerToken . '',
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
            'Content-Length: 29'
        );

        // TODO: check if token type is 'Bearer'
        // and maybe save token to DB for future reference
        $this->bearerToken = $this->_makeRequest($curlParams, $this->_tokenUrl, 'POST', 'grant_type=client_credentials');
    }

    /**
    * Invalidated current Bearer token
    */
    private function _invalidateBearerToken($currentToken)
    {
        $encodedConsumerKey    = urlencode($this->consumerKey);
        $encodedConsumerSecret = urlencode($this->consumerSecret);
        $bearerToken = base64_encode($encodedConsumerKey.':'.$encodedConsumerSecret);

        // url to send data to for authentication
        $url = "https://api.twitter.com/oauth2/invalidate_token"; 

        $headers = array( 
            'POST /oauth2/invalidate_token HTTP/1.1',
            'Host: api.twitter.com',
            'User-Agent: ' . self::USER_AGENT,
            'Authorization: Basic ' . $bearerToken,
            'Accept: */*',
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . (strlen($currentToken) + 13)
        );

        return $this->_makeRequest($params, $url, 'POST', 'access_token=' . $currentToken);
    }

    /**
    * Wrap the cURL requests
    */
    private function _makeRequest($params, $url, $type = 'GET', $postFields = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);            // set url to send to
        curl_setopt($ch, CURLOPT_HTTPHEADER, $params);  // set custom headers

        if ($type == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);          // send as post
            if ($postFields) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields); // post fields to be sent
            }
            curl_setopt($ch, CURLOPT_HEADER, 1);        // send custom headers
        }      
        
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $response = curl_exec ($ch);

        curl_close($ch); // close curl
        
        if ($response === false ) {
            echo curl_error($ch), '<br>';
            echo curl_errno($ch);
            die();
        }else{
            return $response;
        }
    }
}
