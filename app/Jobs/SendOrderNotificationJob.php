<?php

namespace App\Jobs;

use App\Models\Sale;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOrderNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Sale $sale,
        public string $event,
        public string $formattedMessage
    ) {}

    public function handle(): void
    {
        Log::info($this->formattedMessage);
    }
}
