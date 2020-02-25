![tests](https://github.com/webhappens/prismic/workflows/tests/badge.svg)

# A Laravel wrapper for Prismic.io

This package makes it super quick and easy to work with Prismic.io in Laravel.

* Object-oriented design
* Eloquent-style query interface
* Auto-magically render Slices and Fields as HTML
* Automatic link resolution
* Nested Document support
* Out-the-box caching and preview sessions
* Super-charged web hooks (Coming soon)

## Documents
Each Custom Type within Prismic should have a corresponding Document model in your project. Document models make it easy for you to query your Prismic repository and organise your code around your content models.

### Defining Document Models
To get started, let's create a Document model. All Document models extend the `WebHappens\Prismic\Document` class.

The most basic and fundamental property of a Document model is its `type` property. This property indicates which Prismic Custom Type the Document model corresponds to:

```php
<?php

namespace App;

use WebHappens\Prismic\Document;

class Article extends Document
{
    /**
     * The 'API ID' of the Custom Type associated with the document model.
     *
     * @var string
     */
    protected static $type = 'article';
}
```

### Registering Document Models
Before Document models are available they must be registered in a Service Provider using the `Prismic::documents()` method. Typically this would happen in the `register` method of your `App\Providers\AppServiceProvider` class.

```php
use App\Article;
use WebHappens\Prismic\Prismic;

public function register()
{
    Prismic::documents([
        Article::class,
    ]);
}
```

### Retrieving Documets
Once you have created a Document model and its associated content in Prismic, you are ready to start retrieving data from your repository.

#### All Documents
You may retrieve all Documents for a specific type using the `all` method. They will be returned inside a Laravel Collection object:
```php
// Retrieve all Article Documents
$articles = Article::all();
```

#### Chunking Results
If you need to process a lot of Documents, use the `chunk` command. The `chunk` method will retrieve a "chunk" of Document models, feeding them to a given Closure for processing. This will conserve memory when working with large result sets:

```php
// Process each Article in chunks of 100
Article::chunk(100, function ($articles) {
    foreach ($articles as $article) {
        //
    }
});
```

Please note that the largest chunk size is 100 due to a limit on the Prismic API.

#### Single Documents

In addition to retrieving all Documents for a given type, you may also retrieve single records by passing the Document ID into the `find` method. Instead of returning a Laravel Collection, this method returns a single Document instance:

```php
// Retrive the Article with an ID of WAjgAygABN3B0a-a
$article = Article::find('WAjgAygABN3B0a-a');
```

#### Single Type Documents
If you have created a Single Type Document, a Homepage, for example, you may use the `single` method to retrieve the Document model instance, without having to pass a Document ID:

```php
// Retrieve a Single Type Document
$homepage = Homepage::single();
```
