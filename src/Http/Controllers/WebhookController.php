<?php

namespace WebHappens\Prismic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use WebHappens\Prismic\Events\Test;
use WebHappens\Prismic\Events\MasterRefChanged;

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
    }
}
