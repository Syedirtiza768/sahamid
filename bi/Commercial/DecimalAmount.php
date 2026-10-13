<?php

namespace SAHamid\BI\Commercial;

/**
 * Fixed two-decimal money arithmetic for report totals.
 * Source calculations are rounded once per document by the valuation policy.
 */
class DecimalAmount
{
	public static function normalize($value)
	{
		if ($value === null || !preg_match('/^([+-]?)(\d+)(?:\.(\d*))?$/', trim((string) $value), $matches)) {
			throw new \InvalidArgumentException('A decimal amount is required.');
		}
		$sign = isset($matches[1]) && $matches[1] === '-' ? '-' : '';
		$whole = ltrim($matches[2], '0');
		if ($whole === '') $whole = '0';
		$fraction = isset($matches[3]) ? $matches[3] : '';
		$cents = substr(str_pad($fraction, 2, '0'), 0, 2);
		if (isset($fraction[2]) && $fraction[2] >= '5') {
			$minor = self::toMinorUnits($sign . $whole . '.' . $cents);
			$minor += ($sign === '-' ? -1 : 1);
			return self::fromMinorUnits($minor);
		}
		return ($sign === '-' && ($whole !== '0' || $cents !== '00') ? '-' : '') . $whole . '.' . $cents;
	}

	public static function add($left, $right)
	{
		return self::fromMinorUnits(self::toMinorUnits($left) + self::toMinorUnits($right));
	}

	public static function subtract($left, $right)
	{
		return self::fromMinorUnits(self::toMinorUnits($left) - self::toMinorUnits($right));
	}

	public static function compare($left, $right)
	{
		$a = self::toMinorUnits($left);
		$b = self::toMinorUnits($right);
		return $a === $b ? 0 : ($a < $b ? -1 : 1);
	}

	public static function divideInclusiveTax($gross, $taxPercent)
	{
		$minor = self::toMinorUnits($gross);
		$denominator = 100 + (int) $taxPercent;
		if ($denominator <= 0 || abs($minor) > intdiv(PHP_INT_MAX, 100)) {
			throw new \OutOfRangeException('The amount is outside the supported report range.');
		}
		$numerator = $minor * 100;
		$rounded = intdiv($numerator + ($numerator >= 0 ? intdiv($denominator, 2) : -intdiv($denominator, 2)), $denominator);
		return self::fromMinorUnits($rounded);
	}

	public static function sum(array $values)
	{
		$total = 0;
		foreach ($values as $value) $total += self::toMinorUnits($value);
		return self::fromMinorUnits($total);
	}

	private static function toMinorUnits($value)
	{
		$normalized = self::normalizeWithoutRounding($value);
		$negative = isset($normalized[0]) && $normalized[0] === '-';
		if ($negative) $normalized = substr($normalized, 1);
		$parts = explode('.', $normalized, 2);
		$whole = ltrim($parts[0], '0');
		if ($whole === '') $whole = '0';
		if (strlen($whole) > 14) throw new \OutOfRangeException('The amount is outside the supported report range.');
		$minor = ((int) $whole * 100) + (int) $parts[1];
		return $negative ? -$minor : $minor;
	}

	private static function normalizeWithoutRounding($value)
	{
		if ($value === null || !preg_match('/^([+-]?)(\d+)(?:\.(\d*))?$/', trim((string) $value), $matches)) {
			throw new \InvalidArgumentException('A decimal amount is required.');
		}
		$sign = isset($matches[1]) && $matches[1] === '-' ? '-' : '';
		$whole = ltrim($matches[2], '0');
		if ($whole === '') $whole = '0';
		$fraction = isset($matches[3]) ? $matches[3] : '';
		return $sign . $whole . '.' . substr(str_pad($fraction, 2, '0'), 0, 2);
	}

	private static function fromMinorUnits($minor)
	{
		$negative = $minor < 0;
		$minor = abs($minor);
		$whole = (string) intdiv($minor, 100);
		$cents = str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
		return ($negative && $minor !== 0 ? '-' : '') . $whole . '.' . $cents;
	}
}
