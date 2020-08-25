<?php

namespace Elbucho\AlpacaV2;

class Client
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
     * @return  Client
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
     * Return a new instance of API\Account
     * 
     * @access  public
     * @param   void
     * @return  API\Account
     */
    public function account(): Api\Account
    {
        return new API\Account($this->key, $this->secret, $this->endpoint);
    }

    /**
     * Return a new instance of API\Calendar
     *
     * @access  public
     * @param   void
     * @return  API\Calendar
     */
    public function calendar(): Api\Calendar
    {
        return new API\Calendar($this->key, $this->secret, $this->endpoint);
    }

    /**
     * Return a new instance of API\Clock
     *
     * @access  public
     * @param   void
     * @return  API\Clock
     */
    public function clock(): Api\Clock
    {
        return new API\Clock($this->key, $this->secret, $this->endpoint);
    }

    /**
     * Return a new instance of API\Orders
     *
     * @access  public
     * @param   void
     * @return  API\Orders
     */
    public function orders(): Api\Orders
    {
        return new API\Orders($this->key, $this->secret, $this->endpoint);
    }
}