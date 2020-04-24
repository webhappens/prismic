<?php

namespace WebHappens\Prismic\Fields;

class MediaLink extends Link
{
    protected $meta;

    public function __construct($url, $title = null, $meta = [])
    {
        parent::__construct($url, $title);

        $this->meta = $meta;
    }

    public function getTitle(): ?string
    {
        if (! $title = parent::getTitle()) {
            $title = data_get(pathinfo($this->getFileName()), 'filename');
        }

        if ($this->meta) {
            return $title.sprintf(' (%s %s)', strtoupper($this->getFileExtension()), $this->getHumanReadableFileSize());
        }

        return $title;
    }

    public function getFileName(): ?string
    {
        return data_get($this->meta, 'name');
    }

    public function getFileExtension(): ?string
    {
        return data_get(pathinfo($this->getFileName()), 'extension');
    }

    public function getFileSize(): ?string
    {
        return data_get($this->meta, 'size');
    }

    public function getHumanReadableFileSize($decimals = 0): ?string
    {
        if (! $bytes = $this->getFileSize()) {
            return null;
        }

        $size = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor(log($bytes) / log(1024));

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).@$size[$factor];
    }
}
