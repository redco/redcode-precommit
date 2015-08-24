<?php

namespace RedCode\GitHook;

class GitHook
{
    const VERSION = '0.1';
    const STATUS_ACTIVE = __LINE__;
    const STATUS_NOT_ACTIVE = __LINE__;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $script;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $status;

    /**
     * @var array
     */
    private $fileTypes;

    /**
     * @var array
     */
    private $paths;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * @param string $script
     * @return self
     */
    public function setScript($script)
    {
        $this->script = $script;

        return $this;
    }

    /**
     * @return array
     */
    public function getFileTypes()
    {
        return $this->fileTypes;
    }

    /**
     * @param string $fileType
     * @return self
     */
    public function addFileType($fileType)
    {
        $this->fileTypes[] = $fileType;

        return $this;
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param string $path
     * @return self
     */
    public function addPath($path)
    {
        $this->paths[] = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return self
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    public function match($file)
    {
        return true;
    }
}
