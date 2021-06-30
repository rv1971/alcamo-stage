<?php

namespace alcamo\integer;

use alcamo\exception\{OutOfRange, SyntaxError};

/**
 * @brief Range of nonnegative integers
 *
 * @invariant Immutable class.
 *
 * @date Last reviewed 2021-06-08
 */
class NonNegativeRange
{
    /**
     * @brief Create from string.
     *
     * Supports an empty string or the syntax `<min>[-[<max>]]`.
     */
    public static function newFromString(string $str): self
    {
        /** Surrounding whitespace is ignored. */
        $str = trim($str);

        /** The empty string represents the range [0,∞[. */
        if ($str == '') {
            return new static();
        }

        /** @throw alcamo::exception::SyntaxError if the input is
         *  syntactically wrong. */
        if (
            !preg_match(
                '/^(\d+)(\s*-\s*(\d+)?)?$/',
                $str,
                $matches,
                PREG_UNMATCHED_AS_NULL
            )
        ) {
            throw new SyntaxError($str, null, '; not a valid length range');
        }

        $min = intval($matches[1]);

        $max = isset($matches[3])
            ? intval($matches[3])
            : (isset($matches[2]) ? null : $min);

        return new static($min, $max);
    }

    private $min_; ///< Minimum (nonnegative integer)
    private $max_; ///< Maximum (nonnegative integer or null)

    /**
     * @param $min Minimum (nonnegative integer)
     *
     * @param $max Maximum (nonnegative integer or null)
     */
    public function __construct(?int $min = null, ?int $max = null)
    {
        /** @throw alcamo::exception::OutOfRange if $min is less than zero. */
        if ($min < 0) {
            throw new OutOfRange($min, 0);
        }

        /** @throw alcamo::exception::OutOfRange if $max is less than $min. */
        if (isset($max) && $max < $min) {
            throw new OutOfRange($max, $min);
        }

        $this->min_ = (int)$min;
        $this->max_ = $max;
    }

    public function getMin(): int
    {
        return $this->min_;
    }

    public function getMax(): ?int
    {
        return $this->max_;
    }

    public function __toString()
    {
        /** Return empty string for [0,∞[. */
        if (!$this->min_ && !isset($this->max_)) {
            return '';
        }

        /** Otherwise, return a number if the limits are equal. */
        if ($this->min_ === $this->max_) {
            return (string)$this->min_;
        }

        /** Otherwise, return `<min>-<max>`. */
        return "{$this->min_}-{$this->max_}";
    }

    /// Whether different from [0,∞[
    public function isDefined(): bool
    {
        return $this->min_ || isset($this->max_);
    }

    /// Whether the range is one exact value
    public function isExact(): bool
    {
        return $this->min_ === $this->max_;
    }

    /// Whether $value is contained in the defined range
    public function contains(int $value): bool
    {
        return $this->min_ <= $value
            && (!isset($this->max_) || $value <= $this->max_);
    }
}
