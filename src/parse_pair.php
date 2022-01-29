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

        switch (true) {
            case isDoubleQuoted($value):
                if (null === $value = getDoubleQuotedValue($value)) {
                    return $error();
                }

                $value = stripcslashes($value);
                break;
            case isSingleQuoted($value):
                if (null === $value = getSingleQuotedValue($value)) {
                    return $error();
                }
                break;
            default:
                if (!preg_match(
                    '/^(?P<value>[^ ]+)$/',
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

    switch (true) {
        case isDoubleQuoted($value):
            if (null === getDoubleQuotedValue($value)) {
                $value = sprintf('"%s"', addcslashes($value, '"'));
            }
            break;
        case isSingleQuoted($value):
            if (null === getSingleQuotedValue($value)) {
                $value = sprintf("\'%s\'", addcslashes($value, "'"));
            }
            break;
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

    if (strpos($value, ' ') && !in_array($value[0], ['"', "'"])) {
        $value = sprintf('"%s"', $value);
    }

    return sprintf("%s=%s", $key, $value);
}

function isDoubleQuoted(string $string)
{
    return preg_match('/^".*"$/', $string);
}

function isSingleQuoted(string $string)
{
    return preg_match('/^\'.*\'$/', $string);
}

function getDoubleQuotedValue(string $string): ?string
{
    if (preg_match(
        '/^"(?P<value>(?:(?:\\\\\\\\)+"|(?<!\\\\)\\\\\"|\\\\[^"]|[^"\\\\])*)"$/',
        $string,
        $matches
    )) {
        return $matches['value'];
    }

    return null;
}

function getSingleQuotedValue(string $string): ?string
{
    if (preg_match(
        '/^\'(?P<value>(?:(?:\\\\\\\\)+\'|(?<!\\\\)\\\\\'|\\\\[^\']|[^\'\\\\])*)\'$/',
        $string,
        $matches
    )) {
        return $matches['value'];
    }

    return null;
}
