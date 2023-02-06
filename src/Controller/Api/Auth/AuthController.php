<?php

namespace Watish\WatishWEB\Controller\Api\Auth;

use Watish\Components\Attribute\Inject;
use Watish\Components\Attribute\Middleware;
use Watish\Components\Attribute\Path;
use Watish\Components\Attribute\Prefix;
use Watish\Components\Constructor\ValidatorConstructor;
use Watish\Components\Includes\Database;
use Watish\Components\Struct\Request;
use Watish\Components\Utils\ENV;
use Watish\WatishWEB\Middleware\UserExistsMiddleware;
use Watish\WatishWEB\Service\UserService;

#[Prefix('/api/auth')]
class AuthController
{
    #[Inject(UserService::class)]
    private UserService $userService;

    #[Path('/register')]
    public function do_register(Request $request) :array
    {
        $params = $request->all();
        $validator = ValidatorConstructor::make($params,[
            "user" => "string|min:6|max:16|required",
            "password" => "string|min:6|max:24|required"
        ]);
        if($validator->fails())
        {
            return [
                "Ok" => false,
                "Msg" => "表單數據非法"
            ];
        }
        $validated = $validator->validated();
        $user = $validated["user"];
        $password = $validated["password"];
        if($this->userService->check_exists_by_name($user))
        {
            return [
                "Ok" => false,
                "Msg" => "用戶已存在"
            ];
        }
        $first = false;
        if(Database::instance()->table("users")->count() <= 0)
        {
            $first = true;
        }
        if(!$first)
        {
            if(ENV::getConfig("App")["ALLOW_REGISTER"] !== "1")
            {
                return [
                    "Ok" => false,
                    "Msg" => "禁止注冊"
                ];
            }
        }
        $rowArray = [
            "user_name" => $user,
            "user_password" => md5($password),
            "user_avatar" => null,
            "is_admin" => (int)$first,
            "register_time" => time(),
            "token" => md5(uniqid() .time() . rand(1,999))
        ];
        $user_id = Database::instance()->table("users")
            ->insertGetId($rowArray);
        unset($rowArray["user_password"]);
        $rowArray["user_id"] = $user_id;
        return [
            "Ok" => true,
            "Data" => $rowArray
        ];
    }

    #[Path('/login')]
    public function do_login(Request $request):array
    {
        $params = $request->all();
        $validator = ValidatorConstructor::make($params,[
            "user" => "string|required",
            "password" => "string|required"
        ]);
        if($validator->fails())
        {
            return [
                "Ok" => false,
                "Msg" => '表單數據非法'
            ];
        }
        $validated = $validator->validated();
        $user = $validated["user"];
        $password = $validated["password"];

        if(!$this->userService->check_exists_by_name($user))
        {
            return [
                "Ok" => false,
                "Msg" => "用戶不存在"
            ];
        }
        $user_info = $this->userService->get_user_info_by_name($user);
        $user_id = $user_info["user_id"];
        $real_password = $user_info["user_password"];
        if(md5($password) !== $real_password)
        {
            return [
                "Ok" => false,
                "Msg" => "賬號或密碼錯誤"
            ];
        }
        $new_token = md5($user_id.uniqid().time().rand(1000,9999));
        Database::instance()->table("users")
            ->where("user_id",$user_id)
            ->update([
                "token" => $new_token
            ]);
        $user_info["token"] = $new_token;
        unset($user_info["user_password"]);
        return [
            "Ok" => true,
            "Data" => $user_info
        ];
    }
}
