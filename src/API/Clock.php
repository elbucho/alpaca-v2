<?php

namespace Elbucho\AlpacaV2\API;
use GuzzleHttp\Exception\GuzzleException;
use Elbucho\AlpacaV2\Exceptions\InvalidResponseException;

final class Clock extends Endpoint
{
    /**
     * Endpoint path
     *
     * @access  private
     * @var     string
     */
    private $path = '/clock';

    /**
     * Get information on the current market day
     *
     * [
     *   'timestamp'  => \DateTimeImmutable,
     *   'is_open'    => bool,
     *   'next_open'  => \DateTimeImmutable,
     *   'next_close' => \DateTimeImmutable
     * ]
     *
     * @access  public
     * @param   void
     * @return  array
     * @throws  GuzzleException
     * @throws  InvalidResponseException
     * @throws  \Exception
     */
    public function getClock(): array
    {
        $results = $this->get($this->path);

        foreach (['timestamp', 'is_open', 'next_open', 'next_close'] as $required) {
            if ( ! array_key_exists($required, $results)) {
                throw new InvalidResponseException('Response missing required parameters');
            }
        }

        return [
            'timestamp'  => Helper::convertToDateTime($results['timestamp']),
            'is_open'    => $results['is_open'],
            'next_open'  => Helper::convertToDateTime($results['next_open']),
            'next_close' => Helper::convertToDateTime($results['next_close'])
        ];
    }
}