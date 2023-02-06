<?php

namespace Watish\WatishWEB\Middleware\TableField;

use Watish\Components\Constructor\ValidatorConstructor;
use Watish\Components\Includes\Context;
use Watish\Components\Includes\Database;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;
use Watish\WatishWEB\Middleware\MiddlewareInterface;

class TableFieldListMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response)
    {
        $params = $request->all();
        $validator = ValidatorConstructor::make($params,[
            "table" => "string|required",
            "page" => "numeric|required",
            "limit" => "numeric|required",
            "where" => "json|sometimes"
        ]);
        if($validator->fails())
        {
            Context::json([
                "Ok" => false,
                "Msg" => "表單數據非法"
            ]);
            Context::abort();
        }
        $validated = $validator->validated();
        $table = $validated["table"];
        $status = true;
        try {
            Database::instance()->table($table)->count();
        }catch (\Exception $exception)
        {
            Context::json([
                "Ok" => false,
                "Msg" => "數據表不存在"
            ]);
            Context::abort();
        }
    }

}
