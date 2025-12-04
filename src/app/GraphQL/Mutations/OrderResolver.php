<?php

namespace App\GraphQL\Mutations;

use App\Models\Order;

class OrderResolver
{
    public function updateStatus($root, array $args): Order
    {
        $order = Order::findOrFail($args['id']);
        $order->update(['status' => $args['status']]);
        return $order->fresh();
    }
}
