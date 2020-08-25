<?php

namespace Elbucho\AlpacaV2\API;

final class OrderCollection implements \Iterator
{
    /**
     * Orders in collection
     *
     * @access  private
     * @var     Order[]
     */
    private $orders = [];

    /**
     * Current pointer
     *
     * @access  private
     * @var     int
     */
    private $pointer = 0;

    /**
     * Return a count of the orders in this collection
     *
     * @access  public
     * @param   void
     * @return  int
     */
    public function count(): int
    {
        return count($this->orders);
    }

    /**
     * Add an order to the collection
     *
     * @access  public
     * @param   Order   $order
     * @param   bool    $replace
     * @return  bool
     */
    public function add(Order $order, bool $replace = false): bool
    {
        $key = $order->{'ClientOrderId'};

        if (is_null($key)) {
            return false;
        }

        if (array_key_exists($key, $this->orders)) {
            if ( ! $replace) {
                return false;
            }
        }

        $this->orders[$key] = $order;

        return true;
    }

    /**
     * Locate an order in the array
     *
     * @access  public
     * @param   string  $orderId
     * @return  Order|null
     */
    public function find(string $orderId): ?Order
    {
        if (array_key_exists($orderId, $this->orders)) {
            return $this->orders[$orderId];
        }

        return null;
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->orders[$this->key()];
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        ++$this->pointer;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return string|float|int|bool|null scalar on success, or null on failure.
     */
    public function key()
    {
        $keys = array_keys($this->orders);

        return $keys[$this->pointer];
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        if ($this->pointer >= count($this->orders)) {
            return false;
        }

        return isset($this->orders[$this->key()]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->pointer = 0;
    }
}