<?php

/**
 * @author Marcelo Jacobus <marcelo.jacobus@gmail.com>
 */
class String extends Object
{

    protected $_content;

    /**
     * @param string $string
     */
    public function __construct($string = '')
    {
        $this->_content = (string) $string;
    }

    /**
     * Append text to the string
     * @param string $string
     */
    public function append($string)
    {
        $this->_content .= $string;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_content;
    }

    /**
     * To Upper Case
     * @return String
     */
    public function toUpperCase()
    {
        return new String(mb_strtoupper($this, 'UTF-8'));
    }

    /**
     * To Lower Case
     * @return String
     */
    public function toLowerCase()
    {
        return new String(mb_strtolower($this, 'UTF-8'));
    }

    /**
     * String to parameter
     *
     * @param string separator
     * @return String
     */
    public function parameterize($separator = '-')
    {
        $forbiddenChars = "/[^$separator,a-z]/";

        return $this->toLowerCase()
            ->gsub('/\s+/', $separator)
            ->gsub($forbiddenChars, '');
    }

    /**
     * Replace string
     *
     * @pattern string regexp or string
     * @return String
     */
    public function gsub($pattern, $replacement)
    {
        $pattern = new String($pattern);

        if ($pattern->isRegexp()) {
            return new String(preg_replace($pattern, $replacement, $this));
        } else {
            return new String(str_replace($pattern, $replacement, $this));
        }
    }

    /**
     * Checks if string is a Regular Expression.
     *
     * If param is not given, the considered string is the object itself.
     *
     * @param string $string defaults to null
     * @return bool
     */
    protected function isRegexp($string = null)
    {
        if ($string === null) {
            $string = $this;
        }

        return preg_match('/\/.*\//', $string);
    }

    /**
     * Splits the string
     * @param string separator
     * @return Hash[String]
     */
    public function split($separator)
    {
        $hash = new Hash(explode((string) $separator, (string) $this));

        return $hash->map(
            function ($value) {
                return new String($value);
            }
        );
    }

    /**
     * Get the number of chars
     * @return int
     */
    public function count()
    {
        return mb_strlen((string) $this, 'UTF-8');
    }

    /**
     * Get the number of chars
     * @return int
     */
    public function length()
    {
        return $this->count();
    }

}
