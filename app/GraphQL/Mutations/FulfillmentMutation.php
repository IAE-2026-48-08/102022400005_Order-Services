<?php

namespace App\GraphQL\Mutations;

use App\Services\DatabaseService;
use GraphQL\Error\Error;
use Exception;

class FulfillmentMutation
{
    public function processOrder($root, array $args)
    {
        try {
            return DatabaseService::processOrderToTransaction($args['orderId']);
        } catch (Exception $e) {
            throw new Error($e->getMessage());
        }
    }
}
