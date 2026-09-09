<?php

namespace Tests\Unit;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\CodeGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodeGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_purchase_item_barcode(): void
    {
        $code1 = CodeGeneratorService::generateBarcode(PurchaseItem::class, 'barcode', 'BC', 3);
        $this->assertEquals('BC001', $code1);
    }

    public function test_can_generate_month_based_code(): void
    {
        $code = CodeGeneratorService::generateMonthBasedCode(
            Purchase::class,
            'purchase_number',
            '2026-09-10',
            'purchased_at'
        );

        $this->assertEquals('SEP001', $code);
    }
}
