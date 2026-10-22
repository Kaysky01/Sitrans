<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController
{
    public function __invoke(Request $request): JsonResponse
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with([
                'category',
                'variants' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'modifierGroups.modifiers' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $products->map(fn (Product $product): array => [
                'id' => $product->getKey(),
                'name' => $product->name,
                'description' => $product->description,
                'price' => (float) $product->price,
                'category' => $product->category?->name,
                'is_available' => $product->isOrderable(),
                'variants' => $product->variants->map(fn ($variant): array => [
                    'id' => $variant->getKey(),
                    'name' => $variant->name,
                    'price' => (float) $variant->price,
                ])->values(),
                'modifier_groups' => $product->modifierGroups->map(fn ($group): array => [
                    'id' => $group->getKey(),
                    'name' => $group->name,
                    'is_required' => (bool) $group->is_required,
                    'min_select' => (int) $group->min_select,
                    'max_select' => (int) $group->max_select,
                    'modifiers' => $group->modifiers->map(fn ($modifier): array => [
                        'id' => $modifier->getKey(),
                        'name' => $modifier->name,
                        'price' => (float) $modifier->price,
                    ])->values(),
                ])->values(),
            ])->values(),
        ]);
    }
}
