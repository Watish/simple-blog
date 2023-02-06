<?php

namespace Watish\WatishWEB\Controller\Api\Author;

use Watish\Components\Attribute\Inject;
use Watish\Components\Attribute\Middleware;
use Watish\Components\Attribute\Path;
use Watish\Components\Attribute\Prefix;
use Watish\Components\Includes\Database;
use Watish\Components\Struct\Request;
use Watish\WatishWEB\Middleware\ArticleExistsMiddleware;
use Watish\WatishWEB\Middleware\ArticleForm\ArticleSubmitCheckMiddleware;
use Watish\WatishWEB\Middleware\TokenValidMiddleware;
use Watish\WatishWEB\Service\ArticleService;
use Watish\WatishWEB\Service\ContentService;
use Watish\WatishWEB\Service\UserService;

#[Prefix('/api/author/edit')]
#[Middleware([TokenValidMiddleware::class])]
class EditController
{
    #[Inject(UserService::class)]
    private UserService $userService;

    #[Inject(ContentService::class)]
    private ContentService $contentService;

    #[Inject(ArticleService::class)]
    private ArticleService $articleService;

    #[Path('/new')]
    #[Middleware([ArticleSubmitCheckMiddleware::class])]
    public function new_article(Request $request) :array
    {
        $params = $request->all();
        $title = $params["title"];
        $content = $params["content"];
        $tags = $params["tags"] ?? "";
        $sort_id = $params["sort_id"] ?? 0;
        $token = $params["token"];
        $user_info = $this->userService->get_user_info_by_token($token);
        $user_id = $user_info["user_id"];
        $uuid = md5($user_id.uniqid().time().rand(1000,9999));
        $this->contentService->put($uuid,$content);
        $article_id = Database::instance()->table("articles")
            ->insertGetId([
                "article_title" => $title,
                "content_id" => $uuid,
                "author_id" => $user_id,
                "sort_id" => $sort_id,
                "article_tags" => $tags,
                "create_time" => time(),
                "modified_time" => time(),
                "show_switch" => 1
            ]);
        return [
            "Ok" => (bool)($article_id>0),
            "Data" => $this->articleService->get_article_info($article_id)
        ];
    }

    #[Path('/change')]
    #[Middleware([ArticleExistsMiddleware::class,ArticleSubmitCheckMiddleware::class])]
    public function change_article(Request $request) :array
    {
        $params = $request->all();
        $token = $params["token"];
        $article_id = $params["article_id"];
        $title = $params["title"];
        $content = $params["content"];
        $user_info = $this->userService->get_user_info_by_token($token);
        $user_id = $user_info["user_id"];
        $article_info = $this->articleService->get_article_info($article_id);
        $author_id = $article_info["author_id"];
        if($user_id !== $author_id)
        {
            return [
                "Ok" => false,
                "Msg" => "非本人文章"
            ];
        }
        $data["article_title"] = $title;
        if(isset($params["sort_id"]))
        {
            $data["sort_id"] = $params["sort_id"];
        }
        if(isset($params["tags"]))
        {
            $data["article_tags"] = $params["tags"];
        }
        if(isset($params["show_switch"]))
        {
            $show_switch = $params["show_switch"]>0 ? 1 : 0;
            $data["show_switch"] = $show_switch;
        }
        $this->contentService->put($article_info["content_id"],$content);
        $data["modified_time"] = time();
        $code = Database::instance()->table("articles")
            ->where("article_id",$article_id)
            ->update($data);
        return [
            "Ok" => (bool)($code>=0),
            "Data" => $this->articleService->get_article_info($article_id)
        ];
    }
}
