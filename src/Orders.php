<?php

namespace Elbucho\AlpacaV2;
use Elbucho\AlpacaV2\API\Endpoint;
use Elbucho\AlpacaV2\Exceptions\InvalidParameterException;
use Elbucho\AlpacaV2\Exceptions\InvalidResponseException;
use GuzzleHttp\Exception\GuzzleException;

final class Orders extends Endpoint
{
    /**
     * Order Status Definitions
     */
    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';
    const STATUS_ALL = 'all';

    /**
     * Endpoint path
     *
     * @access  private
     * @var     string
     */
    private $path = '/orders';

    /**
     * Get a list of orders made by the account
     *
     * @access  public
     * @param   \DateTimeImmutable|null $from
     * @param   \DateTimeImmutable|null $to
     * @param   string                  $status
     * @param   int                     $limit
     * @return  array
     * @throws  InvalidResponseException
     * @throws  InvalidParameterException
     */
    public function getOrders(
        \DateTimeImmutable $from = null,
        \DateTimeImmutable $to = null,
        string $status = self::STATUS_ALL,
        int $limit = 50
    ): array {
        if (is_null($from)) {
            $from = new \DateTimeImmutable('7 days ago');
        }

        if (is_null($to) or ($to <= $from)) {
            $to = new \DateTimeImmutable('now');
        }

        if ( ! in_array($status, [self::STATUS_ALL, self::STATUS_CLOSED, self::STATUS_OPEN])) {
            throw new InvalidParameterException(sprintf(
                'Provided status %s is not a recognized option.  Options are: %s',
                $status,
                implode(', ', [self::STATUS_ALL, self::STATUS_CLOSED, self::STATUS_OPEN])
            ));
        }

        if ($limit <= 0 or $limit > 500) {
            throw new InvalidParameterException(sprintf(
                'Provided limit %d is not in the acceptable range of 0 to 500',
                $limit
            ));
        }

        try {
            $results = $this->get($this->path, [
                'status'    => $status,
                'limit'     => $limit,
                'after'     => $from->format('c'),
                'until'     => $to->format('c'),
                'direction' => 'asc',
                'nested'    => true
            ]);
        } catch (GuzzleException $e) {
            throw new InvalidResponseException($e->getMessage());
        }

        return $this->formatOrders($results);
    }

    /**
     * Get an order from a provided order ID
     *
     * @access  public
     * @param   string  $id
     * @return  array
     * @throws  InvalidResponseException
     */
    public function getOrder(string $id): array
    {
        try {
            $results = $this->get(
                sprintf('%s/%s', $this->path, $id),
                ['nested' => true]
            );
        } catch (GuzzleException $e) {
            throw new InvalidResponseException($e->getMessage());
        }

        return $this->formatOrder($results);
    }


    /**
     * Format a provided array of order objects
     *
     * @access  private
     * @param   array   $data
     * @return  array
     * @throws  InvalidResponseException
     */
    private function formatOrders(array $data = []): array
    {
        $response = [];

        foreach ($data as $order) {
            $order = $this->formatOrder($order);
            $response[$order['id']] = $order;
        }

        return $response;
    }

    /**
     * Format a provided order array
     *
     * @access  private
     * @param   array   $order
     * @return  array
     * @throws  InvalidResponseException
     */
    private function formatOrder(array $order = []): array
    {
        $response = [];

        $keys = [
            'id'                => ['type' => 'string', 'required' => true],
            'client_order_id'   => ['type' => 'string', 'required' => true],
            'created_at'        => ['type' => 'datetime', 'required' => true],
            'updated_at'        => ['type' => 'datetime', 'required' => false],
            'submitted_at'      => ['type' => 'datetime', 'required' => false],
            'filled_at'         => ['type' => 'datetime', 'required' => false],
            'expired_at'        => ['type' => 'datetime', 'required' => false],
            'canceled_at'       => ['type' => 'datetime', 'required' => false],
            'failed_at'         => ['type' => 'datetime', 'required' => false],
            'replaced_at'       => ['type' => 'datetime', 'required' => false],
            'replaces'          => ['type' => 'string', 'required' => false],
            'asset_id'          => ['type' => 'string', 'required' => true],
            'symbol'            => ['type' => 'string', 'required' => true],
            'asset_class'       => ['type' => 'string', 'required' => true],
            'qty'               => ['type' => 'int', 'required' => true],
            'filled_qty'        => ['type' => 'int', 'required' => true],
            'type'              => ['type' => 'string', 'required' => true],
            'side'              => ['type' => 'string', 'required' => true],
            'time_in_force'     => ['type' => 'string', 'required' => true],
            'limit_price'       => ['type' => 'float', 'required' => false],
            'stop_price'        => ['type' => 'float', 'required' => false],
            'filled_avg_price'  => ['type' => 'float', 'required' => false],
            'status'            => ['type' => 'string', 'required' => true],
            'extended_hours'    => ['type' => 'bool', 'required' => true],
            'legs'              => ['type' => 'array', 'required' => false],
        ];

        foreach ($keys as $key => $definition) {
            $response[$key] = $this->getFormattedData($order, $key, $definition);
        }

        return $response;
    }
}