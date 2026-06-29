<?php

namespace App\Repository;

use App\Models\Product;

class ProductRepository
{
    public function __construct(private Product $entity){}

    public function upsert(array $product):void {
        $this->entity->updateOrCreate(
            ['external_id' => $product['id']],
            [
                'title' => $product['title'],
                'price' => $product['price'],
                'description' => $product['description'],
                'category' => $product['category'],
                'image' => $product['image'],
            ]
        );
    }
}