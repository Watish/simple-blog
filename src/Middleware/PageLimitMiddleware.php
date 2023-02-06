<?php

namespace Watish\WatishWEB\Middleware;

use Watish\Components\Constructor\ValidatorConstructor;
use Watish\Components\Includes\Context;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;

class PageLimitMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response)
    {
        $validator = ValidatorConstructor::make($request->all(),[
            "page" => "sometimes|numeric",
            "limit" => "sometimes|numeric"
        ]);
        if($validator->fails())
        {
            Context::json([
                "Ok" => false,
                "Msg" => "表单数据非法"
            ]);
            Context::abort();
        }
    }
}
