# parse_pair(string $string): array
![Packagist Version](https://img.shields.io/packagist/v/hyqo/parse_pair?style=flat-square)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/hyqo/parse_pair?style=flat-square)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/hyqo/parse_pair/run-tests?style=flat-square)
## Why not parse_str?
Because `parse_str` works only with a URL query string format.

So `foo="bar"` will be parsed like
```text
array(1) {
  ["foo"]=>
  string(5) ""bar""
}
```
We have double-quoted value `"bar"`. Instead, you can use `parse_pair` and if value is a valid double-quoted it will be expanded

## Install

```sh
composer require hyqo/parse_pair
```

## Usage
```php
use function Hyqo\Parser\parse_pair;

[$key, $value] = parse_str('foo=bar');

echo $key; //foo
echo $bar; //bar
```

If string is valid, it will be parsed:

| string              | key   | value              |
|---------------------|-------|--------------------|
| `foo=`              | `foo` | null               | 
| `foo=""`            | `foo` | empty string       | 
| `foo=bar`           | `foo` | `bar`              | 
| `foo="bar"`         | `foo` | `bar`              | 
| `foo="\"bar\""`     | `foo` | `"bar"`            |
| `foo="\"bar"`       | `foo` | `"bar`             |
| `foo="multi\nline"` | `foo` | `multi`<br/>`line` |


If string is invalid, result will be `null`:

| string       |
|--------------|
| `foo="bar\"` | 
| `foo='bar\'` |
| `foo="bar""` |
| `foo='bar''` |
