<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class KitchenOrderUpdated
{
    use Dispatchable;

    public int $companyId;
    public string $type;

    public function __construct(int $companyId, string $type = 'kitchen')
    {
        $this->companyId = $companyId;
        $this->type = $type;
    }
}
