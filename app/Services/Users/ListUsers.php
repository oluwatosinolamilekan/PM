<?php


namespace App\Services\Users;


use App\Actions\Action;
use App\Http\Resources\UserResources;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\QueryBuilder\QueryBuilder;

class ListUsers extends Action
{

    public function action(): JsonResource
    {
        $users = QueryBuilder::for(User::class)
                            ->allowedFilters([
                                'name',
                                'email'
                            ])
                            ->get();
        return UserResources::collection($users);
    }
}
