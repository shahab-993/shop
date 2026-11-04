<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Unit;
use App\Models\UnitTranslation;
use App\Services\PurchaseService;

class PurchaseServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;
    private Product $product;
    private PurchaseService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PurchaseService::class);

        $this->user = User::factory()->create();

        $this->supplier = Supplier::create([
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'name' => 'Piece',
            'abbreviation' => 'pc',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => \App\Models\Category::create([
                'is_active' => true,
            ])->id,
            'unit_id' => $unit->id,
            'sku' => 'TEST-001',
            'purchase_price' => 0,
            'selling_price' => 100,
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

    private function purchaseData(): array
    {
        return [
            'supplier_id' => $this->supplier->id,
            'invoice_number' => 'PUR-TEST-001',
            'supplier_invoice_number' => 'SUP-001',
            'purchase_date' => now()->toDateString(),
            'discount_amount' => 100,
            'notes' => 'Test purchase',

            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'unit_cost' => 100,
                    'discount_amount' => 50,
                ],
            ],
        ];
    }

    public function test_purchase_is_created_as_draft(): void
    {
        $purchase = $this->service->create(
            $this->purchaseData(),
            $this->user->id
        );

        $this->assertEquals('draft', $purchase->status);
        $this->assertEquals(1000, $purchase->subtotal);
        $this->assertEquals(150, $purchase->discount_amount);
        $this->assertEquals(850, $purchase->total_amount);

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseCount('inventory_batches', 0);
    }

    public function test_completing_purchase_creates_inventory_batch(): void
    {
        $purchase = $this->service->create(
            $this->purchaseData(),
            $this->user->id
        );

        $completed = $this->service->complete($purchase->id);

        $this->assertEquals('completed', $completed->status);

        $this->assertDatabaseHas('inventory_batches', [
            'product_id' => $this->product->id,
            'source_type' => 'purchase',
            'source_id' => $purchase->id,
            'original_quantity' => 10,
            'remaining_quantity' => 10,
            'unit_cost' => 85,
        ]);
    }

    public function test_purchase_cannot_be_completed_twice(): void
    {
        $purchase = $this->service->create(
            $this->purchaseData(),
            $this->user->id
        );

        $this->service->complete($purchase->id);

        $this->expectException(
            \Illuminate\Validation\ValidationException::class
        );

        $this->service->complete($purchase->id);
    }

    public function test_invalid_purchase_discount_is_rejected(): void
    {
        $data = $this->purchaseData();

        $data['discount_amount'] = 2000;

        $this->expectException(
            \Illuminate\Validation\ValidationException::class
        );

        $this->service->create($data, $this->user->id);
    }
}
