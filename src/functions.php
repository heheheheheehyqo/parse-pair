<?php

namespace Hyqo\Parser;

function parse_pair(string $string, bool $throwOnError = false): ?array
{
    $error = static function () use ($throwOnError, $string) {
        if ($throwOnError) {
            throw new \InvalidArgumentException(sprintf('Invalid string: %s', $string));
        }

        return null;
    };

    if (!preg_match('/^ *(?P<key>[\w-]+) ?= *(?P<value>.*) *$/', trim($string), $matches)) {
        return $error();
    }

    $key = $matches['key'];

    if (!$matches['value']) {
        $value = '';
    } else {
        $value = $matches['value'];

        if (isQuoted($value)) {
            if (null === $value = getQuotedValue($value)) {
                return $error();
            }

            $value = stripcslashes($value);
        } else {
            if (!preg_match(
                '/^(?P<value>[^\s"\']*)$/',
                $value,
                $matches
            )) {
                return $error();
            }

            $value = $matches['value'];
        }
    }

    return [$key, $value];
}

function build_pair(?string $key, ?string $value)
{
    if (!preg_match('/^[\w]+$/', $key)) {
        throw new \InvalidArgumentException('Key must contains only a-zA-Z0-9_');
    }

    if (preg_match('/[\r\n\t\f\v]/', $value)) {
        $whitespaces = [
            "/\r/" => '\r',
            "/\n/" => '\n',
            "/\t/" => '\t',
            "/\f/" => '\f',
            "/\v/" => '\v',
        ];

        $value = preg_replace(array_keys($whitespaces), array_values($whitespaces), $value);
    }

    if (preg_match('/[\W]+/', $value)) {
        $value = sprintf('"%s"', addcslashes($value, '"'));
    }

    return sprintf("%s=%s", $key, $value);
}

function isQuoted(string $string)
{
    return preg_match('/^".*"$/', $string);
}

function getQuotedValue(string $string): ?string
{
    if (preg_match(
        '/^"(?P<value>(?:(?:\\\\\\\\)+\\\\"|(?<!\\\\)\\\\"|\\\\[^"]|[^"\\\\])*)"$/',
        $string,
        $matches
    )) {
        return $matches['value'];
    }

    return null;
}
