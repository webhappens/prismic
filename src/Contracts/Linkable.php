<?php

namespace WebHappens\Prismic\Contracts;

interface Linkable
{
    public function getUrl(): ?string;
    public function getTitle(): ?string;
}
