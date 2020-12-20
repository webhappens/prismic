<?php

namespace WebHappens\Prismic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use WebHappens\Prismic\Events\Test;
use WebHappens\Prismic\Events\Tags\TagCreated;
use WebHappens\Prismic\Events\Tags\TagDeleted;
use WebHappens\Prismic\Events\DocumentsUpdated;
use WebHappens\Prismic\Events\MasterRefChanged;
use WebHappens\Prismic\Events\Releases\ReleaseCreated;
use WebHappens\Prismic\Events\Releases\ReleaseDeleted;
use WebHappens\Prismic\Events\Releases\ReleaseUpdated;

class WebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $body = json_decode($request->getContent());

        if (data_get($body, 'secret') != config('prismic.webhook_secret')) {
            abort(403, 'Invalid secret');
        }

        if (data_get($body, 'type') == 'test-trigger') {
            event(new Test);
        }

        if ($ref = data_get($body, 'masterRef')) {
            event(new MasterRefChanged($ref));
        }

        // It's important to review the specific behaviour through this article
        // https://user-guides.prismic.io/en/articles/790505-webhooks

        // Releases
        foreach(data_get($body, 'releases.addition', []) as $release) {
            event(new ReleaseCreated((object) $release));
        }

        foreach(data_get($body, 'releases.update', []) as $release) {
            event(new ReleaseUpdated((object) $release));
        }

        foreach(data_get($body, 'releases.deletion', []) as $release) {
            event(new ReleaseDeleted((object) $release));
        }

        // Tags
        foreach(data_get($body, 'tags.addition', []) as $tag) {
            event(new TagCreated($tag->id));
        }

        foreach(data_get($body, 'tags.deletion', []) as $tag) {
            event(new TagDeleted($tag->id));
        }

        // Documents
        if ($documents = data_get($body,'documents', [])) {
            event(new DocumentsUpdated($ref, $documents));
        }

        return response(200);
    }
}
