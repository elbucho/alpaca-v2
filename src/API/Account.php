<?php

namespace Elbucho\AlpacaV2\API;
use Elbucho\AlpacaV2\Exceptions\InvalidResponseException;
use GuzzleHttp\Exception\GuzzleException;

final class Account extends Endpoint
{
    /**
     * Current account array
     *
     * @access  private
     * @var     array
     */
    private $account = [];

    /**
     * Date that the account array was last updated
     *
     * @access  private
     * @var     \DateTimeImmutable
     */
    private $lastUpdated;

    /**
     * Account refresh time (in minutes)
     *
     * If more than the given time has elapsed since we last fetched the account info,
     * fetch the account info once again.
     *
     * @access  private
     * @var     int
     */
    private $refreshTime = 15;

    /**
     * Endpoint path
     *
     * @access  private
     * @var     string
     */
    private $path = '/account';

    /**
     * Return current buying power
     *
     * [
     *   'buying_power'             => float,
     *   'daytrading_buying_power'  => float,
     *   'regt_buying_power'        => float,
     *   'multiplier'               => float
     * ]
     *
     * @access  public
     * @param   void
     * @return  array
     * @throws  InvalidResponseException
     */
    public function getBuyingPower(): array
    {
        $account = $this->getAccount();
        $return = [];
        $keys = [
            'buying_power',
            'daytrading_buying_power',
            'regt_buying_power',
            'multiplier'
        ];

        foreach ($keys as $key) {
            if ( ! array_key_exists($key, $account)) {
                throw new InvalidResponseException(
                    'Unable to get Account buying power'
                );
            }

            $return[$key] = (float) $account[$key];
        }

        return $return;
    }

    /**
     * Return the value in this account
     *
     * [
     *   'cash'               => float,
     *   'portfolio_value'    => float,
     *   'long_market_value'  => float,
     *   'short_market_value' => float,
     *   'equity'             => float,
     *   'last_equity'        => float,
     *   'sma'                => float
     * ]
     *
     * @access  public
     * @param   void
     * @return  array
     * @throws  InvalidResponseException
     */
    public function getValue(): array
    {
        $account = $this->getAccount();
        $return = [];
        $keys = [
            'cash',
            'portfolio_value',
            'long_market_value',
            'short_market_value',
            'equity',
            'last_equity',
            'sma'
        ];

        foreach ($keys as $key) {
            if ( ! array_key_exists($key, $account)) {
                throw new InvalidResponseException(
                    'Unable to get Account value'
                );
            }

            $return[$key] = (float) $account[$key];
        }

        return $return;
    }

    /**
     * Return account status and settings
     *
     * [
     *   'id'                      => string,
     *   'account_number'          => int,
     *   'created_at'              => datetime,
     *   'status'                  => string,
     *   'currency'                => string,
     *   'pattern_day_trader'      => bool,
     *   'trade_suspended_by_user' => bool,
     *   'trading_blocked'         => bool,
     *   'transfers_blocked'       => bool,
     *   'account_blocked'         => bool,
     *   'shorting_enabled'        => bool,
     *   'daytrade_count'          => int
     * ]
     *
     * @access  public
     * @param   void
     * @return  array
     * @throws  InvalidResponseException
     * @throws  \Exception
     */
    public function getAccountInfo(): array
    {
        $account = $this->getAccount();
        $return = [];
        $keys = [
            'id'                      => 'string',
            'account_number'          => 'string',
            'created_at'              => 'datetime',
            'status'                  => 'string',
            'currency'                => 'string',
            'pattern_day_trader'      => 'bool',
            'trade_suspended_by_user' => 'bool',
            'trading_blocked'         => 'bool',
            'transfers_blocked'       => 'bool',
            'account_blocked'         => 'bool',
            'shorting_enabled'        => 'bool',
            'daytrade_count'          => 'int'
        ];

        foreach ($keys as $key => $type) {
            if ( ! array_key_exists($key, $account)) {
                throw new InvalidResponseException(
                    'Unable to get Account info'
                );
            }

            switch ($type) {
                case 'int':
                    $return[$key] = (int) $account[$key];
                    break;
                case 'bool':
                    $return[$key] = (strtolower($account[$key]) == 'true');
                    break;
                case 'datetime':
                    $return[$key] = Helper::convertToDateTime($account[$key]);
                    break;
                case 'string':
                default:
                    $return[$key] = $account[$key];
            }
        }

        return $return;
    }

    /**
     * Get the account info from the API
     *
     * @access  private
     * @param   void
     * @return  array
     * @throws  InvalidResponseException
     */
    private function getAccount(): array
    {
        // Determine if the account info already exists and we've fetched it
        // more recently than the default refresh time
        if (is_array($this->account)) {
            try {
                $now = new \DateTime('now');
                $now->sub(new \DateInterval(sprintf('PT%dM', $this->refreshTime)));
            } catch (\Exception $e) {
                throw new InvalidResponseException($e->getMessage());
            }

            if ($now < $this->lastUpdated) {
                return $this->account;
            }
        }

        try {
            $results = $this->get($this->path);
        } catch (GuzzleException $e) {
            throw new InvalidResponseException($e->getMessage());
        }

        if ( ! count($results)) {
            throw new InvalidResponseException(
                'Unable to fetch Account from API'
            );
        }

        $this->account = $results;
        $this->lastUpdated = new \DateTimeImmutable('now');

        return $this->account;
    }
}