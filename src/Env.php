<?php

declare(strict_types=1);

namespace MaplePHP\Http;

use MaplePHP\DTO\Format;

class Env
{
	private static array $registry = []; // Static registry, survives reloads correctly
	private array $fileData = [];
	private array $data = [];
	private array $set = [];
	private array $drop = [];

	public function __construct(?string $file = null)
	{
		if ($file !== null && is_file($file)) {
			$this->loadEnvFile($file);
		}
	}

	public function loadEnvFile(string $file): void
	{
		if (!is_file($file)) {
			throw new \RuntimeException("Environment file not found: $file");
		}

		$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$cleaned = array_filter($lines, fn($line) => !str_starts_with(trim($line), '#'));
		$data = parse_ini_string(implode("\n", $cleaned), false, INI_SCANNER_RAW);

		if ($data === false) {
			throw new \RuntimeException(
				"Failed to parse .env file: $file\n" .
				"Hint: wrap values containing special characters (;, |, $) in double quotes."
			);
		}

		$this->fileData = $data;
	}

	public function hasEnv(string $key): ?string
	{
		$key = $this->formatKey($key);
		return isset($this->fileData[$key]) ? $key : null;
	}

	public function set(string $key, string $value): string
	{
		if ($keyB = $this->hasEnv($key)) {
			$this->fileData[$keyB] = $value;
		} else {
			$key = $this->formatKey($key);
			$this->set[$key] = $value;
		}
		return "{$key}={$value}";
	}

	public function get(string $key): string
	{
		$key = $this->formatKey($key);
		return self::$registry[$key] ?? "";
	}

	public function drop(string $key): void
	{
		$key = $this->formatKey($key);
		$this->drop[$key] = $key;
	}

	public function formatKey(string $key): string
	{
		return Format\Str::value($key)->clearBreaks()->trim()->normalizeAccents()
			->normalizeSeparators()->replaceSpaces("-")->toUpper()->get();
	}

	public function generateOutput(array $fromArr = ["data", "fileData", "set"]): string
	{
		$out = "";
		$data = [];
		$validData = ["data", "fileData", "set"];

		foreach ($validData as $d) {
			if (in_array($d, $fromArr)) {
				$data += $this->{$d};
			}
		}

		$length = count($data);
		foreach ($data as $key => $val) {
			if (empty($this->drop[$key])) {
				$key = $this->formatKey($key);
				$val = trim($val);
				if (!is_numeric($val) && ($val !== "true" && $val !== "false")) {
					$val = "'{$val}'";
				}
				$out .= "{$key}={$val}";
				if ($length > 1) {
					$out .= "\n";
				}
			}
		}
		return $out;
	}

	public function putenv(string $key, string $value): self
	{
		$this->data[$key] = $value;
		return $this;
	}

	public function putenvArray(array $array): self
	{
		foreach ($array as $prefix => $val) {
			$prefix = strtoupper($prefix);
			if (is_array($val)) {
				foreach ($val as $k1 => $v1) {
					foreach ($v1 as $k2 => $v2) {
						$newKey = strtoupper("{$k1}_{$k2}");
						if (!isset($this->fileData[$newKey])) {
							$this->data[$newKey] = $v2;
						}
					}
				}
			} else {
				$this->data[$prefix] = $val;
			}
		}
		$this->data = array_merge($this->data, $array);
		return $this;
	}

	public function execute(bool $overwrite = false): void
	{
		$merged = $this->getData();

		// Fully replace the registry on every execute — no bleed-through
		self::$registry = [];

		foreach ($merged as $key => $value) {
			if (isset($this->drop[$key])) {
				continue;
			}
			if (!$overwrite && isset(self::$registry[$key])) {
				continue;
			}
			self::$registry[$key] = $value;
		}
	}

	public static function getFromRegistry(string $key): mixed
	{
		return self::$registry[$key] ?? null;
	}

	public function getData(): array
	{
		return $this->data + $this->fileData + $this->set;
	}
}