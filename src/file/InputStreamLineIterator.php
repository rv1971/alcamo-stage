<?php

namespace alcamo\file;

use alcamo\exception\Unsupported;
use alcamo\iterator\IteratorCurrentTrait;

/**
 * @brief Iterator reading lines from a file pointer
 *
 * @date Last reviewed 2021-06-14
 */
class InputStreamLineIterator implements \Iterator
{
    use IteratorCurrentTrait;

    /// Whether to include the line delimiter into the result of next()
    public const INCLUDE_LINE_DELIMITER = 1;

    /// Whether to skip empty lines
    public const SKIP_EMPTY = 2;

    private $handle_; ///< file pointer
    private $flags_;  ///< int

    /**
     * @param $handle File pointer
     *
     * @param $flags Bitwise or of the above class constants
     */
    public function __construct($handle, ?int $flags = null)
    {
        $this->handle_ = $handle;
        $this->flags_ = (int)$flags;

        /** Call readline() to read the first item. */
        $this->currentKey_ = 1;
        $this->current_ = $this->readLine();
    }

    public function getFlags(): int
    {
        return $this->flags_;
    }

    public function rewind()
    {
        if ($this->currentKey_ > 1) {
            /** @throw alcamo::exception::Unsupported when attempting to
             *  rewind, except if the iterator is still at the beginning. */
            throw new Unsupported('rewind');
        }
    }

    public function next()
    {
        if (isset($this->current_)) {
            $this->currentKey_++;
            $this->current_ = $this->readLine();
        }
    }

    /**
     * @brief Read a line, if possible
     *
     * @return The line read, or `null` if eof or any other low-level error
     */
    protected function readLine(): ?string
    {
        $line = fgets($this->handle_);

        if ($line === false) {
            return null;
        } else {
            if (
                $this->flags_ & self::SKIP_EMPTY && rtrim($line, PHP_EOL) == ''
            ) {
                return $this->readLine();
            }

            if (!($this->flags_ & self::INCLUDE_LINE_DELIMITER)) {
                $line = rtrim($line, PHP_EOL);
            }

            return $line;
        }
    }
}
