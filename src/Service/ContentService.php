<?php

namespace Watish\WatishWEB\Service;

use Illuminate\Filesystem\FilesystemAdapter;
use Watish\Components\Constructor\LocalFilesystemConstructor;
use Watish\Components\Utils\Logger;

class ContentService
{
    public function read(string $key) :string|null
    {
        if(!$this->exists($key))
        {
            return null;
        }
        $fileSystem = $this->getFileSystem();
        $uuid = md5($key);
        $result = "";
        try{
            $result = $fileSystem->read("database/content/{$uuid}");
        }catch (\Exception $exception)
        {
            Logger::exception($exception);
        }
        return $result;
    }

    public function exists(string $key) :bool
    {
        $fileSystem = $this->getFileSystem();
        $uuid = md5($key);
        if($fileSystem->exists("database/content/{$uuid}"))
        {
            return true;
        }
        return false;
    }

    public function put(string $key,string $content) :void
    {
        $fileSystem = $this->getFileSystem();
        $uuid = md5($key);
        if($fileSystem->exists("database/content/{$uuid}"))
        {
            $fileSystem->delete("database/content/{$uuid}");
        }
        try{
            $fileSystem->write("database/content/{$uuid}",$content);
        }catch (\Exception $exception)
        {
            Logger::exception($exception);
        }
    }

    private function getFileSystem(): FilesystemAdapter
    {
        $fileSystem =  LocalFilesystemConstructor::getIlluminateFilesystem();
        if(!$fileSystem->directoryExists("database/content/"))
        {
            $fileSystem->makeDirectory("database/content/");
        }
        return $fileSystem;
    }

}
