<?php

namespace Watish\WatishWEB\Controller\Api\Public;

use Watish\Components\Attribute\Inject;
use Watish\Components\Attribute\Middleware;
use Watish\Components\Attribute\Path;
use Watish\Components\Attribute\Prefix;
use Watish\Components\Includes\Database;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;
use Watish\WatishWEB\Middleware\ArticleExistsMiddleware;
use Watish\WatishWEB\Middleware\PageLimitMiddleware;
use Watish\WatishWEB\Middleware\UserExistsMiddleware;
use Watish\WatishWEB\Service\ArticleService;
use Watish\WatishWEB\Service\BaseService;

#[Prefix('/api/public/article')]
class ArticleController
{
    #[Inject(BaseService::class)]
    private BaseService $baseService;

    #[Inject(ArticleService::class)]
    private ArticleService $articleService;

    #[Path('/list')]
    #[Middleware([PageLimitMiddleware::class])]
    public function list_posts(Request $request,Response $response): array
    {
        $params = $request->all();
        $page = $params["page"] ?? 1;
        $limit = $params["limit"] ?? 10;

        $builder = Database::instance()->table("articles")
            ->where("show_switch",1);

        //分类
        if(isset($params["sort_id"]))
        {
            $sort_id = (int)$params["sort_id"];
            if($sort_id > 0)
            {
                $builder = $builder->where("sort_id",$sort_id);
            }
        }

        //作者
        if(isset($params["author_id"]))
        {
            $author_id = (int)$params["author_id"];
            if($author_id > 0)
            {
                $builder = $builder->where("author_id",$author_id);
            }
        }

        //顺序
        if(isset($params["order"]))
        {
            $order = $params["order"];
            if(in_array($order,["desc","asc"]))
            {
                $builder = $builder->orderBy("create_time",$order);
            }
        }

        $res = $this->baseService->paginatedArray($builder,$page,$limit);
        $tmp = [];
        foreach ($res["data"] as $item)
        {
            $this->articleService->formatItem($item);
            $tmp[] = $item;
        }
        $res["data"] = $tmp;
        return $res;
    }

    #[Path("/info")]
    #[Middleware([ArticleExistsMiddleware::class])]
    public function article_info(Request $request) :array
    {
        $params = $request->all();
        $article_id = $params["article_id"];
        $res = $this->articleService->get_article_info($article_id);
        return [
            "Ok" => true,
            "Data" => $res
        ];
    }

    #[Path('/list/by/sort')]
    public function list_by_sort(Request $request) :array
    {
        $params = $request->all();
        $page = $params["page"] ?? 1;
        $limit = $params["limit"] ?? 10;
        $sort_id = $params["sort_id"];
        $builder = Database::instance()->table("articles")
            ->where("sort_id",$sort_id)
            ->where("show_switch",1)
            ->orderByDesc("create_time");
        return [
            "Ok" => true,
            "Data" => $this->baseService->paginatedArray($builder,$page,$limit)
        ];
    }

    #[Path('/list/by/author')]
    #[Middleware([UserExistsMiddleware::class])]
    public function list_by_author(Request $request) :array
    {
        $params = $request->all();
        $page = $params["page"] ?? 1;
        $limit = $params["limit"] ?? 10;
        $user_id = $params["user_id"];
        $builder = Database::instance()->table("articles")
            ->where("author_id",$user_id)
            ->where("show_switch",1)
            ->orderByDesc("create_time");
        return [
            "Ok" => true,
            "Data" => $this->baseService->paginatedArray($builder,$page,$limit)
        ];
    }

    #[Path('/random')]
    #[Middleware([PageLimitMiddleware::class])]
    public function get_random(Request $request) :array
    {
        $params = $request->all();
        $limit = $params["limit"] ?? 4;
        $resList = Database::instance()->table("articles")
            ->select(["article_id"])
            ->where("show_switch",1)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
        $resList = $this->baseService->toArray($resList);
        foreach ($resList as &$item)
        {
            $article_id = $item["article_id"];
            $item = $this->articleService->get_article_info($article_id);
        }
        return [
            "Ok" => true,
            "Data" => $resList
        ];
    }
}
