<?php

namespace Watish\WatishWEB\Service;

use Watish\Components\Includes\Database;

class SortService
{
    public function get_sort_name(int $sort_id) :string
    {
        $res = Database::instance()->table("sorts")
            ->where("sort_id",$sort_id)
            ->first()->toArray();
        if(!$res)
        {
            return "暫無分類";
        }
        return $res["sort_name"];
    }
}
