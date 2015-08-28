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
    private $description;

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
     *
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
     *
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
     *
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
     *
     * @return self
     */
    public function addPath($path)
    {
        $path = realpath($path);
        if ($path) {
            $this->paths[] = realpath($path);
        }

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
     *
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     *
     * @return self
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public function match($file)
    {
        return
            $this->matchPaths($file) &&
            $this->matchExtension($file);
    }

    /**
     * @param array $files
     *
     * @return array
     */
    public function getMatchFiles(array $files)
    {
        return array_filter(
            $files,
            function ($file) {
                return $this->match($file);
            }
        );
    }

    /**
     * @param $file
     *
     * @return bool
     */
    private function matchExtension($file)
    {
        if (empty($this->getFileTypes())) {
            return true;
        }

        foreach ($this->getFileTypes() as $fileType) {
            if (preg_match(sprintf('/%s$/u', $fileType), $file)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $file
     *
     * @return bool
     */
    private function matchPaths($file)
    {
        if (empty($this->getPaths())) {
            return true;
        }

        foreach ($this->getPaths() as $path) {
            if (strpos(realpath($file), $path) !== false) {
                return true;
            }
        }

        return false;
    }
}
