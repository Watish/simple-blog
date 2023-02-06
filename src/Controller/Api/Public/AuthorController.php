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
use Watish\WatishWEB\Middleware\UserExistsMiddleware;
use Watish\WatishWEB\Service\ArticleService;
use Watish\WatishWEB\Service\AuthorService;
use Watish\WatishWEB\Service\BaseService;
use Watish\WatishWEB\Service\SortService;

#[Prefix('/api/public/author')]
class AuthorController {

    #[Inject(AuthorService::class)]
    private AuthorService $authorService;

    #[Inject(BaseService::class)]
    private BaseService $baseService;

    #[Inject(SortService::class)]
    private SortService $sortService;

    #[Path('/info')]
    #[Middleware([UserExistsMiddleware::class])]
    public function get_info(Request $request) :array
    {
        $params = $request->all();
        $user_id = $params["user_id"];
        $info = $this->authorService->get_author_info($user_id);
        return [
            "Ok" => true,
            "Data" => $info
        ];
    }

    #[Path('/sorts/all')]
    #[Middleware([UserExistsMiddleware::class])]
    public function all_sorts(Request $request) :array
    {
        $user_id = $request->all()["user_id"];
        $builder = Database::instance()->table("articles")
            ->selectRaw("count(*) as article_num")
            ->addSelect("sort_id")
            ->where("author_id",$user_id)
            ->where("show_switch",1)
            ->groupBy("sort_id")
            ->orderByDesc("article_num");
        $resList = $this->baseService->toArray($builder->get());
        foreach ($resList as &$item)
        {
            $sort_id = $item["sort_id"];
            if($sort_id <= 0)
            {
                $item["sort_name"] = "未分类";
            }else{
                $item["sort_name"] = $this->sortService->get_sort_name($sort_id);
            }
        }
        return [
            "Ok" => true,
            "Data" => $resList
        ];
    }
}
