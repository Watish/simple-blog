<?php

namespace Watish\WatishWEB\Controller\Api\Author;

use Watish\Components\Attribute\Inject;
use Watish\Components\Attribute\Middleware;
use Watish\Components\Attribute\Path;
use Watish\Components\Attribute\Prefix;
use Watish\Components\Includes\Database;
use Watish\Components\Struct\Request;
use Watish\WatishWEB\Middleware\TokenValidMiddleware;
use Watish\WatishWEB\Service\ArticleService;
use Watish\WatishWEB\Service\BaseService;
use Watish\WatishWEB\Service\UserService;

#[Prefix('/api/author/article')]
#[Middleware([TokenValidMiddleware::class])]
class ArticleController
{
    #[Inject(UserService::class)]
    private UserService $userService;

    #[Inject(BaseService::class)]
    private BaseService $baseService;

    #[Inject(ArticleService::class)]
    private ArticleService $articleService;

    #[Path("/list")]
    public function list_articles(Request $request) :array
    {
        $params = $request->all();
        $token = $params["token"];
        $page = $params["page"] ?? 1;
        $limit = $params["limit"] ?? 10;
        $user_info = $this->userService->get_user_info_by_token($token);
        $user_id = $user_info["user_id"];
        $builder = Database::instance()->table("articles")
            ->where("author_id",$user_id);
        $res = $this->baseService->paginatedArray($builder,$page,$limit);
        foreach ($res["data"] as &$item)
        {
            $this->articleService->formatItem($item);
        }
        return [
            "Ok" => true,
            "Data" => $res
        ];
    }
}
