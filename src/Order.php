<?php

namespace Elbucho\AlpacaV2;

final class Order extends DataObject
{
    /**
     * Definitions
     */
    const TIF_DAY = 'day';
    const TIF_GOOD_TIL_CANCEL = 'gtc';
    const TIF_OPENING = 'opg';
    const TIF_CLOSING = 'cls';
    const TIF_IMMEDIATE_OR_KILL = 'ioc';
    const TIF_FILL_OR_KILL = 'fok';

    const SIDE_BUY = 'buy';
    const SIDE_SELL = 'sell';

    const STATUS_NEW = 'new';
    const STATUS_PARTIALLY_FILLED = 'partially_filled';
    const STATUS_FILLED = 'filled';
    const STATUS_DONE_FOR_DAY = 'done_for_day';
    const STATUS_CANCELED = 'canceled';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REPLACED = 'replaced';
    const STATUS_PENDING_CANCEL = 'pending_cancel';
    const STATUS_PENDING_REPLACE = 'pending_replace';
    const STATUS_ACCEPTED = 'accepted'; // accepted by Alpaca, but not yet routed to execution
    const STATUS_PENDING_NEW = 'pending_new';
    const STATUS_ACCEPTED_FOR_BIDDING = 'accepted_for_bidding';
    const STATUS_STOPPED = 'stopped';
    const STATUS_REJECTED = 'rejected';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CALCULATED = 'calculated';

    const TYPE_MARKET = 'market';
    const TYPE_LIMIT = 'limit';
    const TYPE_STOP = 'stop';
    const TYPE_STOP_LIMIT = 'stop_limit';

    const CLASS_SIMPLE = 'simple';
    const CLASS_BRACKET = 'bracket';
    const CLASS_ONE_TRIGGERS_OTHER = 'oto';
    const CLASS_ONE_CANCELS_OTHER = 'oco';

    /**
     * Return an array of valid keys for this object
     *
     * @access  protected
     * @param   void
     * @return  array
     */
    protected function getValidKeys(): array
    {
        return [
            'Id'                => DataObject::VAR_UUID,
            'ClientOrderId'     => DataObject::VAR_UUID,
            'AssetId'           => DataObject::VAR_UUID,
            'Symbol'            => DataObject::VAR_STRING,
            'Status'            => DataObject::VAR_ORDER_STATUS,
            'Quantity'          => DataObject::VAR_INT,
            'FilledQuantity'    => DataObject::VAR_INT,
            'TimeInForce'       => DataObject::VAR_ORDER_TIF,
            'Side'              => DataObject::VAR_ORDER_SIDE,
            'Type'              => DataObject::VAR_ORDER_TYPE,
            'LimitPrice'        => DataObject::VAR_FLOAT,
            'StopPrice'         => DataObject::VAR_FLOAT,
            'FilledAvgPrice'    => DataObject::VAR_FLOAT,
            'Class'             => DataObject::VAR_ORDER_CLASS,
            'TakeProfit'        => DataObject::VAR_ORDER_TAKE_PROFIT,
            'StopLoss'          => DataObject::VAR_ORDER_STOP_LOSS,
            'CreatedAt'         => DataObject::VAR_DATE,
            'UpdatedAt'         => DataObject::VAR_DATE,
            'SubmittedAt'       => DataObject::VAR_DATE,
            'FilledAt'          => DataObject::VAR_DATE,
            'ExpiredAt'         => DataObject::VAR_DATE,
            'CanceledAt'        => DataObject::VAR_DATE,
            'FailedAt'          => DataObject::VAR_DATE,
            'ReplacedAt'        => DataObject::VAR_DATE,
            'ReplacedBy'        => DataObject::VAR_UUID,
            'Replaces'          => DataObject::VAR_UUID,
            'ExtendedHours'     => DataObject::VAR_BOOL
        ];
    }

    /**
     * Class constructor
     *
     * @access  public
     * @param   array   $order
     * @return  Order
     */
    public function __construct(array $order = [])
    {
        if (empty($order)) {
            $this->generateOrderId();
        } else {
            $this->loadOrderFromArray($order);
        }

        return $this;
    }

    /**
     * Update this order with an array of new information
     *
     * @access  public
     * @param   array   $data
     * @return  Order
     */
    public function update(array $data = []): Order
    {
        $this->loadOrderFromArray($data, false);

        return $this;
    }

    /**
     * Generate a new UUID for this order's unique ID
     *
     * @access  private
     * @param   void
     * @return  void
     */
    private function generateOrderId()
    {
        $md5 = md5(rand());
        $uuid = '';

        $uuid .= substr($md5, 0, 8) . '-';
        $uuid .= substr($md5, 8, 4) . '-';
        $uuid .= substr($md5, 12, 4) . '-';
        $uuid .= substr($md5, 16, 4) . '-';
        $uuid .= substr($md5, 20);

        $this->{'ClientOrderId'} = $uuid;
    }

    /**
     * Load in an order from an array
     *
     * @access  private
     * @param   array   $order
     * @param   bool    $clearAfter
     * @return  void
     */
    private function loadOrderFromArray(array $order = [], bool $clearAfter = true)
    {
        $keys = [
            'id'                => 'Id',
            'client_order_id'   => 'ClientOrderId',
            'created_at'        => 'CreatedAt',
            'updated_at'        => 'UpdatedAt',
            'submitted_at'      => 'SubmittedAt',
            'filled_at'         => 'FilledAt',
            'expired_at'        => 'ExpiredAt',
            'canceled_at'       => 'CanceledAt',
            'failed_at'         => 'FailedAt',
            'replaced_at'       => 'ReplacedAt',
            'replaced_by'       => 'ReplacedBy',
            'replaces'          => 'Replaces',
            'asset_id'          => 'AssetId',
            'symbol'            => 'Symbol',
            'qty'               => 'Quantity',
            'filled_qty'        => 'FilledQuantity',
            'type'              => 'Type',
            'side'              => 'Side',
            'time_in_force'     => 'TimeInForce',
            'limit_price'       => 'LimitPrice',
            'stop_price'        => 'StopPrice',
            'filled_avg_price'  => 'FilledAvgPrice',
            'status'            => 'Status',
            'extended_hours'    => 'ExtendedHours'
        ];

        foreach ($order as $key => $value) {
            if (array_key_exists($key, $keys)) {
                $this->{$keys[$key]} = $value;
            }
        }

        if ($clearAfter) {
            $this->clearChanges();
        }
    }
}