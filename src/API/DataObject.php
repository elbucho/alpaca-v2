<?php

namespace Elbucho\AlpacaV2\API;

abstract class DataObject
{
    /**
     * Class definitions
     */
    const VAR_INT = 'int';
    const VAR_FLOAT = 'float';
    const VAR_STRING = 'string';
    const VAR_UUID = 'uuid';
    const VAR_DATE = 'date';
    const VAR_BOOL = 'bool';
    const VAR_ORDER_SIDE = 'order_side';
    const VAR_ORDER_TYPE = 'order_type';
    const VAR_ORDER_TIF = 'order_tif';
    const VAR_ORDER_CLASS = 'order_class';
    const VAR_ORDER_STATUS = 'order_status';
    const VAR_ORDER_TAKE_PROFIT = 'order_take_profit';
    const VAR_ORDER_STOP_LOSS = 'order_stop_loss';

    /**
     * Order data
     *
     * @access  private
     * @var     array
     */
    private $data = [];

    /**
     * Changes to order since saving
     *
     * @access  private
     * @var     array
     */
    private $changes = [];

    /**
     * Magic get method
     *
     * @access  public
     * @param   string  $key
     * @return  mixed
     */
    public function __get(string $key)
    {
        return (array_key_exists($key, $this->data) ? $this->data[$key] : null);
    }

    /**
     * Magic set method
     *
     * @access  public
     * @param   string  $key
     * @param   mixed   $value
     * @return  void
     */
    public function __set(string $key, $value)
    {
        if ( ! $this->isValid($key, $value)) {
            return;
        }

        if ( ! isset($this->data[$key]) or $this->data[$key] !== $value) {
            $this->changes[$key] = (isset($this->data[$key]) ? $this->data[$key] : null);
        }

        $this->data[$key] = $value;
    }

    /**
     * Return this order as an array
     *
     * @access  public
     * @param   void
     * @return  array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Return an array of changes to this object since last saved
     *
     * @access  public
     * @param   void
     * @return  array
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * Clear the changes on this object
     *
     * @access  public
     * @param   void
     * @return  void
     */
    public function clearChanges()
    {
        $this->changes = [];
    }

    /**
     * Determine if the given value is valid for the provided key
     *
     * @access  protected
     * @param   string  $key
     * @param   mixed   $value
     * @return  bool
     */
    protected function isValid(string $key, &$value): bool
    {
        $validKeys = $this->getValidKeys();

        if ( ! array_key_exists($key, $validKeys)) {
            return false;
        }

        switch ($validKeys[$key]) {
            case self::VAR_INT:
                if (is_numeric($value)) {
                    $value = (int) $value;
                    return true;
                }
                break;
            case self::VAR_FLOAT:
                if (is_numeric($value)) {
                    $value = (float) $value;
                    return true;
                }
                break;
            case self::VAR_STRING:
                return is_string($value);
            case self::VAR_BOOL:
                if (is_bool($value)) {
                    return true;
                }

                if ($value == 0 or (strtolower($value) == 'false')) {
                    $value = false;
                    return true;
                }

                if ($value == 1 or (strtolower($value) == 'true')) {
                    $value = true;
                    return true;
                }
                break;
            case self::VAR_UUID:
                if (is_string($value)) {
                    $pattern = "/^[a-f0-9]{8}\-([a-f0-9]{4}\-){3}[a-f0-9]{12}$/";
                    return preg_match($pattern, $value) == 1;
                }
                break;
            case self::VAR_DATE:
                if (is_string($value)) {
                    try {
                        $value = new \DateTimeImmutable($value);
                    } catch (\Exception $e) {
                        return false;
                    }
                }

                return $value instanceof \DateTimeInterface;
            case self::VAR_ORDER_CLASS:
                return in_array($value, [
                    Order::CLASS_BRACKET,
                    Order::CLASS_SIMPLE,
                    Order::CLASS_ONE_CANCELS_OTHER,
                    Order::CLASS_ONE_TRIGGERS_OTHER
                ]);
            case self::VAR_ORDER_SIDE:
                return in_array($value, [
                    Order::SIDE_BUY,
                    Order::SIDE_SELL
                ]);
            case self::VAR_ORDER_TYPE:
                return in_array($value, [
                    Order::TYPE_LIMIT,
                    Order::TYPE_MARKET,
                    Order::TYPE_STOP,
                    Order::TYPE_STOP_LIMIT
                ]);
            case self::VAR_ORDER_TIF:
                return in_array($value, [
                    Order::TIF_DAY,
                    Order::TIF_GOOD_TIL_CANCEL,
                    Order::TIF_OPENING,
                    Order::TIF_CLOSING,
                    Order::TIF_IMMEDIATE_OR_KILL,
                    Order::TIF_FILL_OR_KILL
                ]);
            case self::VAR_ORDER_STATUS:
                return in_array($value, [
                    Order::STATUS_NEW,
                    Order::STATUS_PARTIALLY_FILLED,
                    Order::STATUS_FILLED,
                    Order::STATUS_DONE_FOR_DAY,
                    Order::STATUS_CANCELED,
                    Order::STATUS_EXPIRED,
                    Order::STATUS_REPLACED,
                    Order::STATUS_PENDING_CANCEL,
                    Order::STATUS_PENDING_REPLACE,
                    Order::STATUS_ACCEPTED,
                    Order::STATUS_PENDING_NEW,
                    Order::STATUS_ACCEPTED_FOR_BIDDING,
                    Order::STATUS_STOPPED,
                    Order::STATUS_REJECTED,
                    Order::STATUS_SUSPENDED,
                    Order::STATUS_CALCULATED,
                ]);
            case self::VAR_ORDER_STOP_LOSS:
                if (is_array($value)) {
                    foreach (['stop_price','limit_price'] as $k) {
                        if ( ! array_key_exists($k, $value) or ! is_numeric($value[$k])) {
                            return false;
                        }
                    }

                    return true;
                }
                break;
            case self::VAR_ORDER_TAKE_PROFIT:
                if (is_array($value)) {
                    foreach (['limit_price'] as $k) {
                        if ( ! array_key_exists($k, $value) or ! is_numeric($value[$k])) {
                            return false;
                        }
                    }

                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Return an array of valid keys for this object
     *
     * @abstract
     * @access  protected
     * @param   void
     * @return  array
     */
    abstract protected function getValidKeys(): array;
}