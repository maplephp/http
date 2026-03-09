<?php

declare(strict_types=1);

namespace MaplePHP\Http;

use MaplePHP\Http\Interfaces\PathInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class Path implements PathInterface
{
    private array $parts;
    private array $vars;
	private ServerRequestInterface|RequestInterface $request;

	public function __construct(array $parts, ServerRequestInterface|RequestInterface $request)
    {
        $this->parts = $parts;
        $this->vars = $this->partsToVars($parts);
		$this->request = $request;
    }

	/**
	 * Get PSR URI instance with whitelisted path and
	 * cleared, query and fragments
	 *
	 * @return UriInterface
	 */
	public function uri(): UriInterface
	{
		return $this->request->getUri()
			->withPath(implode("/", $this->vars))
			->withQuery("")
			->withFragment("");
	}

	/**
	 * Get current full URL with whitelisted path
	 *
	 * @return string
	 */
	public function url(): string
	{
		return $this->uri()->getUri();
	}

    /**
     * With URI path type key
     *
     * @param null|string|array $type
     * @return static
     */
    public function withType(null|string|array $type): self
    {
        if (is_string($type)) {
            $type = [$type];
        }
        if ($type === null) {
            $type = [];
        }

        $inst = clone $this;
        $parts = [];
        $vars = $this->partsToVars($inst->parts, $type, function ($key, $item) use (&$parts, $type) {
            $parts[$key] = $item;
        });
        $inst->parts = $parts;
        $inst->vars = $vars;
        return $inst;
    }

    /**
     * Same as withType except that you Need to select a part
     *
     * @param string|array $type
     * @return static
     */
    public function select(string|array $type): self
    {
        return $this->withType($type);
    }

    /**
     * Same as withType except it will only reset
     *
     * @return static
     */
    public function reset(): self
    {
        return $this->withType(null);
    }

    /**
     * Append to URI path
     *
     * @param array|string $arr
     * @return static
     */
    public function append(array|string $arr): self
    {
        $inst = clone $this;
        if (is_string($arr)) {
            $arr = [$arr];
        }

        $inst->vars = array_merge($inst->vars, $arr);
        return $inst;
    }

    /**
     * Prepend to URI path
     *
     * @param array|string $arr
     * @return static
     */
    public function prepend(array|string $arr): self
    {
        $inst = clone $this;
        if (is_string($arr)) {
            $arr = [$arr];
        }

        $inst->vars = array_merge($arr, $inst->vars);
        return $inst;
    }

    /**
     * Get vars/path as array
     *
     * @return array
     */
    public function vars(): array
    {
        return $this->vars;
    }

    /**
     * Get vars/path as array
     *
     * @return array
     */
    public function parts(): array
    {
        return $this->parts;
    }

    /**
     * Get expected slug from path
     *
     * @return array
     */
    public function get(): array
    {
        return array_filter(explode("/", $this->last()));
    }


    /**
     * Get last path item
     *
     * @return string
     */
    public function last(): string
    {
		$end = end($this->vars);
        return is_string($end) ? $end : '';
    }

    /**
     * Get first path item
     *
     * @return string
     */
    public function first(): string
    {
	    $reset = reset($this->vars);
	    return is_string($reset) ? $reset : '';
    }

    /**
     * Get travers to prev path item
     *
     * @return string
     */
    public function prev(): string
    {
        return prev($this->vars);
    }

    /**
     * Get travers to next path item
     *
     * @return string
     */
    public function next(): string
    {
        return next($this->vars);
    }

    /**
     * Break parts down
     *
     * @param array $parts
     * @param array|null $type
     * @param callable|null $call
     * @return array
     */
    protected function partsToVars(array $parts, ?array $type = null, ?callable $call = null): array
    {
        $vars = [];
        foreach ($parts as $key => $item) {

            if ($type === null || in_array($key, $type)) {

                if (is_array($item)) {
                    $vars = array_merge($vars, $item);
                } else {
                    $vars[] = $item;
                }
                if ($call !== null) {
                    $call($key, $item);
                }

            }
        }
        return $vars;
    }
}
