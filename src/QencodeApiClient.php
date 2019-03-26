<?php

namespace Qencode;

use Qencode\Exceptions\QencodeApiException;
use Qencode\Exceptions\QencodeException;
use Qencode\Classes\TranscodingTask;

/**
 * Class QencodeClient
 */
class QencodeApiClient
{
    /**
     * Qencode API key
     * @var string
     */
    private $key = '';
    private $access_token;

    private $lastResponseRaw;

    private $lastResponse;

    public $url = 'https://api.qencode.com/';
    public $version = 'v1';
    private $supported_versions = array('v1', 'v1.1');

    const USER_AGENT = 'Qencode PHP API SDK 1.1';

    /**
     * Maximum amount of time in seconds that is allowed to make the connection to the API server
     * @var int
     */
    public $curlConnectTimeout = 20;

    /**
     * Maximum amount of time in seconds to which the execution of cURL call will be limited
     * @var int
     */
    public $curlTimeout = 20;

    /**
     * @param string $key Qencode Project API key
     * @param string $url Optional url to any different API endpoint
     * @param string $version Optional API version
     * @throws \Qencode\Exceptions\QencodeException if the library failed to initialize
     */
    public function __construct($key, $url = null, $version = null)
    {
        if (strlen($key) < 12) {
            throw new QencodeException('Missing or invalid Qencode project api key!');
        }
        if ($url) {
            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                throw new QencodeException('Invalid API endpoint url!');
            }
            $this->url = $url;
        }
        if ($version) {
            $version = strtolower($version);
            if (in_array($version, $this->supported_versions)) {
                $this->version = $version;
                if ($version == 'v1.1') {
                    $this->url = $this->v1_1_get_endpoint();
                }
            }
            else throw new QencodeException('Unsupported API version: '.$version);
        }
        $this->key = $key;
        $this->getAccessToken();
    }

    private function v1_1_get_endpoint() {
        $api_host = file_get_contents($this->url.'/v1.1');
        return 'https://'.$api_host;
    }

    private function getAccessToken() {
        $response = $this->post("access_token", array('api_key' => $this->key));
        $this->access_token = $response['token'];
    }

    /**
     * Returns total available item count from the last request if it supports paging (e.g order list) or null otherwise.
     *
     * @return int|null Item count
     */
    public function getItemCount()
    {
        return isset($this->lastResponse['paging']['total']) ? $this->lastResponse['paging']['total'] : null;
    }

    /**
     * Perform a POST request to the API
     * @param string $path Request path (e.g. 'start_encode')
     * @param array $data Request body data as an associative array
     * @param array $params Additional GET parameters as an associative array
     * @return mixed API response
     * @throws \Qencode\Exceptions\QencodeApiException if the API call status code is not in the 2xx range
     * @throws QencodeException if the API call has failed or the response is invalid
     */
    public function post($path, $params = [], $url = null)
    {
        return $this->request('POST', $path, $params);
    }

    /**
     * Return raw response data from the last request
     * @return string|null Response data
     */
    public function getLastResponseRaw()
    {
        return $this->lastResponseRaw;
    }

    /**
     * Return decoded response data from the last request
     * @return array|null Response data
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Internal request implementation
     * @param string $method POST, GET, etc.
     * @param string $path (Can be just method name like 'start_encode' or full url to be called)
     * @param array $params
     * @param mixed $data
     * @return
     * @throws \Qencode\Exceptions\QencodeApiException
     * @throws \Qencode\Exceptions\QencodeException
     */
    private function request($method, $path, array $params = [])
    {
        $this->lastResponseRaw = null;
        $this->lastResponse = null;

        if (strpos(strtolower($path), 'http') === 0) {
            $url = $path;
        }
        else {
            $url = $this->url . '/' . $this->version . '/' . trim($path, '/');
        }
        echo "URL: ".$url."\n";
        if (!empty($params) & is_array($params)) {
            $params = http_build_query($params);
        }
        #echo $url;
        #echo "\n";
        #echo $params."\n\n";
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_USERPWD, $this->key);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->curlConnectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->curlTimeout);

        curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);


        $this->lastResponseRaw = curl_exec($curl);

        $errorNumber = curl_errno($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($errorNumber) {
            throw new QencodeException('CURL: ' . $error, $errorNumber);
        }

        #echo $this->lastResponseRaw;
        #echo "\n\n";
        $this->lastResponse = $response = json_decode($this->lastResponseRaw, true);
        //print_r($response);

        if (!isset($response['error'])) {
            $e = new QencodeException('Invalid API response');
            $e->rawResponse = $this->lastResponseRaw;
            throw $e;
        }
        /*$status = (int)$response['code'];
        if ($status < 200 || $status >= 300) {
            $e = new QencodeApiException((string)$response['result'], $status);
            $e->rawResponse = $this->lastResponseRaw;
            throw $e;
        }*/
        if ($response['error'] != 0) {
            $e = new QencodeApiException($response['message']);
            $e->rawResponse = $this->lastResponseRaw;
            throw $e;
        }
        return $response;
    }

    public function createTask() {
        $response = $this->post('create_task', array('token' => $this->access_token));
        $task = new TranscodingTask($this, $response['task_token']);
        return $task;
    }
}