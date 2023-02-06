<?php

namespace Watish\WatishWEB\Controller\Ws\Notice;

use Swoole\Coroutine;
use Watish\Components\Attribute\Path;
use Watish\Components\Attribute\Prefix;
use Watish\Components\Includes\Context;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;
use Watish\Components\Utils\Logger;

#[Prefix('/ws/notice/public')]
class PublicNoticeController
{
    #[Path('/')]
    public function handle(Request $request,Response $response): void
    {
        $response->upgrade();
        $uuid = md5(uniqid().time().rand(1,999));
        Context::globalSet_Add_Response("notice.channel.public",$response,$uuid);
        Coroutine::create(function () use ($response,$uuid){
            $response->push(json_encode([
                "msg" => "您已连接公共通知频道",
                "type" => "info"
            ]));
            while (1)
            {
                $frame = $response->recv();
                if($frame->isClosed())
                {
                    Context::globalSet_Del("notice.channel.public",$uuid);
                    return;
                }
                $data = $frame->data;
                Logger::info($data);
                Coroutine::sleep(0.001);
            }
        });
    }

}
