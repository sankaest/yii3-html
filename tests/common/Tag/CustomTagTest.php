<?php

declare(strict_types=1);

namespace Yiisoft\Html\Tests\Tag;

use PHPUnit\Framework\TestCase;
use Stringable;
use Yiisoft\Html\Tag\CustomTag;
use Yiisoft\Html\Tag\P;
use Yiisoft\Html\Tag\Span;
use Yiisoft\Html\Tests\Objects\StringableObject;

use function is_array;

final class CustomTagTest extends TestCase
{
    public function testBase(): void
    {
        $this->assertSame(
            '<test id="custom" count="15">body</test>',
            CustomTag::name('test')
                ->id('custom')
                ->attribute('count', 15)
                ->content('body')
                ->render()
        );
    }

    public function dataVoidTags(): array
    {
        return [
            ['area'],
            ['AREA'],
            ['br'],
            ['hr'],
        ];
    }

    /**
     * @dataProvider dataVoidTags
     *
     * @psalm-param non-empty-string $name
     */
    public function testVoidTags(string $name): void
    {
        $this->assertSame(
            "<$name>",
            CustomTag::name($name)->render()
        );
    }

    public function dataNormal(): array
    {
        return [
            ['<h1></h1>', 'h1'],
            ['<col></col>', 'col'],
        ];
    }

    /**
     * @dataProvider dataNormal
     *
     * @psalm-param non-empty-string $name
     */
    public function testNormal(string $expected, string $name): void
    {
        $this->assertSame($expected, CustomTag::name($name)
            ->normal()
            ->render());
    }

    public function dataVoid(): array
    {
        return [
            ['<h1>', 'h1'],
            ['<col>', 'col'],
        ];
    }

    /**
     * @dataProvider dataVoid
     *
     * @psalm-param non-empty-string $name
     */
    public function testVoid(string $expected, string $name): void
    {
        $this->assertSame($expected, CustomTag::name($name)
            ->void()
            ->render());
    }

    public function testWithoutEncode(): void
    {
        $this->assertSame(
            '<test><b>hello</b></test>',
            (string)CustomTag::name('test')
                ->content('<b>hello</b>')
                ->encode(false)
        );
    }

    public function testWithoutDoubleEncode(): void
    {
        $this->assertSame(
            '<test>&lt;b&gt;A &gt; B&lt;/b&gt;</test>',
            (string)CustomTag::name('test')
                ->content('<b>A &gt; B</b>')
                ->doubleEncode(false)
        );
    }

    public function dataContent(): array
    {
        return [
            'string' => ['<test>hello</test>', 'hello'],
            'string-tag' => ['<test>&lt;p&gt;Hi!&lt;/p&gt;</test>', '<p>Hi!</p>'],
            'object-tag' => ['<test><p>Hi!</p></test>', P::tag()->content('Hi!')],
            'array' => [
                '<test>Hello &gt; <span>World</span>!</test>',
                ['Hello', ' > ', Span::tag()->content('World'), '!'],
            ],
        ];
    }

    /**
     * @dataProvider dataContent
     *
     * @param string|string[]|Stringable|Stringable[] $content
     */
    public function testContent(string $expected, $content): void
    {
        $tag = CustomTag::name('test');
        $tag = is_array($content) ? $tag->content(...$content) : $tag->content($content);

        $this->assertSame($expected, $tag->render());
    }

    public function testEncodeContent(): void
    {
        $this->assertSame(
            '<test>&lt;p&gt;Hi!&lt;/p&gt;</test>',
            CustomTag::name('test')
                ->encode(true)
                ->content(P::tag()->content('Hi!'))
                ->render()
        );
    }

    public function testAddContent(): void
    {
        $this->assertSame(
            '<test>Hello World</test>',
            CustomTag::name('test')
                ->content('Hello')
                ->addContent(' ')
                ->addContent(new StringableObject('World'))
                ->render()
        );
    }

    public function testAddContentVariadic(): void
    {
        $this->assertSame(
            '<test>123</test>',
            CustomTag::name('test')
                ->content('1')
                ->addContent(...['2', '3'])
                ->render()
        );
    }

    public function testOpen(): void
    {
        $this->assertSame(
            '<test id="main">',
            CustomTag::name('test')
                ->id('main')
                ->open(),
        );
    }

    public function testClose(): void
    {
        $this->assertSame(
            '</test>',
            CustomTag::name('test')
                ->id('main')
                ->close(),
        );
    }

    public function testImmutability(): void
    {
        $tag = CustomTag::name('test');
        $this->assertNotSame($tag, $tag->normal());
        $this->assertNotSame($tag, $tag->void());
        $this->assertNotSame($tag, $tag->encode(true));
        $this->assertNotSame($tag, $tag->doubleEncode(true));
        $this->assertNotSame($tag, $tag->content(''));
        $this->assertNotSame($tag, $tag->addContent(''));
    }
}
