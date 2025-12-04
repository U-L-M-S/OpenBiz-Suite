<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_order_can_be_created(): void
    {
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'subtotal' => 100.00,
            'total' => 100.00,
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_order_has_auto_generated_order_number(): void
    {
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'subtotal' => 50.00,
            'total' => 50.00,
        ]);

        $this->assertNotNull($order->order_number);
        $this->assertStringStartsWith('ORD', $order->order_number);
    }

    public function test_order_can_be_marked_as_paid(): void
    {
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'subtotal' => 75.00,
            'total' => 75.00,
        ]);

        $order->markAsPaid('TXN123456');

        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('TXN123456', $order->transaction_id);
        $this->assertNotNull($order->paid_at);
    }

    public function test_order_can_be_shipped(): void
    {
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'status' => 'processing',
            'subtotal' => 100.00,
            'total' => 100.00,
        ]);

        $order->ship();

        $this->assertEquals('shipped', $order->status);
        $this->assertNotNull($order->shipped_at);
    }

    public function test_order_can_be_delivered(): void
    {
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'status' => 'shipped',
            'subtotal' => 100.00,
            'total' => 100.00,
        ]);

        $order->deliver();

        $this->assertEquals('delivered', $order->status);
        $this->assertNotNull($order->delivered_at);
    }
}
