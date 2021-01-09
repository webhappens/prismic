<?php

namespace WebHappens\Prismic\Tests;

use WebHappens\Prismic\Fields\MediaLink;

class MediaLinkFieldTest extends LinkFieldTest
{
    public function test_make()
    {
        $link = $this->mediaLink();
        $this->assertInstanceOf(MediaLink::class, $link);
        $this->assertEquals('https://example.org', $link->getUrl());
        $this->assertEquals('Example Title', $link->getTitle());
    }

    public function test_make_with_meta()
    {
        $link = $this->mediaLink(['name' => 'document.pdf', 'size' => '150244']);
        $this->assertInstanceOf(MediaLink::class, $link);
        $this->assertEquals('https://example.org', $link->getUrl());
        $this->assertEquals('Example Title (PDF 147KB)', $link->getTitle());
    }

    public function test_get_file_name()
    {
        $link = $this->mediaLink(['name' => 'document.pdf']);
        $this->assertEquals('document.pdf', $link->getFileName());
    }

    public function test_get_file_extension()
    {
        $link = $this->mediaLink(['name' => 'document.pdf']);
        $this->assertEquals('pdf', $link->getFileExtension());
    }

    public function test_get_file_size()
    {
        $link = $this->mediaLink(['size' => '150244']);
        $this->assertEquals('150244', $link->getFileSize());
    }

    public function test_get_human_readable_file_size()
    {
        $link = $this->mediaLink(['size' => '1']);
        $this->assertEquals('1B', $link->getHumanReadableFileSize());

        $link = $this->mediaLink(['size' => '1024']);
        $this->assertEquals('1KB', $link->getHumanReadableFileSize());

        $link = $this->mediaLink(['size' => '1048576']);
        $this->assertEquals('1MB', $link->getHumanReadableFileSize());

        $link = $this->mediaLink(['size' => '1073741824']);
        $this->assertEquals('1GB', $link->getHumanReadableFileSize());

        $link = $this->mediaLink(['size' => '1099511627776']);
        $this->assertEquals('1TB', $link->getHumanReadableFileSize());
    }

    public function test_open_in_new_tab()
    {
        $this->assertCanOpenInNewTab($this->mediaLink());
    }

    public function test_attributes()
    {
        $this->assertCanSetAttributes($this->mediaLink());
    }

    public function test_to_string()
    {
        $this->assertCanToString($this->mediaLink());
    }

    public function test_to_html()
    {
        $this->assertCanToHtml(
            $this->mediaLink()->attributes(['class' => 'foo'])
        );
    }

    protected function mediaLink($meta = [])
    {
        return MediaLink::make('https://example.org', 'Example Title', $meta);
    }
}
