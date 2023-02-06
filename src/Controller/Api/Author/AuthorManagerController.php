<?php

namespace Watish\WatishWEB\Controller\Api\Author;

use Watish\Components\Attribute\Inject;
use Watish\Components\Attribute\Middleware;
use Watish\Components\Attribute\Path;
use Watish\Components\Attribute\Prefix;
use Watish\Components\Constructor\LocalFilesystemConstructor;
use Watish\Components\Includes\Database;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;
use Watish\WatishWEB\Dao\ResourceFile;
use Watish\WatishWEB\Middleware\AuthorForm\NewAvatarMiddleware;
use Watish\WatishWEB\Middleware\AuthorForm\NewNameMiddleware;
use Watish\WatishWEB\Middleware\TokenValidMiddleware;
use Watish\WatishWEB\Middleware\UserExistsMiddleware;
use Watish\WatishWEB\Service\AuthorService;
use Watish\WatishWEB\Service\ResourceService;
use Watish\WatishWEB\Service\UserService;

#[Prefix('/api/author/manage')]
#[Middleware([TokenValidMiddleware::class])]
class AuthorManagerController
{
    #[Inject(AuthorService::class)]
    private AuthorService $authorService;

    #[Inject(UserService::class)]
    private UserService $userService;

    #[Inject(ResourceService::class)]
    private ResourceService $resourceService;

    #[Path('/info')]
    public function author_info(Request $request) :array
    {
        $params = $request->all();
        $token = $params["token"];
        $user_info = $this->userService->get_user_info_by_token($token);
        return [
            "Ok" => true,
            "Data" => $user_info
        ];
    }

    #[Path("/change/name")]
    #[Middleware([NewNameMiddleware::class])]
    public function change_name(Request $request) :array
    {
        $params = $request->all();
        $token = $params["token"];
        $user_info = $this->userService->get_user_info_by_token($token);
        $user_id = $user_info["user_id"];
        $new_mame = $params["name"];
        $this->userService->change_user_name($user_id,$new_mame);
        return [
            "Ok" => true,
            "Msg" => "修改成功"
        ];
    }

    #[Path('/change/avatar')]
    #[Middleware([NewAvatarMiddleware::class])]
    public function change_avatar(Request $request,Response $response): array
    {
        $avatarFile = $request->file("avatar");
        $params = $request->all();
        $token = $params["token"];
        $user_info = $this->userService->get_user_info_by_token($token);
        $user_id = $user_info["user_id"];
        $uuid = md5("avatar.user.{$user_id}");
        $file = new ResourceFile();
        $file->setContent($avatarFile->getContent());
        $file->setSize($avatarFile->getSize());
        $file->setName($avatarFile->getFilename());
        $file->setMime($avatarFile->getMimeType());
        $file->setModifiedTime($avatarFile->getMTime());
        $this->resourceService->write($uuid,$file);
        $visitPath = $this->resourceService->getVisitPath($uuid);
        Database::instance()->table("users")
            ->where("user_id",$user_id)
            ->update([
                "user_avatar" => $visitPath
            ]);
        return [
            "Ok" => true,
            "Data" => $this->userService->get_user_info_by_id($user_id)
        ];
    }

}
