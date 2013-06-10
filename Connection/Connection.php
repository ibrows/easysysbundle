<?php

namespace Ibrows\EasySysBundle\Connection;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Output\Output;

use Symfony\Component\Console\Output\NullOutput;

/**
 * @author marcsteiner
 *
 */
class Connection
{

    /**
     * @var OutputInterface
     */
    protected $out;

    const API_FORMAT_JSON = 'json';

    const CURL_TIMEOUT_IN_SECS = 10;

    private $availableFormats = array(
            self::API_FORMAT_JSON
    );
    private $format = self::API_FORMAT_JSON; //json
    private $callUrl;
    private $signatureKey;
    private $userId;

    /**
     * Constructor
     * Creates the call-uri
     */
    public function __construct($serviceUri, $companyName, $apiKey, $signatureKey, $userId, $format)
    {
        $this->setConfig($serviceUri, $companyName, $apiKey, $signatureKey, $userId, $format);
        $this->out = new NullOutput();
    }

    public function setConfig($serviceUri, $companyName, $apiKey, $signatureKey, $userId, $format = self::API_FORMAT_JSON)
    {

        $this->signatureKey = $signatureKey;
        $this->userId = $userId;
        $this->callUrl = $serviceUri . DIRECTORY_SEPARATOR . $companyName . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . $apiKey . DIRECTORY_SEPARATOR;
        $this->setFormat($format);
    }

    public function setOutput(OutputInterface $out)
    {
        $this->out = $out;
    }

    /**
     *
     * @param <type> $url
     * @param <type> $urlParams
     * @param <type> $postParams
     * @param <type> $method
     * @param <type> $getRawData
     * @return <type>
     */
    public function call($resource, $urlParams = array(), $postParams = array(), $method = "GET", $limit = 0, $offset = 0, $order_by = null, $getRawData = false)
    {
        if($method == 'GET'){
            $urlParams['limit'] = $limit;
            $urlParams['offset'] = $offset;
            $urlParams['order_by'] = $order_by;
        }
        $finalUrl = $this->callUrl . $resource . $this->getUrlParameterString($urlParams);

        $this->debug(sprintf('<comment>%s</comment> has been called', $finalUrl), $method, $postParams);

        $data = $this->makeCurlCall($finalUrl, $postParams, $method);
        if (!$getRawData) {
            return $this->returnResult($data);
        }
        return $data;
    }

    /**
     * Gets the current api response format
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Sets the current api response format
     * @param string $format
     * @throws ConnectionException The exception is thrown when an unsupported format is supplied.
     */
    public function setFormat($format)
    {
        if (!in_array($format, $this->availableFormats))
            throw new ConnectionException('500', sprintf('The Format %s is not available', $format));
        $this->format = $format;
    }

    /**
     * Returns all available api formats
     * @return array
     */
    public function getAvailableFormats()
    {
        return $this->availableFormats;
    }

    /**
     * Creates a curl call for the given url, automatically validates the return value for errors.
     * If an error has been found a new ConnectionException will be thrown.
     *
     * @param string $url
     * @param array $postParams Parameters for Post and Put-Requests
     * @param string $method HTTP-Method (GET, PUT, POST, DELETE)
     * @return string $data the result in the format defined in $this->format
     */
    private function makeCurlCall($url, $postParams = array(), $method = 'GET')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, self::CURL_TIMEOUT_IN_SECS);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        //enable those two lines in order to debug api calls with a proxy like charles
        //curl_setopt($curl, CURLOPT_PROXY, "127.0.0.1");
        //curl_setopt($curl, CURLOPT_PROXYPORT, 8890);

        $postData = '';

        if ($postParams) {
            $postData = json_encode($postParams);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        }

        $headers = array();
        $headers[] = 'Accept: application/' . $this->getFormat();
        $headers[] = 'Signature: ' . md5($url . $postData . $this->signatureKey);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = curl_exec($curl);
        $this->checkForError($curl, $data);

        return $data;
    }

    /**
     * Checks for any existing errors in the api response.
     * If an error has been found a new Easysys will be thrown.
     *
     * @param string $data the result data
     * @throws ConnectionException
     */
    private function checkForError($curl, $data)
    {
        $curlInfo = curl_getinfo($curl);
        if (isset($curlInfo['http_code']) && ($curlInfo['http_code'] != 200 && $curlInfo['http_code'] != 201)) {
            throw new ConnectionException($curlInfo['http_code'] ? $data : 'could not get a response from the service', $curlInfo['http_code'] ? $curlInfo['http_code'] : 500);
        }
    }

    /**
     * Automatically converts the results to a more readable format.
     *
     * @param string $data the result from an api call
     * @return mixed - the result in the format defined in $this->format
     */
    private function returnResult($data)
    {
        switch ($this->format) {
        case 'json':
            $data = json_decode($data, true);
            if (!$data && !is_array($data)) {
                throw new ConnectionException('Invalid JSON File returned');
            }
            return $data;
            break;
        case 'xml':
            return new SimpleXMLElement($data);
            break;
        default:
            return $data;
        }
    }

    /**
     * If the debug mode is on, the message will be sent to the browser
     * @param string $message
     */
    private function debug($message, $method, $postParams = array())
    {
        if ($this->out->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $debugString = sprintf('<info>%s</info> - <info>%s</info> %s', date('d.m.Y H:i:s', time()), $method, $message);
            if ($postParams) {
                $debugString .= " - Params: (";
                $debugString .= sprintf('<comment>%s</comment>', urldecode(http_build_query($postParams)));
                $debugString .= ")";
            }
            $this->out->writeln($debugString);

        }
    }

    /**
     * Builds a valid http query
     *
     * @param array $urlParams
     * @return string
     */
    private function getUrlParameterString(array $urlParams)
    {
        if (!$urlParams)
            return "";
        return "?" . http_build_query($urlParams);
    }

    public function getUserId()
    {
        return $this->userId;
    }

}
