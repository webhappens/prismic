<?php

namespace WebHappens\Prismic\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use WebHappens\Prismic\Tests\TestCase;
use WebHappens\Prismic\Events\Tags\TagCreated;
use WebHappens\Prismic\Events\Tags\TagDeleted;
use WebHappens\Prismic\Events\DocumentsUpdated;
use WebHappens\Prismic\Events\MasterRefChanged;
use WebHappens\Prismic\Events\Releases\ReleaseCreated;
use WebHappens\Prismic\Events\Releases\ReleaseDeleted;
use WebHappens\Prismic\Events\Releases\ReleaseUpdated;

class WebhookTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        Config::set('prismic.webhook_secret', 'SHAREDSECRET');
    }

    public function test_protected_by_secret()
    {
        $response = $this->postJson('prismic-webhook', json_decode('{
            "type": "api-update",
            "secret": null
        }', true));

        $response->assertForbidden();

        $response = $this->postJson('prismic-webhook', json_decode('{
            "type": "api-update"
        }', true));

        $response->assertForbidden();

        Event::assertNotDispatched(MasterRefChanged::class);
        Event::assertNotDispatched(DocumentsUpdated::class);
        Event::assertNotDispatched(ReleaseCreated::class);
        Event::assertNotDispatched(ReleaseDeleted::class);
        Event::assertNotDispatched(ReleaseUpdated::class);
        Event::assertNotDispatched(TagCreated::class);
        Event::assertNotDispatched(TagDeleted::class);
    }

    public function test_document_published()
    {
        $response = $this->postJson('prismic-webhook', json_decode('{
            "type": "api-update",
            "masterRef": "X6qn-RIAACMAVge1",
            "documents": [
                "X6LcjhAAAB8AUJwb"
            ],
            "secret": "SHAREDSECRET"
        }', true));

        $response->assertOk();
        Event::assertDispatched(MasterRefChanged::class);
        Event::assertDispatched(DocumentsUpdated::class);
        Event::assertNotDispatched(ReleaseCreated::class);
        Event::assertNotDispatched(ReleaseDeleted::class);
        Event::assertNotDispatched(ReleaseUpdated::class);
        Event::assertNotDispatched(TagCreated::class);
        Event::assertNotDispatched(TagDeleted::class);
    }

    public function test_release_addition()
    {
        $response = $this->postJson('prismic-webhook', json_decode('{
            "type": "api-update",
            "releases": {
                "addition": [
                    {
                        "id": "X66oBhIAACMAaDeo",
                        "ref": "X66oBhIAABMAaDep~X66c6xIAACEAZ8LK",
                        "label": "release4",
                        "documents": []
                    }
                ]
            },
            "secret": "SHAREDSECRET"
        }', true));

        $response->assertOk();
        Event::assertNotDispatched(MasterRefChanged::class);
        Event::assertNotDispatched(DocumentsUpdated::class);
        Event::assertDispatched(ReleaseCreated::class);
        Event::assertNotDispatched(ReleaseDeleted::class);
        Event::assertNotDispatched(ReleaseUpdated::class);
        Event::assertNotDispatched(TagCreated::class);
        Event::assertNotDispatched(TagDeleted::class);
    }

    public function test_release_update()
    {
        $response = $this->postJson('prismic-webhook', json_decode('{
            "type": "api-update",
            "releases": {
                "update": [
                    {
                        "id": "X6qq1BIAACEAVg1H",
                        "ref": "X6qq1BIAACUAVg1J~X6qqwBIAACEAVg0i",
                        "label": "release2",
                        "documents": [
                            "X6LcRBAAACIAUJqp"
                        ]
                    }
                ]
            },
            "secret": "SHAREDSECRET"
        }', true));

        $response->assertOk();
        Event::assertNotDispatched(MasterRefChanged::class);
        Event::assertNotDispatched(DocumentsUpdated::class);
        Event::assertNotDispatched(ReleaseCreated::class);
        Event::assertNotDispatched(ReleaseDeleted::class);
        Event::assertDispatched(ReleaseUpdated::class);
        Event::assertNotDispatched(TagCreated::class);
        Event::assertNotDispatched(TagDeleted::class);
    }

    public function test_release_deletion()
    {
        $response = $this->postJson('prismic-webhook', json_decode('{
            "type": "api-update",
            "masterRef": "XaBPIxEAABkAe5n8",
            "releases": {
                "deletion": [
                    {
                        "id": "XZ9MwBEAABkAdylL",
                        "ref": "XZ9NKBEAAA8Adysd~XaBN7BEAABkAe5Sg",
                        "label": "test",
                        "scheduledAt": 1572523200000,
                        "documents": [
                            "X6Lb_xAAACEAUJlg",
                            "X6LcRBAAACIAUJqp"
                        ]
                    }
                ]
            },
            "secret": "SHAREDSECRET"
        }', true));

        $response->assertOk();
        Event::assertDispatched(MasterRefChanged::class);
        Event::assertNotDispatched(DocumentsUpdated::class);
        Event::assertNotDispatched(ReleaseCreated::class);
        Event::assertDispatched(ReleaseDeleted::class);
        Event::assertNotDispatched(ReleaseUpdated::class);
        Event::assertNotDispatched(TagCreated::class);
        Event::assertNotDispatched(TagDeleted::class);
    }

    public function test_release_deletion_with_document_updates()
    {
        $response = $this->postJson('prismic-webhook', json_decode('{
            "type": "api-update",
            "masterRef": "X5BeBhAAACMAUaLE",
            "releases": {
                "deletion": [
                    {
                        "id": "X47-hhAAACEAS5RD",
                        "ref": "X5Bd1RAAABAAUaHd~X5BdyhAAACIAUaGs",
                        "label": "release name",
                        "documents": [
                            "X3b_6xIAACEA-YYE",
                            "X47-ThAAACAAS5NV"
                        ]
                    }
                ]
            },
            "documents": [
                "X3b_6xIAACEA-YYE",
                "X47-ThAAACAAS5NV"
            ],
            "secret": "SHAREDSECRET"
        }', true));

        $response->assertOk();
        Event::assertDispatched(MasterRefChanged::class);
        Event::assertDispatched(DocumentsUpdated::class);
        Event::assertNotDispatched(ReleaseCreated::class);
        Event::assertDispatched(ReleaseDeleted::class);
        Event::assertNotDispatched(ReleaseUpdated::class);
        Event::assertNotDispatched(TagCreated::class);
        Event::assertNotDispatched(TagDeleted::class);
    }

    public function test_tag_added()
    {
        $response = $this->postJson('prismic-webhook', json_decode('{
            "type": "api-update",
            "masterRef": "X6A5sxIAACkAvjzh",
            "tags": {
                "addition": [
                    {
                        "id": "testtag"
                    }
                ]
            },
            "documents": [
                "XxhMhBMAACAAWMp6"
            ],
            "secret": "SHAREDSECRET"
        }', true));

        $response->assertOk();
        Event::assertDispatched(MasterRefChanged::class);
        Event::assertDispatched(DocumentsUpdated::class);
        Event::assertNotDispatched(ReleaseCreated::class);
        Event::assertNotDispatched(ReleaseDeleted::class);
        Event::assertNotDispatched(ReleaseUpdated::class);
        Event::assertDispatched(TagCreated::class);
        Event::assertNotDispatched(TagDeleted::class);
    }

    public function test_tag_deleted()
    {
        $response = $this->postJson('prismic-webhook', json_decode('{
            "type": "api-update",
            "masterRef": "X6A8CRIAACcAvkbK",
            "tags": {
                "deletion": [
                    {
                        "id": "testtag"
                    }
                ]
            },
            "documents": [
                "XxhMhBMAACAAWMp6"
            ],
            "secret": "SHAREDSECRET"
        }', true));

        $response->assertOk();
        Event::assertDispatched(MasterRefChanged::class);
        Event::assertDispatched(DocumentsUpdated::class);
        Event::assertNotDispatched(ReleaseCreated::class);
        Event::assertNotDispatched(ReleaseDeleted::class);
        Event::assertNotDispatched(ReleaseUpdated::class);
        Event::assertNotDispatched(TagCreated::class);
        Event::assertDispatched(TagDeleted::class);
    }
}
