<?php

namespace WebHappens\Prismic\Fields;

class WebLink extends Link
{
    public function getUrl(): ?string
    {
        if ($fragment = self::matchWhenFragmentOnly(parent::getUrl())) {
            return $fragment;
        }

        return parent::getUrl();
    }

    protected static function matchWhenFragmentOnly($url): ?string
    {
        if (preg_match('@https?://(#[a-z0-9\-]+)@', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
