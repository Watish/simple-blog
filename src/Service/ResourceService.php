<?php

namespace Watish\WatishWEB\Service;

use Illuminate\Filesystem\FilesystemAdapter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Watish\Components\Attribute\Inject;
use Watish\Components\Constructor\LocalFilesystemConstructor;
use Watish\Components\Includes\Database;
use Watish\Components\Utils\Logger;
use Watish\WatishWEB\Dao\ResourceFile;

class ResourceService
{
    #[Inject(BaseService::class)]
    private BaseService $baseService;

    public function get(string $key) :ResourceFile|null
    {
        $fileSystem = $this->getFileSystem();
        $uuid = md5($key);
        $file = new ResourceFile();
        if($fileSystem->exists("/database/resource/{$uuid}"))
        {
            $builder = Database::instance()->table("resource")->where("resource_uuid",$uuid);
            if($builder->exists())
            {
                $resource_info = $builder->first();
                $resource_info = $this->baseService->toArray($resource_info);
                try{
                    $file->setContent($fileSystem->read("/database/resource/{$uuid}"));
                }catch (\Exception $exception){
                    Logger::exception($exception);
                    return null;
                }
                $file->setMime($resource_info["resource_mime"]);
                $file->setName($resource_info["resource_name"]);
                $file->setSize($resource_info["resource_size"]);
                return $file;
            }
        }
        return null;
    }

    public function getFilePath(string $key) :string|null
    {
        $fileSystem  = $this->getFileSystem();
        $uuid = md5($key);
        if($fileSystem->exists("/database/resource/{$uuid}"))
        {
            return BASE_DIR."database/resource/{$uuid}";
        }
        return null;
    }

    public function getVisitPath(string $key): string
    {
        $fileSystem  = $this->getFileSystem();
        return '/api/public/resource/uuid/'.$key;
    }

    public function exists(string $key) :bool
    {
        $fileSystem  = $this->getFileSystem();
        $uuid = md5($key);
        if($fileSystem->exists("/database/resource/{$uuid}"))
        {
            return true;
        }
        return false;
    }

    public function write(string $key,ResourceFile $file) :void
    {
        $fileSystem  = $this->getFileSystem();
        $uuid = md5($key);
        if($fileSystem->exists("/database/resource/{$uuid}"))
        {
            try{
                $fileSystem->delete("/database/resource/{$uuid}");
            }catch (\Exception $exception)
            {
                Logger::exception($exception);
            }
        }
        $builder = Database::instance()->table("resource")->where("resource_uuid",$uuid);
        if($builder->exists())
        {
            $builder->update([
                "resource_name" => $file->getName(),
                "resource_mime" => $file->getMime(),
                "resource_size" => $file->getSize(),
                "create_time"   => $file->getModifiedTime()
            ]);
        }else{
            Database::instance()->table("resource")
                ->insertGetId([
                    "resource_uuid" => $uuid,
                    "resource_name" => $file->getName(),
                    "resource_mime" => $file->getMime(),
                    "resource_size" => $file->getSize(),
                    "create_time"   => $file->getModifiedTime()
                ]);
        }
        try{
            $fileSystem->write("/database/resource/{$uuid}",$file->getContent());
        }catch (\Exception $exception)
        {
            Logger::exception($exception);
        }
    }

    private function getFileSystem(): FilesystemAdapter
    {
        $fileSystem = LocalFilesystemConstructor::getIlluminateFilesystem();
        if(!$fileSystem->directoryExists("/database/resource/"))
        {
            try{
                $fileSystem->makeDirectory("/database/resource/");
            }catch (\Exception $exception)
            {
                Logger::exception($exception);
            }
        }
        return $fileSystem;
    }
}
