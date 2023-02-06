<?php

namespace Watish\WatishWEB\Service;

use Aura\SqlQuery\QueryFactory;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\Builder;
use PDO;
use SQLite3;
use Watish\Components\Struct\DatabaseExtend;
use Watish\Components\Utils\PDOPool;

class BaseService
{

    public function toArray(mixed $data): array
    {
        return json_decode(json_encode($data), true);
    }

    public function getSqlite(): PDO
    {
        return new PDO('sqlite:'.BASE_DIR.'/database/database.db');
    }

    public function paginatedArray(Builder $builder,int $page,int $limit) :array
    {
        $total = $builder->count();
        if($total<=0)
        {
            return [
                "current" => 0,
                "pages" => 0,
                "total" => 0,
                "data" => []
            ];
        }
        $pages = (int)($total/$limit);
        if($total%$limit > 0)
        {
            $pages++;
        }
        $skip = ($page-1)*$limit;
        return [
            "current" => $page,
            "pages" => $pages,
            "total" => $total,
            "data" => $this->toArray($builder->skip($skip)->limit($limit)->get())
        ];
    }
}
