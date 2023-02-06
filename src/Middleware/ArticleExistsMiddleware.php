<?php

namespace Watish\WatishWEB\Middleware;

use Watish\Components\Attribute\Inject;
use Watish\Components\Constructor\ValidatorConstructor;
use Watish\Components\Includes\Context;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;
use Watish\WatishWEB\Service\ArticleService;

class ArticleExistsMiddleware implements MiddlewareInterface
{
    #[Inject(ArticleService::class)]
    private ArticleService $articleService;

    public function handle(Request $request, Response $response)
    {
        $validator = ValidatorConstructor::make($request->all(),[
            "article_id" => "numeric|required"
        ]);
        if($validator->fails())
        {
            Context::json([
                "Ok" => false,
                "Msg" => "ID非法"
            ]);
            Context::abort();
            return;
        }
        $validated = $validator->validated();
        $article_id = $validated["article_id"];
        if(!$this->articleService->check_article_exists($article_id))
        {
            Context::json([
                "Ok" => false,
                "Data" => "文章不存在"
            ]);
            Context::abort();
        }
    }

}
