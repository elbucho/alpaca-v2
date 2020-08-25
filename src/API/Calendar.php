<?php

namespace Elbucho\AlpacaV2\API;
use GuzzleHttp\Exception\GuzzleException;

final class Calendar extends Endpoint
{
    /**
     * Endpoint path
     *
     * @access  private
     * @var     string
     */
    private $path = '/calendar';

    /**
     * Return an array of dates with open and close time for the market
     * on each given day in 24-hour format.  If no start date is provided,
     * this function will by default provide a start date of 7 days ago.
     * If no end date is provided, it will default with today's date.
     *
     * [
     *   [
     *     'date'  => 'YYYY-MM-DD',
     *     'open'  => 'HH:MM',
     *     'close' => 'HH:MM'
     *   ],
     *   ...
     * ]
     *
     * @access  public
     * @param   \DateTimeImmutable|null $startDate
     * @param   \DateTimeImmutable|null $endDate
     * @return  array
     * @throws  GuzzleException
     */
    public function getMarketDays(
        \DateTimeImmutable $startDate = null,
        \DateTimeImmutable $endDate = null
    ): array
    {
        if (is_null($startDate)) {
            $startDate = new \DateTime('7 days ago');
            $startDate->setTime(0, 0, 0);
        }

        if (is_null($endDate)) {
            $endDate = new \DateTime('now');
            $endDate->setTime(23, 59, 59);
        }

        $results = $this->get($this->path, [
            'start' => $startDate->format('c'),
            'end'   => $endDate->format('c')
        ]);

        array_walk($results, function (&$result) {
            unset($result['session_open']);
            unset($result['session_close']);
        });

        return $results;
    }
}