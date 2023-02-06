<?php

namespace Watish\WatishWEB\Middleware;

use Watish\Components\Attribute\Inject;
use Watish\Components\Constructor\ValidatorConstructor;
use Watish\Components\Includes\Context;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;
use Watish\WatishWEB\Service\SortService;

class SortExistsMiddleware implements MiddlewareInterface
{
    #[Inject(SortService::class)]
    private SortService $sortService;

    public function handle(Request $request, Response $response)
    {
        $validator = ValidatorConstructor::make($request->all(),[
           "sort_id" => "numeric|required"
        ]);
        if($validator->fails())
        {
            Context::json([
                "Ok" => false,
                "Msg" => "分類ID非法"
            ]);
            Context::abort();
            return;
        }
        $validated = $validator->validated();
        $sort_id = $validated["sort_id"];
        if(!$this->sortService->get_sort_name($sort_id))
        {
            Context::json([
                "Ok" => false,
                "Msg" => "分類不存在"
            ]);
            Context::abort();
        }
    }
}
