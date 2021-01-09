<?php

namespace WebHappens\Prismic\Fields;

class WebLink extends Link
{
    public function getUrl(): ?string
    {
        if ($fragment = self::matchWhenFragmentOnly(parent::getUrl())) {
            return $fragment;
        }

        return self::replaceAbsolute(self::replaceRelative(parent::getUrl()));
    }

    protected static function matchWhenFragmentOnly($url): ?string
    {
        if (preg_match('@https?://(#[a-z0-9\-]+)@', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected static function replaceAbsolute($url): ?string
    {
        return preg_replace('@^https?:///@', '/', $url);
    }

    protected static function replaceRelative($url): ?string
    {
        return preg_replace('@^https?://\./@', '', $url);
    }
}
