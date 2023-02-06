<?php

namespace Watish\WatishWEB\Middleware\AuthorForm;

use Watish\Components\Constructor\ValidatorConstructor;
use Watish\Components\Includes\Context;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;
use Watish\WatishWEB\Middleware\MiddlewareInterface;

class NewAvatarMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response)
    {
        $params = $request->all();
        $validator = ValidatorConstructor::make($params,[
            "avatar" => "required|image"
        ]);
        if($validator->fails())
        {
            Context::json([
                "Ok" => false,
                "Msg" => "头像文件非法",
            ]);
            Context::abort();
        }
    }

}
