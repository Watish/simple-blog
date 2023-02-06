<?php

namespace Watish\WatishWEB\Middleware;

use Watish\Components\Attribute\Inject;
use Watish\Components\Constructor\ValidatorConstructor;
use Watish\Components\Includes\Context;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;
use Watish\WatishWEB\Service\UserService;

class AdminAuthMiddleware implements MiddlewareInterface
{
    #[Inject(UserService::class)]
    private UserService $userService;

    public function handle(Request $request, Response $response)
    {
        $validator = ValidatorConstructor::make($request->all(),[
           "token" => "string|min:32|max:32|required"
        ]);
        if($validator->fails())
        {
            Context::json([
                "Ok" => false,
                "Msg" => "令牌非法"
            ]);
            Context::abort();
            return;
        }
        $validated = $validator->validated();
        $token = $validated["token"];
        if(!$this->userService->check_token_valid($token))
        {
            Context::json([
                "Ok" => false,
                "Msg" => "令牌過期"
            ]);
            Context::abort();
            return;
        }
        $user_info = $this->userService->get_user_info_by_token($token);
        $is_admin = $user_info["is_admin"] ?? 0;
        if(!$is_admin)
        {
            Context::json([
                "Ok" => false,
                "Msg" => "無權訪問"
            ]);
            Context::abort();
        }
    }

}
