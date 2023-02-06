<?php

namespace Watish\WatishWEB\Service;

use Watish\Components\Attribute\Inject;
use Watish\Components\Includes\Database;

class ArticleService
{
    #[Inject(SortService::class)]
    private SortService $sortService;

    #[Inject(BaseService::class)]
    private BaseService $baseService;

    #[Inject(ContentService::class)]
    private ContentService $contentService;

    #[Inject(AuthorService::class)]
    private AuthorService $authorService;

    public function check_article_exists(int $article_id) :bool
    {
        return Database::instance()->table("articles")
            ->where("article_id",$article_id)
            ->exists();
    }

    public function formatItem(array &$item) :void
    {
        if(isset($item["sort_id"]))
        {
            $sort_id = $item["sort_id"];
            $sort_name = "未分類";
            if($sort_id > 0)
            {
                $sort_name = $this->sortService->get_sort_name($sort_id);
            }
            $item["sort_name"] = $sort_name;
        }
        if(isset($item["content_id"]))
        {
            $content_id = $item["content_id"];
            $item["content"] = $this->contentService->read($content_id);
        }
        if(isset($item["author_id"]))
        {
            $author_id = $item["author_id"];
            $item["author_info"] = $this->authorService->get_author_info($author_id);
        }
    }

    public function get_article_info(int $article_id) :null|array
    {
        $res = Database::instance()->table("articles")
            ->where("article_id",$article_id)
            ->first();
        if(!$res)
        {
            return null;
        }
        $res = $this->baseService->toArray($res);
        $this->formatItem($res);
        return $res;
    }
}
