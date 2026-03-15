<?php

namespace MaplePHP\Http;

use MaplePHP\DTO\Format\Arr;
use MaplePHP\DTO\Format\Str;

class Input
{

	/**
	 * Check if key exists in $_GET or $_POST
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function has(string $key): bool
	{
		return isset($_GET[$key]) || isset($_POST[$key]);
	}

	/**
	 * Check if key exists in $_GET
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function hasGet(string $key): bool
	{
		return isset($_GET[$key]);
	}

	/**
	 * Check if key exists in $_POST
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function hasPost(string $key): bool
	{
		return isset($_POST[$key]);
	}

	/**
	 * Get encoded value from $_GET
	 *
	 * @param string $key
	 * @param string|null $default Fallback value if key does not exist
	 * @param bool $raw Return raw unencoded value
	 * @return string|null
	 */
	public static function get(string $key, ?string $default = null, bool $raw = false): ?string
	{
		$value = $_GET[$key] ?? $default;
		if ($value === null) return null;
		return $raw ? $value : Str::value($value)->encode();
	}

	/**
	 * Get encoded value from $_POST
	 *
	 * @param string $key
	 * @param string|null $default Fallback value if key does not exist
	 * @param bool $raw Return raw unencoded value
	 * @return string|null
	 */
	public static function post(string $key, ?string $default = null, bool $raw = false): ?string
	{
		$value = $_POST[$key] ?? $default;
		if ($value === null) return null;
		return $raw ? $value : Str::value($value)->encode();
	}

	/**
	 * Get encoded value from $_GET or $_POST (GET takes priority)
	 *
	 * @param string $key
	 * @param string|null $default Fallback value if key does not exist
	 * @param bool $raw Return raw unencoded value
	 * @return string|null
	 */
	public static function request(string $key, ?string $default = null, bool $raw = false): ?string
	{
		$value = $_GET[$key] ?? $_POST[$key] ?? $default;
		if ($value === null) return null;
		return $raw ? $value : Str::value($value)->encode();
	}

	/**
	 * Get raw unencoded value from $_GET, useful for arrays e.g. $_GET['key'][]
	 *
	 * @param string $key
	 * @param mixed $default Fallback value if key does not exist
	 * @return mixed
	 */
	public static function getRaw(string $key, mixed $default = null): mixed
	{
		return $_GET[$key] ?? $default;
	}

	/**
	 * Get raw unencoded value from $_POST, useful for arrays e.g. $_POST['key'][]
	 *
	 * @param string $key
	 * @param mixed $default Fallback value if key does not exist
	 * @return mixed
	 */
	public static function postRaw(string $key, mixed $default = null): mixed
	{
		return $_POST[$key] ?? $default;
	}

	/**
	 * Get all raw input from $_GET and $_POST merged (POST takes priority)
	 *
	 * @return array<string, mixed>
	 */
	public static function all(): array
	{
		return array_merge($_GET, $_POST);
	}

	/**
	 * Get all encoded input from $_GET and $_POST merged (POST takes priority)
	 *
	 * @return array<string, string>
	 */
	public static function allEncoded(): array
	{
		return Arr::value($_GET)
			->merge($_POST)
			->walkRecursive(fn($value) => Str::value($value)->encode()->get())
			->toArray();
	}
}