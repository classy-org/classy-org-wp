<?php

class ClassyAPIClient
{
    const CLASSY_API_BASEURL = 'https://api.classy.org';
    const CLASSY_API_VERSION = '2.0';

    private static $instance;
    private $clientId;
    private $clientSecret;

    /**
     * Private constructor, use factory.
     *
     * @param $clientId API client ID
     * @param $clientSecret API client secret
     */
    private function __construct($clientId, $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * Factory method for API singleton.
     *
     * @param $clientId API client ID
     * @param $clientSecret API client secret
     * @return ClassyAPIClient
     */
    public static function getInstance($clientId, $clientSecret)
    {
        if ((self::$instance instanceof ClassyAPIClient) === false)
        {
            self::$instance = new ClassyAPIClient($clientId, $clientSecret);
        }

        return self::$instance;
    }

    /**
     * Create an access token using key and secret.
     *
     * @return mixed
     */
    public function getAccessToken()
    {
        $cacheKey = ClassyOrg::CACHE_KEY_PREFIX . '_ACCESS_TOKEN_' . $this->clientId;
        $token = get_transient($cacheKey);

        if ($token === false)
        {
            $options = array(
                'http' => array(
                    'header' => 'Content-Type: text/plain',
                    'method' => 'POST'
                )
            );

            $context = stream_context_create($options);
            $content = "client_id={$this->clientId}&client_secret={$this->clientSecret}&grant_type=client_credentials";
            $result = file_get_contents('https://api.classy.org/oauth2/auth?' . $content, false, $context);

            $token = json_decode($result, true);
            set_transient($cacheKey, $token['access_token'], ($token['expires_in'] - 60));
            $token = $token['access_token'];
        }

        return $token;
    }

    /**
     * Make an API request to Classy API.
     *
     * @param string $url Endpoint URL (e.g. /campaigns/999999)
     * @param string $method HTTP Method (e.g. 'GET', 'POST', 'PUT')
     * @param array $params URL Parameters to be sent as '?key1=value1&key2=value2'
     * @return string Content response
     */
    public function request($url, $method = 'GET', $params = array()) // FIXME: more params
    {
        $url = self::CLASSY_API_BASEURL
            . '/' . self::CLASSY_API_VERSION
            . '/' . $url
            . '?' . http_build_query($params);

        $token = $this->getAccessToken();

        $options = array(
            'http' => array(
                'method' => $method,
                'header' => "Content-Type: text/plain\r\n"
                    . "Authorization: Bearer $token\r\n"
            )
        );

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        // FIXME: Error handle

        return $response;
    }
}