<?php declare(strict_types=1);

namespace Fal\Cache;

interface SerializerInterface
{
    /**
     * Return string representation of PHP value
     *
     * @param mixed $arg
     * @return string
     */
    public function serialize($arg): string;

    /**
     * Return PHP value derived from string
     *
     * @param mixed $arg
     * @return mixed
     */
    public function unserialize($arg);
}
