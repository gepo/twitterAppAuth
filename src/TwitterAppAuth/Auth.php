<?php
namespace TwitterAppAuth;

/**
* PHP class that implements Twitter's Application-only authentication model
*
* This class implements Twitter's Application-only authentication model described at {@link hhttps://dev.twitter.com/docs/auth/application-only-auth}
*
* @version 0.1
* @author Sinan Taga
* @author Gennady Telegin <gtelegin@gmail.com>
* @license MIT http://opensource.org/licenses/MIT
*/
class Auth {
    const USER_AGENT = 'TwitterAppAuth v 0.0.2';
    
    const API_URL   = 'https://api.twitter.com/1.1/';
    const TOKEN_URL = 'https://api.twitter.com/oauth2/token';

    private $consumerKey;
    private $consumerSecret;
    private $token = null;

    public function __construct($consumerKey, $consumerSecret)
    {
        $this->consumerKey    = $consumerKey;
        $this->consumerSecret = $consumerSecret;
    }

    /**
    * Gets the information of a user by his username
    * 
    * @param String $username Twitter username 
    * @return Array returns the JSON decoded respone
    */
    public function getUserInfo($username)
    {
        $url = self::API_URL . 'users/show.json?screen_name=' . $username;

        $params = array( 
            'GET /1.1/users/show.json?screen_name' . $username . ' HTTP/1.1',
            'Host: api.twitter.com',
            'User-Agent: ' . self::USER_AGENT,
            "Authorization: Bearer " . $this->getToken(),
        );

        return json_decode($this->makeRequest($params, $url), true);
    }

    public function getToken()
    {
        if (!$this->token) {
            $this->token = $this->getBearerToken();
        }
        
        return $this->token;
    }
    
    /**
    *   Gets the "Bearer access token from twitter"
    */
    private function getBearerToken()
    {
        // From Twitter
        // URL encode the consumer key and the consumer secret according to RFC 1738. 
        // Note that at the time of writing, this will not actually change the consumer key and secret, 
        // but this step should still be performed in case the format of those values changes in the future.
        $encodedConsumerKey    = urlencode($this->consumerKey);
        $encodedConsumerSecret = urlencode($this->consumerSecret);

        // Concatenate the encoded consumer key, a colon character ":", 
        // and the encoded consumer secret into a single string.
        // Base64 encode the string from the previous step.
        $bearerTokenRequest = base64_encode($encodedConsumerKey.':'.$encodedConsumerSecret);

        // Parameters for cURL 
        $curlParams = array( 
            'POST /oauth2/token HTTP/1.1',
            'Host: api.twitter.com',
            'User-Agent: ' . self::USER_AGENT,
            'Authorization: Basic ' . $bearerTokenRequest . '',
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
            'Content-Length: 29'
        );

        $responseBody = $this->makeRequest($curlParams, self::TOKEN_URL, 'POST', 'grant_type=client_credentials');
        $response = json_decode($responseBody, true);
        
        if (!isset($response['token_type']) || $response['token_type'] != 'bearer') {
            throw new \Exception('Could not get bearer access token');
        }
        
        return $response['access_token'];
    }

    /**
    * Invalidated current Bearer token
    */
    private function invalidateBearerToken($currentToken)
    {
        $encodedConsumerKey    = urlencode($this->consumerKey);
        $encodedConsumerSecret = urlencode($this->consumerSecret);
        
        $bearerToken = base64_encode($encodedConsumerKey . ':' . $encodedConsumerSecret);

        $url = "https://api.twitter.com/oauth2/invalidate_token"; 

        $curlParams = array( 
            'POST /oauth2/invalidate_token HTTP/1.1',
            'Host: api.twitter.com',
            'User-Agent: ' . self::USER_AGENT,
            'Authorization: Basic ' . $bearerToken,
            'Accept: */*',
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . (strlen($currentToken) + 13)
        );

        return $this->makeRequest($curlParams, $url, 'POST', 'access_token=' . $currentToken);
    }

    /**
    * Wrap the cURL requests
    */
    private function makeRequest($params, $url, $type = 'GET', $postFields = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $params);
        
        if ($type == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($postFields) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            }
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = curl_exec ($ch);
        
        curl_close($ch);
        if ($response === false ) {
            throw new \Exception('Curl error[' . curl_errno($ch) . '] ' . curl_error($ch));
        } else{
            return $response;
        }
    }
}