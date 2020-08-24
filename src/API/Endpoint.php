<?php

namespace Elbucho\AlpacaV2\API;
use Elbucho\AlpacaV2\Exceptions\InvalidResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Endpoint
{
    /**
     * Access Key
     *
     * @access  protected
     * @var     string
     */
    protected $key;

    /**
     * Secret Key
     *
     * @access  protected
     * @var     string
     */
    protected $secret;

    /**
     * Endpoint URL
     *
     * @access  protected
     * @var     string
     */
    protected $endpoint;

    /**
     * Class constructor
     *
     * @access  public
     * @param   string  $key        // Access Key
     * @param   string  $secret     // Secret Access Key
     * @param   string  $endpoint   // Endpoint base url (eg. https://paper-api.alpaca.markets)
     * @return  Endpoint
     */
    public function __construct(
        string $key,
        string $secret,
        string $endpoint
    ) {
        $this->key = $key;
        $this->secret = $secret;
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Send a GET request to the given path with the provided parameters
     * and return an array of data
     *
     * @access  protected
     * @param   string  $path
     * @param   array   $params
     * @return  array
     * @throws  GuzzleException
     */
    protected function get(string $path, array $params = []): array
    {
        $client = new Client();
        $response = $client->request(
            'GET',
            $this->buildUrl($path),
            [
                'headers'   => $this->getHeaders(),
                'query'     => $params
            ]
        );

        $return = json_decode($response->getBody(), true);

        return (is_array($return) ? $return : [$return]);
    }

    /**
     * Send a POST request to the given path with the provided data,
     * and return an array of data
     *
     * @access  protected
     * @param   string  $path
     * @param   array   $data
     * @return  array
     * @throws  GuzzleException
     */
    protected function post(string $path, array $data = []): array
    {
        $client = new Client();
        $response = $client->request(
            'POST',
            $this->buildUrl($path),
            [
                'headers'   => $this->getHeaders(),
                'body'      => $data
            ]
        );

        $return = json_decode($response->getBody(), true);

        return (is_array($return) ? $return : [$return]);
    }

    /**
     * Send a PATCH request to the given path with the provided data,
     * and return an array of data
     *
     * @access  protected
     * @param   string  $path
     * @param   array   $data
     * @return  array
     * @throws  GuzzleException
     */
    protected function patch(string $path, array $data = []): array
    {
        $client = new Client();
        $response = $client->request(
            'PATCH',
            $this->buildUrl($path),
            [
                'headers'   => $this->getHeaders(),
                'body'      => $data
            ]
        );

        $return = json_decode($response->getBody(), true);

        return (is_array($return) ? $return : [$return]);
    }

    /**
     * Send a DELETE request to the given path and return an array of data
     *
     * @access  protected
     * @param   string  $path
     * @param   bool    $status
     * @return  array
     * @throws  GuzzleException
     */
    protected function delete(string $path, bool &$status): array
    {
        $client = new Client();
        $response = $client->request(
            'DELETE',
            $this->buildUrl($path),
            [
                'headers'   => $this->getHeaders()
            ]
        );

        $return = json_decode($response->getBody(), true);
        $statusCode = $response->getStatusCode();
        $status = ($statusCode == 200);

        return (is_array($return) ? $return : [$return]);
    }

    /**
     * Return formatted data from a given array, key, and ruleset
     *
     * @deprecated
     * @access  protected
     * @param   array   $data
     * @param   string  $key
     * @param   array   $rules
     * @return  mixed
     * @throws  InvalidResponseException
     */
    protected function getFormattedData(array $data, string $key, array $rules)
    {
        if ( ! array_key_exists('type', $rules)) {
            throw new InvalidResponseException(sprintf(
                'No rule specified for the key %s',
                $key
            ));
        }

        if ( ! array_key_exists('required', $rules) or $rules['required'] == false) {
            $required = false;
        } else {
            $required = true;
        }

        if ($required and ! array_key_exists($key, $data)) {
            throw new InvalidResponseException(sprintf(
                'Key %s is required for this data, but is missing',
                $key
            ));
        }

        $return = [];

        try {
            switch($rules['type']) {
                case 'datetime':
                    $return[$key] = (empty($data[$key]) ? null :
                        Helper::convertToDateTime($data[$key]));
                    break;
                case 'int':
                    $return[$key] = (int) $data[$key];
                    break;
                case 'float':
                    $return[$key] = (float) $data[$key];
                    break;
                case 'bool':
                    $return[$key] = (strtolower($data[$key]) == 'true');
                    break;
                case 'array':
                    $return[$key] = (is_array($data[$key]) ? $data[$key] : [$data[$key]]);
                    break;
                case 'string':
                default:
                    $return[$key] = $data[$key];
            }
        } catch (\Exception $e) {
            throw new InvalidResponseException($e->getMessage());
        }

        return $return;
    }

    /**
     * Build the URL from the given path and the provided endpoint
     *
     * @access  private
     * @param   string  $path
     * @return  string
     */
    private function buildUrl(string $path): string
    {
        if (preg_match("/\/$/", $this->endpoint)) {
            $this->endpoint = substr($this->endpoint, 0, -1);
        }

        if ( ! preg_match("/v2$/", $this->endpoint)) {
            $this->endpoint .= "/v2";
        }

        if ( ! preg_match("/^\//", $path)) {
            $path = '/' . $path;
        }

        return $this->endpoint . $path;
    }

    /**
     * Return an array of headers to pass to the Guzzle client
     *
     * @access  private
     * @param   void
     * @return  array
     */
    private function getHeaders(): array
    {
        return [
            'APCA-API-KEY-ID'       => $this->key,
            'APCA-API-SECRET-KEY'   => $this->secret
        ];
    }
}