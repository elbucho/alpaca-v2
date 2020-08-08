<?php

namespace Elbucho\AlpacaV2;
use Elbucho\AlpacaV2\API\Endpoint;
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
            'timestamp'  => new \DateTimeImmutable($this->format($results['timestamp'])),
            'is_open'    => $results['is_open'],
            'next_open'  => new \DateTimeImmutable($this->format($results['next_open'])),
            'next_close' => new \DateTimeImmutable($this->format($results['next_close']))
        ];
    }

    /**
     * Alter the format of the provided timestamp to one acceptable to \DateTimeImmutable
     *
     * @access  private
     * @param   string  $timestamp
     * @return  string
     * @throws  InvalidResponseException
     */
    private function format(string $timestamp): string
    {
        $pattern = "/^(?<year>\d{4})\-(?<month>\d{2})\-(?<day>\d{2})T(?<hour>\d{2})\:" .
            "(?<minute>\d{2})\:(?<second>\d{2})(\.\d*)?(?<offset>\-\d{2}\:\d{2})?/";
        preg_match($pattern, $timestamp, $match);

        foreach (['year','month','day','hour','minute','second'] as $required) {
            if ( ! array_key_exists($required, $match)) {
                throw new InvalidResponseException(sprintf(
                    'Timestamp does not conform to required format: %s',
                    $timestamp
                ));
            }
        }

        $offset = (isset($match['offset']) ? $match['offset'] : '-00:00');

        return sprintf(
            '%s-%s-%sT%s:%s:%s%s',
            $match['year'],
            $match['month'],
            $match['day'],
            $match['hour'],
            $match['minute'],
            $match['second'],
            $offset
        );
    }
}