<?php

namespace Elbucho\AlpacaV2\API;
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