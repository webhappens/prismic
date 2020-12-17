<?php

namespace WebHappens\Prismic\Tests;

use WebHappens\Prismic\FieldNameResolver;
use WebHappens\Prismic\Tests\TestCase;

class FieldNameResolverTest extends TestCase
{
    public function test_global_fields()
    {
        $resolver = new FieldNameResolver(null, ['id', 'lang']);

        $this->assertEquals('document.id', $resolver->resolve('id'));
        $this->assertEquals('document.id', $resolver->resolve('document.id'));
        $this->assertEquals('document.lang', $resolver->resolve('lang'));
    }

    public function test_custom_field_names()
    {
        $resolver = new FieldNameResolver(null, ['id', 'lang']);
        $this->assertEquals('my.article.foo', $resolver->resolve('article.foo'));
        $this->assertEquals('my.article.foo', $resolver->resolve('my.article.foo'));
        $this->assertEquals('my.article.foo', $resolver->type('article')->resolve('foo'));
        $this->assertEquals('my.article.foo', $resolver->type('article')->resolve('article.foo'));

        $resolver = new FieldNameResolver('article', ['id', 'lang']);
        $this->assertEquals('my.article.foo', $resolver->resolve('foo'));
        $this->assertEquals('my.article.foo', $resolver->resolve('article.foo'));
        $this->assertEquals('my.article.foo', $resolver->resolve('my.article.foo'));
    }

    public function test_nested_fields()
    {
        $resolver = new FieldNameResolver(null, ['id', 'lang']);
        $this->assertEquals('my.article.foo.bar', $resolver->resolve('article.foo.bar'));
        $this->assertEquals('my.article.foo.bar', $resolver->resolve('my.article.foo.bar'));
        $this->assertEquals('my.article.foo.bar', $resolver->type('article')->resolve('foo.bar'));
        $this->assertEquals('my.article.foo.bar', $resolver->type('article')->resolve('article.foo.bar'));

        $resolver = new FieldNameResolver('article', ['id', 'lang']);
        $this->assertEquals('my.article.foo.bar', $resolver->resolve('foo.bar'));
        $this->assertEquals('my.article.foo.bar', $resolver->resolve('article.foo.bar'));
        $this->assertEquals('my.article.foo.bar', $resolver->resolve('my.article.foo.bar'));
    }
}
