<?php

namespace Watish\WatishWEB\Controller\Api\Admin;

use Watish\Components\Attribute\Inject;
use Watish\Components\Attribute\Middleware;
use Watish\Components\Attribute\Path;
use Watish\Components\Attribute\Prefix;
use Watish\Components\Includes\Database;
use Watish\Components\Struct\Request;
use Watish\Components\Utils\Logger;
use Watish\WatishWEB\Middleware\AdminAuthMiddleware;
use Watish\WatishWEB\Middleware\TableField\TableFieldDeleteMiddleware;
use Watish\WatishWEB\Middleware\TableField\TableFieldInsertMiddleware;
use Watish\WatishWEB\Middleware\TableField\TableFieldListMiddleware;
use Watish\WatishWEB\Middleware\TableField\TableFieldUpdateMiddleware;
use Watish\WatishWEB\Service\BaseService;

#[Prefix('/api/admin/manage')]
#[Middleware([AdminAuthMiddleware::class])]
class ManageController
{
    #[Inject(BaseService::class)]
    private BaseService $baseService;

    #[Path('/list')]
    #[Middleware([TableFieldListMiddleware::class])]
    public function list_rows(Request $request) :array
    {
        $params = $request->all();
        $table = $params["table"];
        $page = $params["page"];
        $limit = $params["limit"];
        $builder = Database::instance()->table($table);
        if(isset($params["where"]))
        {
            $whereJson = $params["where"];
//            Logger::info($whereJson,"AdminManageList");
            $whereList = json_decode($whereJson,true);
            foreach ($whereList as $listWhere)
            {
                $builder = $builder->where($listWhere[0],$listWhere[1],$listWhere[2]);
            }
        }
        $res = $this->baseService->paginatedArray($builder,$page,$limit);
        return [
            "Ok" => true,
            "Data" => $res
        ];
    }

    #[Path('/update')]
    #[Middleware([TableFieldUpdateMiddleware::class])]
    public function update_row(Request $request) :array
    {
        $params = $request->all();
        $table = $params["table"];
        $dataJson = $params["update"];
        $whereJson = $params["where"];
        $data = json_decode($dataJson,true);
        $where = json_decode($whereJson,true);
        $builder = Database::instance()->table($table);
        foreach ($where as $listWhere)
        {
            $builder = $builder->where($listWhere[0],$listWhere[1],$listWhere[2]);
        }
        $code = $builder->update($data);
        return [
            "Ok" => (bool)($code>=0)
        ];
    }

    #[Path('/delete')]
    #[Middleware([TableFieldDeleteMiddleware::class])]
    public function delete(Request $request) :array
    {
        $params = $request->all();
        $whereJson = $params["where"];
        $table = $params["table"];
        $builder = Database::instance()->table($table);
        $whereList = json_decode($whereJson,true);
        foreach ($whereList as $listWhere)
        {
            $builder = $builder->where($listWhere[0],$listWhere[1],$listWhere[2]);
        }
        $code = $builder->delete();
        return [
            "Ok" => (bool)($code>=0)
        ];
    }

    #[Path('/insert')]
    #[Middleware([TableFieldInsertMiddleware::class])]
    public function insert(Request $request) :array
    {
        $params = $request->all();
        $table = $params["table"];
        $dataJson = $params["data"];
        $data = json_decode($dataJson);
        $id = $builder = Database::instance()->table($table)
            ->insertGetId($data);
        return [
            "Ok" => (bool)($id>0)
        ];
    }

}
