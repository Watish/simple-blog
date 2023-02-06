<?php

namespace Watish\WatishWEB\Dao;

class ResourceFile
{
    public string $name;
    public string $mime;
    public int $size;
    public int $modified_time;
    public string $content;

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * @return int
     */
    public function getModifiedTime(): int
    {
        return $this->modified_time;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $mime
     */
    public function setMime(string $mime): void
    {
        $this->mime = $mime;
    }

    /**
     * @param int $modified_time
     */
    public function setModifiedTime(int $modified_time): void
    {
        $this->modified_time = $modified_time;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param int $size
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
