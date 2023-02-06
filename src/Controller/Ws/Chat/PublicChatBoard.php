<?php

namespace Watish\WatishWEB\Controller\Ws\Chat;

use Swoole\Coroutine;
use Watish\Components\Attribute\Path;
use Watish\Components\Attribute\Prefix;
use Watish\Components\Includes\Context;
use Watish\Components\Struct\Request;
use Watish\Components\Struct\Response;

#[Prefix('/ws/chat/public/board')]
class PublicChatBoard
{
    #[Path('/')]
    public function index(Request $request,Response $response) :void
    {
        $response->upgrade();
        $fd = $response->fd;
        $uuid = md5(uniqid().time().rand(1,9999).$fd);
        Coroutine::create(function () use ($response,$uuid){
            Context::globalSet_Add_Response("chat.public.board",$response,$uuid);
            while (1)
            {
                Coroutine::sleep(0.001);
                $frame = $response->recv();
                if($frame->isClosed())
                {
                    Context::globalSet_Del("chat.public.board",$uuid);
                    return;
                }
                $json = $frame->data;
                $msgArr = json_decode($json,true);
                $type = $msgArr["type"] ?? "heartbeat";
                $msg = $msgArr["msg"] ?? "";
                if($type == "msg" and $msg !== "")
                {
                    Context::globalSet_PushAll("chat.public.board",$msg);
                }
            }
        });
    }
}
