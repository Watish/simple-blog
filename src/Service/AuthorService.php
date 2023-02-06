<?php

namespace Watish\WatishWEB\Service;

use Watish\Components\Attribute\Inject;
use Watish\Components\Includes\Database;

class AuthorService
{
    #[Inject(BaseService::class)]
    private BaseService $baseService;

    public function check_author_exists(int $author_id) :bool
    {
        return Database::instance()->table("users")
            ->where("user_id",$author_id)
            ->exists();
    }

    public function get_author_info(int $author_id) :array|null
    {
        $res = Database::instance()->table("users")
            ->select([
                "user_id","user_name","user_avatar","register_time","last_login"
            ])
            ->first();
        if(!$res)
        {
            return null;
        }
        $res = $this->baseService->toArray($res);
        $res["articles_count"] = $this->get_author_public_articles_count($author_id);
        return $res;
    }

    public function get_author_public_articles_count(int $author_id) :int
    {
        return Database::instance()->table("articles")
            ->where("author_id",$author_id)
            ->where("show_switch",1)
            ->count();
    }
}
