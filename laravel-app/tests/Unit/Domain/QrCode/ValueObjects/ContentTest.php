<?php

namespace Tests\Unit\Domain\QrCode\ValueObjects;

use PHPUnit\Framework\TestCase;
use App\Domain\QrCode\ValueObjects\Content;
use App\Domain\QrCode\Exceptions\InvalidContentException;

class ContentTest extends TestCase
{
    public function test_can_create_content_with_valid_string()
    {
        $content = new Content('https://example.com');
        
        $this->assertEquals('https://example.com', $content->getValue());
        $this->assertEquals('https://example.com', (string) $content);
    }

    public function test_throws_exception_for_empty_content()
    {
        $this->expectException(InvalidContentException::class);
        new Content('');
    }

    public function test_throws_exception_for_content_too_long()
    {
        $this->expectException(InvalidContentException::class);
        new Content(str_repeat('a', 4297));
    }

    public function test_can_compare_content_objects()
    {
        $content1 = new Content('test');
        $content2 = new Content('test');
        $content3 = new Content('different');

        $this->assertTrue($content1->equals($content2));
        $this->assertFalse($content1->equals($content3));
    }
}