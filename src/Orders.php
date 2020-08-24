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
     * @return  OrderCollection
     * @throws  InvalidResponseException
     * @throws  InvalidParameterException
     */
    public function getOrders(
        \DateTimeImmutable $from = null,
        \DateTimeImmutable $to = null,
        string $status = self::STATUS_ALL,
        int $limit = 50
    ): OrderCollection {
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

        $return = new OrderCollection();

        foreach ($results as $order) {
            $return->add(new Order($order));
        }

        return $return;
    }

    /**
     * Get an order from a provided order ID
     *
     * @access  public
     * @param   string  $id
     * @return  Order|null
     * @throws  InvalidResponseException
     */
    public function getOrder(string $id): ?Order
    {
        try {
            $results = $this->get(
                sprintf('%s/%s', $this->path, $id),
                ['nested' => true]
            );
        } catch (GuzzleException $e) {
            throw new InvalidResponseException($e->getMessage());
        }

        if (empty($results)) {
            return null;
        }

        return new Order($results);
    }

    /**
     * Get an order from a provided Client Order ID
     *
     * @access  public
     * @param   string  $clientOrderId
     * @return  Order|null
     * @throws  InvalidResponseException
     */
    public function getOrderByClientId(string $clientOrderId): ?Order
    {
        try {
            $results = $this->get(
                sprintf('%s:by_client_order_id', $this->path),
                ['client_order_id' => $clientOrderId]
            );
        } catch (GuzzleException $e) {
            throw new InvalidResponseException($e->getMessage());
        }

        if (empty($results)) {
            return null;
        }

        return new Order($results);
    }

    /**
     * Place a new order
     *
     * @access  public
     * @param   Order   $order
     * @return  bool
     */
    public function placeOrder(Order $order): bool
    {
        try {
            $results = $this->post($this->path, $this->prepareOrderForPost($order));
        } catch (GuzzleException $e) {
            return false;
        }

        if ( ! empty($results)) {
            $order->update($results);
        }

        return true;
    }

    /**
     * Update an order, return a new updated Order object
     *
     * @access  public
     * @param   Order   $order
     * @return  Order|null
     */
    public function updateOrder(Order $order): ?Order
    {
        $key = $order->{'Id'};

        if (empty($key)) {
            return null;
        }

        try {
            $results = $this->patch(
                sprintf('%s/%s', $this->path, $key),
                $this->prepareOrderForPatch($order)
            );
        } catch (GuzzleException $e) {
            return null;
        }

        if (empty($results)) {
            return null;
        }

        return new Order($results);
    }

    /**
     * Cancel Order
     *
     * @access  public
     * @param   Order   $order
     * @return  bool
     */
    public function cancelOrder(Order $order): bool
    {
        $key = $order->{'Id'};

        if (empty($key)) {
            return false;
        }

        try {
            $this->delete(
                sprintf('%s/%s', $this->path, $key),
                $status
            );
        } catch (GuzzleException $e) {
            return false;
        }

        return $status;
    }

    /**
     * Prepare an order object for a POST request
     *
     * @access  private
     * @param   Order   $order
     * @return  array
     */
    private function prepareOrderForPost(Order $order): array
    {
        $return = [
            'symbol'            => $order->{'Symbol'},
            'qty'               => (string) $order->{'Quantity'},
            'side'              => $order->{'Side'},
            'type'              => $order->{'Type'},
            'time_in_force'     => $order->{'TimeInForce'},
            'limit_price'       => (string) $order->{'LimitPrice'},
            'stop_price'        => (string) $order->{'StopPrice'},
            'extended_hours'    => ($order->{'ExtendedHours'} ? 'true' : 'false'),
            'client_order_id'   => $order->{'ClientOrderId'},
            'order_class'       => $order->{'Class'},
            'take_profit'       => $order->{'TakeProfit'},
            'stop_loss'         => $order->{'StopLoss'}
        ];

        return array_filter($return, function ($value) {
            return ! is_null($value);
        });
    }

    /**
     * Prepare an order object for a POST request
     *
     * @access  private
     * @param   Order   $order
     * @return  array
     */
    private function prepareOrderForPatch(Order $order): array
    {
        $return = [
            'qty'               => (string) $order->{'Quantity'},
            'time_in_force'     => $order->{'TimeInForce'},
            'limit_price'       => (string) $order->{'LimitPrice'},
            'stop_price'        => (string) $order->{'StopPrice'},
            'client_order_id'   => $order->{'ClientOrderId'}
        ];

        return array_filter($return, function ($value) {
            return ! is_null($value);
        });
    }

    /**
     * Format a provided array of order objects
     *
     * @deprecated
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
     * @deprecated
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