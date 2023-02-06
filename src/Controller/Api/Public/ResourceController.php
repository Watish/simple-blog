<?php

namespace Watish\WatishWEB\Controller\Api\Public;

use Watish\Components\Attribute\Inject;
use Watish\Components\Attribute\Middleware;
use Watish\Components\Attribute\Path;
use Watish\Components\Attribute\Prefix;
use Watish\Components\Constructor\ValidatorConstructor;
use Watish\Components\Includes\Database;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;
use Watish\WatishWEB\Middleware\ArticleExistsMiddleware;
use Watish\WatishWEB\Middleware\TokenValidMiddleware;
use Watish\WatishWEB\Service\ArticleService;
use Watish\WatishWEB\Service\BaseService;
use Watish\WatishWEB\Service\ResourceService;
use Watish\WatishWEB\Service\UserService;

#[Prefix("/api/public/resource")]
class ResourceController{

    #[Inject(UserService::class)]
    private UserService $userService;

    #[Inject(ResourceService::class)]
    private ResourceService $resourceService;

    #[Path("/user/avatar/{user_id}")]
    public function user_avatar(Request $request,Response $response) :void
    {
        $user_id = (int)$request->route("user_id");
        if($this->userService->check_user_exists($user_id))
        {
            $user_info = $this->userService->get_user_info_by_id($user_id);
            $user_avatar = $user_info["user_avatar"] ?? null;
            if($user_avatar)
            {
                $response->redirect($user_avatar);
                return;
            }
        }
        $response->sendfile(BASE_DIR."/storage/Resource/user.png");
    }

    #[Path('/uuid/{uuid}')]
    public function get_assert(Request $request,Response $response) :void
    {
        $uuid = $request->route("uuid");
        $validator = ValidatorConstructor::make(["uuid"=>$uuid],[
            "uuid" => "required|string"
        ]);
        if($validator->fails())
        {
            $response->end();
            return;
        }
        if(!$this->resourceService->exists($uuid))
        {
            $response->end();
            return;
        }
        $file = $this->resourceService->get($uuid);
        $response->header("Content-Type",$file->getMime());
        $response->write($file->getContent());
    }

    #[Path('/upload')]
    #[Middleware([TokenValidMiddleware::class])]
    public function upload(Request $request,Response $response) :array
    {
        $files = $request->all();
        return [
            "files" => $files
        ];
    }

}
