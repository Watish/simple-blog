<?php

namespace Watish\WatishWEB\Middleware;

use Watish\Components\Attribute\Inject;
use Watish\Components\Constructor\ValidatorConstructor;
use Watish\Components\Includes\Context;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;
use Watish\WatishWEB\Service\UserService;

class UserExistsMiddleware implements MiddlewareInterface
{
    #[Inject(UserService::class)]
    private UserService $userService;

    public function handle(Request $request, Response $response)
    {
        $validator = ValidatorConstructor::make($request->all(),[
           "user_id" => "numeric|required"
        ]);
        if($validator->fails())
        {
            Context::json([
                "Ok" => false,
                "Msg" => "用戶ID非法"
            ]);
            Context::abort();
            return;
        }
        $validated = $validator->validated();
        $user_id = $validated["user_id"];
        if(!$this->userService->check_user_exists($user_id))
        {
            Context::json([
                "Ok" => false,
                "Msg" => "用戶不存在"
            ]);
            Context::abort();
        }
    }

}
