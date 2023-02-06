<?php

namespace Watish\WatishWEB\Middleware\ArticleForm;

use Watish\Components\Constructor\ValidatorConstructor;
use Watish\Components\Includes\Context;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;
use Watish\WatishWEB\Middleware\MiddlewareInterface;

class ArticleSubmitCheckMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response)
    {
        $params = $request->all();
        $validator = ValidatorConstructor::make($params, [
            "title" => "string|required",
            "content" => "string|required",
            "sort_id" => "numeric|sometimes",
            "tags" => "string|sometimes"
        ]);
        if ($validator->fails())
        {
            Context::json([
                "Ok" => false,
                "Msg" => "表單數據非法"
            ]);
            Context::abort();
            return;
        }
    }
}
