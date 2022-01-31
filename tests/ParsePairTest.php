<?php

namespace Hyqo\Parser\Test;

use PHPUnit\Framework\TestCase;

use function Hyqo\Parser\build_pair;
use function Hyqo\Parser\parse_pair;

class ParsePairTest extends TestCase
{
    public function test_parse_pair(): void
    {
        $this->assertNull(parse_pair(''));
        $this->assertNull(parse_pair('foo'));
        $this->assertNull(parse_pair('foo = """'));
        $this->assertNull(parse_pair('foo="bar'));
        $this->assertNull(parse_pair('foo=\'bar\''));
        $this->assertEquals(['for', '192.0.2.43'], parse_pair('for=192.0.2.43'));
        $this->assertEquals(['foo', ''], parse_pair('foo='));
        $this->assertEquals(['foo', 'bar'], parse_pair('foo=bar'));
        $this->assertEquals(['foo', 'bar'], parse_pair('foo="bar"'));
        $this->assertEquals(['foo', ''], parse_pair('foo=""'));
        $this->assertEquals(['foo', '🐘'], parse_pair('foo=🐘'));
        $this->assertEquals(['foo', '🐘'], parse_pair('foo="🐘"'));
        $this->assertEquals(['foo', '"🐘'], parse_pair('foo="\"🐘"'));
        $this->assertEquals(['foo', "bar\nbaz"], parse_pair('foo="bar\nbaz"'));
        $this->assertEquals(['foo', "multi\nline"], parse_pair('foo="multi\nline"'));
        $this->assertEquals(['foo', "\n\t\r"], parse_pair('foo="\n\t\r"'));
        $this->assertNull(parse_pair('foo=\'\n\t\r\''));

        for ($i = 0; $i < 1000; $i++) {
            $string = sprintf('foo="%s"bar"', str_repeat('\\', $i));

            if ($i % 2 === 0) {
                $this->assertNull(parse_pair($string), $string);
            } else {
                $this->assertEquals(['foo', sprintf('%s"bar', str_repeat('\\', ($i - 1) / 2))], parse_pair($string), $string);
            }
        }
    }

    public function test_build_pair(): void
    {
        $this->assertEquals('foo=bar', build_pair('foo', 'bar'));
        $this->assertEquals('foo="bar baz"', build_pair('foo', 'bar baz'));
        $this->assertEquals('foo="bar\nbaz"', build_pair('foo', "bar\nbaz"));
        $this->assertEquals('foo="\"bar baz\""', build_pair('foo', '"bar baz"'));
        $this->assertEquals('foo="\'bar\nbaz\'"', build_pair('foo', "'bar\nbaz'"));
        $this->assertEquals('foo="\"bar\nbaz\""', build_pair('foo', "\"bar\nbaz\""));
        $this->assertEquals('foo="bar\"\nbaz"', build_pair('foo', "bar\"\nbaz"));
    }
}
