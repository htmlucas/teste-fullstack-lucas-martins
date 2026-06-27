<?php

namespace App\Repository;

use App\Models\Affiliate;

class AffiliateRepository
{
    public function __construct(private Affiliate $entity){}

    public function upsert($user):void {
        $this->entity->updateOrCreate(
            ['external_id' => $user['id']],
            [
                'username' => $user['username'],
                'email' => $user['email'],
                'password' => $user['password'],
            ],
        );
    }
}