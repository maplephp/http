<?php

namespace MaplePHP\Http\Interfaces;


use Psr\Http\Message\UriInterface;

interface PathInterface
{

	/**
	 * Get PSR URI instance with whitelisted path and
	 * cleared, query and fragments
	 *
	 * @return UriInterface
	 */
	public function uri(): UriInterface;

	/**
	 * Get current full URL with whitelisted path
	 *
	 * @return string
	 */
	public function url(): string;

	/**
	 * With URI path type key
	 *
	 * @param null|string|array $type
	 * @return static
	 */
	public function withType(null|string|array $type): self;

	/**
	 * Same as withType except that you Need to select a part
	 *
	 * @param string|array $type
	 * @return static
	 */
	public function select(string|array $type): self;

	/**
	 * Same as withType except it will only reset
	 *
	 * @return static
	 */
	public function reset(): self;

	/**
	 * Append to URI path
	 *
	 * @param array|string $arr
	 * @return static
	 */
	public function append(array|string $arr): self;

	/**
	 * Prepend to URI path
	 *
	 * @param array|string $arr
	 * @return static
	 */
	public function prepend(array|string $arr): self;

	/**
	 * Get vars/path as array
	 *
	 * @return array
	 */
	public function vars(): array;

	/**
	 * Get vars/path as array
	 *
	 * @return array
	 */
	public function parts(): array;

	/**
	 * Get expected slug from path
	 *
	 * @return array
	 */
	public function get(): array;

	/**
	 * Get last path item
	 *
	 * @return string
	 */
	public function last(): string;

	/**
	 * Get first path item
	 *
	 * @return string
	 */
	public function first(): string;

	/**
	 * Get travers to prev path item
	 *
	 * @return string
	 */
	public function prev(): string;

	/**
	 * Get travers to next path item
	 *
	 * @return string
	 */
	public function next(): string;
}
