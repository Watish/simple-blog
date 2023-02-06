<?php

namespace Watish\WatishWEB\Middleware\TableField;

use Watish\Components\Constructor\ValidatorConstructor;
use Watish\Components\Includes\Context;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;
use Watish\WatishWEB\Middleware\MiddlewareInterface;

class TableFieldDeleteMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response)
    {
        $params = $request->all();
        $validator = ValidatorConstructor::make($params,[
            "table" => "string|required",
            "where" => "json|required"
        ]);
        if($validator->fails())
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
