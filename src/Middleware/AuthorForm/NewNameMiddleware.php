<?php

namespace Watish\WatishWEB\Middleware\AuthorForm;

use Watish\Components\Attribute\Inject;
use Watish\Components\Constructor\ValidatorConstructor;
use Watish\Components\Includes\Context;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;
use Watish\WatishWEB\Middleware\MiddlewareInterface;
use Watish\WatishWEB\Service\UserService;

class NewNameMiddleware implements MiddlewareInterface
{
    #[Inject(UserService::class)]
    private UserService $userService;

    public function handle(Request $request, Response $response)
    {
        $validator = ValidatorConstructor::make($request->all(),[
            "name" => "string|min:6|max:12|required"
        ]);
        if($validator->fails())
        {
            Context::json([
                "Ok" => false,
                "Msg" => "表单数据非法"
            ]);
            Context::abort();
            return;
        }
        $name = $validator->validated()["name"];
        if($this->userService->check_exists_by_name($name))
        {
            Context::json([
                "Ok" => false,
                "Msg" => "用户名已存在"
            ]);
            Context::abort();
        }
    }
}
