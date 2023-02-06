<?php

namespace Watish\WatishWEB\Service;

use Watish\Components\Attribute\Async;
use Watish\Components\Attribute\Inject;
use Watish\Components\Includes\Database;

class UserService
{
    #[Inject(BaseService::class)]
    private BaseService $baseService;

    public function check_user_exists(int $user_id) :bool
    {
        return Database::instance()->table("users")
            ->where("user_id",$user_id)
            ->exists();
    }

    public function check_exists_by_name(string $name): bool
    {
        return Database::instance()->table("users")
            ->where("user_name",$name)
            ->exists();
    }

    public function get_user_info_by_name(string $name) :array|null
    {
        $res = Database::instance()->table("users")
            ->where("user_name",$name)
            ->first();
        if(!$res)
        {
            return null;
        }
        return $this->baseService->toArray($res);
    }

    public function get_user_info_by_id(int $user_id) :array|null
    {
        $res = Database::instance()->table("users")
            ->where("user_id",$user_id)
            ->first();
        if(!$res)
        {
            return null;
        }
        return $this->baseService->toArray($res);
    }

    public function check_token_valid(string $token) :bool
    {
        return Database::instance()->table("users")
            ->where("token",$token)
            ->exists();
    }

    public function get_user_info_by_token(string $token) :array|null
    {
        $res = Database::instance()->table("users")
            ->where("token",$token)
            ->first();
        if(!$res)
        {
            return null;
        }
        return $this->baseService->toArray($res);
    }

    #[Async]
    public function change_user_name(int $user_id,string $name) :void
    {
        Database::instance()->table("users")
            ->where("user_id",$user_id)
            ->update([
                "user_name" => $name
            ]);
    }
}
