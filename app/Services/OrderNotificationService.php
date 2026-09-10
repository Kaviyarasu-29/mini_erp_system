<?php

namespace App\Services;

use App\Jobs\SendOrderNotificationJob;
use App\Models\Sale;

class OrderNotificationService
{
    public static function notifyOrderCreated(Sale $sale): void
    {
        self::dispatchNotification($sale, 'created');
    }

    public static function notifyOrderUpdated(Sale $sale): void
    {
        self::dispatchNotification($sale, 'updated');
    }

    public static function notifyOrderCompleted(Sale $sale): void
    {
        self::dispatchNotification($sale, 'completed');
    }

    public static function notifyOrderCancelled(Sale $sale): void
    {
        self::dispatchNotification($sale, 'cancelled');
    }

    public static function formatMessage(Sale $sale, string $event): string
    {
        $sale->loadMissing('customer');
        $customerName = $sale->customer->name ?? 'Customer';
        $customerEmail = $sale->customer->email ?? 'N/A';
        $invoiceNumber = $sale->invoice_number ?? $sale->sale_number ?? 'N/A';
        $totalAmount = number_format((float) $sale->total_amount, 2);

        return match ($event) {
            'created' => "[ORDER CREATED] Invoice #{$invoiceNumber} created for {$customerName} <{$customerEmail}> with Total Amount ₹{$totalAmount}.",
            'updated' => "[ORDER UPDATED] Invoice #{$invoiceNumber} updated for {$customerName} <{$customerEmail}> with Total Amount ₹{$totalAmount}. Status: {$sale->status}.",
            'completed' => "[ORDER COMPLETED] Invoice #{$invoiceNumber} for {$customerName} <{$customerEmail}> marked as Completed. Total: ₹{$totalAmount}.",
            'cancelled' => "[ORDER CANCELLED] Invoice #{$invoiceNumber} for {$customerName} <{$customerEmail}> has been Cancelled. Stock restored.",
            default => "[ORDER NOTIFICATION] Event '{$event}' triggered for Invoice #{$invoiceNumber}."
        };
    }

    private static function dispatchNotification(Sale $sale, string $event): void
    {
        $formattedMessage = self::formatMessage($sale, $event);
        SendOrderNotificationJob::dispatch($sale, $event, $formattedMessage);
    }
}
