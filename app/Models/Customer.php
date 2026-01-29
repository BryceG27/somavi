<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Customer extends User
{
    protected static function booted(): void
    {
        static::addGlobalScope('customer_group', function (Builder $builder): void {
            $builder->whereHas('userGroup', function (Builder $query): void {
                $query->where('slug', UserGroup::CUSTOMER_SLUG);
            });
        });

        static::creating(function (Customer $customer): void {
            if ($customer->user_group_id) {
                return;
            }

            $groupId = UserGroup::query()
                ->where('slug', UserGroup::CUSTOMER_SLUG)
                ->value('id');

            if ($groupId) {
                $customer->user_group_id = $groupId;
            }
        });
    }
}
