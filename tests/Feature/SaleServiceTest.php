<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\UnitTranslation;
use App\Models\InventoryBatch;
use App\Models\Sale;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $unit = Unit::create([
            'name' => 'Piece',
            'abbreviation' => 'pc',
            'is_active' => true,
        ]);

        $category = Category::create([
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'sku' => 'SALE-001',
            'purchase_price' => 10,
            'selling_price' => 20,
            'min_stock' => 0,
            'is_active' => true,
        ]);

        UnitTranslation::create([
            'unit_id' => $unit->id,
            'locale' => 'ps',
            'name' => 'ټوټه',
            'abbreviation' => 'ټوټه',
        ]);
    }

    private function createBatch(
        float $quantity,
        float $unitCost,
        string $receivedAt
    ): InventoryBatch {

        $totalCost = round($quantity * $unitCost, 2);

        return InventoryBatch::create([
            'product_id' => $this->product->id,
            'source_type' => 'opening',
            'source_id' => null,
            'source_item_id' => null,
            'received_at' => $receivedAt,
            'original_quantity' => $quantity,
            'remaining_quantity' => $quantity,
            'unit_cost' => $unitCost,
            'original_cost' => $totalCost,
            'remaining_cost' => $totalCost,
        ]);
    }

    private function saleData(float $quantity = 1): array
    {
        return [
            'invoice_number' => 'SALE-TEST-001',
            'buyer_name' => 'Test Buyer',
            'buyer_phone' => '0700000000',
            'sale_date' => now(),
            'discount_amount' => 0,
            'notes' => 'Test sale',

            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $quantity,
                    'unit_price' => 20,
                    'discount_amount' => 0,
                ],
            ],
        ];
    }

    public function test_sale_is_created_as_draft(): void
    {
        $this->createBatch(10, 10, '2026-01-01 08:00:00');

        $service = app(\App\Services\SaleService::class);

        $sale = $service->create(
            $this->saleData(),
            $this->user->id
        );

        $this->assertEquals('draft', $sale->status);

        $this->assertEquals(20, $sale->total_amount);

        $this->assertDatabaseCount('sale_item_allocations', 0);
    }

    public function test_fifo_consumes_oldest_batch_first(): void
    {
        $firstBatch = $this->createBatch(
            2,
            10,
            '2026-01-01 08:00:00'
        );

        $secondBatch = $this->createBatch(
            3,
            12,
            '2026-01-02 08:00:00'
        );

        $service = app(\App\Services\SaleService::class);

        $sale = $service->create(
            $this->saleData(3),
            $this->user->id
        );

        $completed = $service->complete($sale->id);

        $this->assertEquals('completed', $completed->status);

        $this->assertEquals(
            0,
            (float) $firstBatch->fresh()->remaining_quantity
        );

        $this->assertEquals(
            2,
            (float) $secondBatch->fresh()->remaining_quantity
        );

        $this->assertDatabaseHas('sale_item_allocations', [
            'sale_item_id' => $sale->items()->first()->id,
            'inventory_batch_id' => $firstBatch->id,
            'quantity' => 2,
            'total_cost' => 20,
        ]);

        $this->assertDatabaseHas('sale_item_allocations', [
            'sale_item_id' => $sale->items()->first()->id,
            'inventory_batch_id' => $secondBatch->id,
            'quantity' => 1,
            'total_cost' => 12,
        ]);

        $this->assertEquals(
            32,
            (float) $sale->items()->first()->fresh()->cost_total
        );
    }

    public function test_sale_cannot_exceed_available_stock(): void
    {
        $this->createBatch(2, 10, '2026-01-01 08:00:00');

        $service = app(\App\Services\SaleService::class);

        $sale = $service->create(
            $this->saleData(5),
            $this->user->id
        );

        $this->expectException(
            \Illuminate\Validation\ValidationException::class
        );

        $service->complete($sale->id);
    }

    public function test_sale_cannot_be_completed_twice(): void
    {
        $this->createBatch(10, 10, '2026-01-01 08:00:00');

        $service = app(\App\Services\SaleService::class);

        $sale = $service->create(
            $this->saleData(),
            $this->user->id
        );

        $service->complete($sale->id);

        $this->expectException(
            \Illuminate\Validation\ValidationException::class
        );

        $service->complete($sale->id);
    }
}