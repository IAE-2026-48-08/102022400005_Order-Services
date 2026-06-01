<?php

namespace App\GraphQL\Queries;

use App\Services\DatabaseService;
use GraphQL\Error\Error;

class FulfillmentQuery
{
    public function orders($root, array $args)
    {
        return DatabaseService::getOrders();
    }

    public function order($root, array $args)
    {
        $order = DatabaseService::getOrderById($args['id']);
        if (!$order) {
            throw new Error("Order dengan ID {$args['id']} tidak ditemukan.");
        }
        return $order;
    }
}
