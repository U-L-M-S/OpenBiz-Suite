<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'session_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function addItem(Product $product, int $quantity = 1, ?array $options = null): CartItem
    {
        $existingItem = $this->items()->where('product_id', $product->id)->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
            return $existingItem->fresh();
        }

        return $this->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'options' => $options,
        ]);
    }

    public function updateItemQuantity(Product $product, int $quantity): void
    {
        $this->items()->where('product_id', $product->id)->update(['quantity' => $quantity]);
    }

    public function removeItem(Product $product): void
    {
        $this->items()->where('product_id', $product->id)->delete();
    }

    public function clear(): void
    {
        $this->items()->delete();
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn ($item) => $item->product->current_price * $item->quantity);
    }

    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function toOrder(User $user, array $billingAddress, ?array $shippingAddress = null): Order
    {
        $order = Order::create([
            'tenant_id' => $this->tenant_id,
            'user_id' => $user->id,
            'status' => 'pending',
            'subtotal' => $this->subtotal,
            'total' => $this->subtotal,
            'billing_address' => $billingAddress,
            'shipping_address' => $shippingAddress ?? $billingAddress,
        ]);

        foreach ($this->items as $cartItem) {
            $product = $cartItem->product;
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $cartItem->quantity,
                'unit_price' => $product->current_price,
                'total' => $product->current_price * $cartItem->quantity,
                'options' => $cartItem->options,
            ]);
            $product->decrementStock($cartItem->quantity);
        }

        $this->clear();

        return $order;
    }
}
