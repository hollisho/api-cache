<?php

namespace hollisho\apicache\Adapter;


class FilesystemAdapter extends AbstractAdapter
{
    private $directory;

    private $cache_path;

    private $cache_time = 3;

    private $cache_extension = '.cache';

    /**
     * @return string
     */
    public function getCachePath()
    {
        if (null === $this->cache_path) {
            $this->cache_path = $this->directory;
        }
        if (!file_exists($this->cache_path)) {
            @mkdir($this->cache_path, 0777, true);
        }
        return $this->cache_path;
    }

    /**
     * @return int
     */
    public function getCacheTime()
    {
        return $this->cache_time;
    }

    /**
     * @param int $cache_time
     */
    public function setCacheTime(int $cache_time)
    {
        $this->cache_time = $cache_time;
    }

    /**
     * @return string
     */
    public function getCacheExtension()
    {
        return $this->cache_extension;
    }

    /**
     * @param string $cache_extension
     */
    public function setCacheExtension(string $cache_extension)
    {
        $this->cache_extension = $cache_extension;
    }

    public function __construct($namespace = '', $directory = null, $cache_time)
    {
        $this->cache_time = $cache_time;
        $this->init($namespace, $directory);
    }


    public function get($key)
    {
        if ($this->isCache($key)) {
            $filename = $this->getCachePath() . $this->safeFilename($key) . $this->getCacheExtension();
            return file_get_contents($filename);
        }

        return false;
    }

    public function put($key, $value)
    {
        $filename = $this->getCachePath() . $this->safeFilename($key) . $this->getCacheExtension();

        file_put_contents($filename, $value);
    }

    public function isCache($key) {
        $filename = $this->getCachePath() . $this->safeFilename($key) . $this->getCacheExtension();
        if(file_exists($filename) && (filemtime($filename) + $this->getCacheTime() >= time())) {
            return true;
        }
        return false;
    }

    private function safeFilename($filename)
    {
        return preg_replace('/[^0-9a-z\.\_\-]/i','', strtolower($filename));
    }

    private function init(string $namespace, ?string $directory)
    {
        if (!isset($directory[0])) {
            $directory = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'api-cache';
        } else {
            $directory = realpath($directory) ?: $directory;
        }
        if (isset($namespace[0])) {
            if (preg_match('#[^-+_.A-Za-z0-9]#', $namespace, $match)) {
                throw new InvalidArgumentException(sprintf('Namespace contains "%s" but only characters in [-+_.A-Za-z0-9] are allowed.', $match[0]));
            }
            $directory .= \DIRECTORY_SEPARATOR.$namespace;
        } else {
            $directory .= \DIRECTORY_SEPARATOR.'@';
        }
        if (!file_exists($directory)) {
            @mkdir($directory, 0777, true);
        }
        $directory .= \DIRECTORY_SEPARATOR;
        // On Windows the whole path is limited to 258 chars
        if ('\\' === \DIRECTORY_SEPARATOR && \strlen($directory) > 234) {
            throw new InvalidArgumentException(sprintf('Cache directory too long (%s).', $directory));
        }

        $this->directory = $directory;
    }
}