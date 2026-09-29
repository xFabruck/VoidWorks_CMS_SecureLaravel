<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_contact_form_is_available_and_includes_csrf_protection(): void
    {
        $this->get(route('contact.create'))->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('name="privacy_accepted"', false)
            ->assertSee('name="phone"', false);
    }

    public function test_valid_submission_is_stored_with_request_metadata_and_sends_configured_smtp_notification(): void
    {
        Mail::fake();
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.example.test',
            'contact.notification_email' => 'cms@example.test',
        ]);

        $this->post(route('contact.store'), $this->validPayload([
            'name' => '  Ada Lovelace  ',
            'phone' => '+57 300 000 0000',
        ]))->assertRedirect(route('contact.create'))->assertSessionHas('status');

        $message = ContactMessage::query()->firstOrFail();
        $this->assertSame('Ada Lovelace', $message->name);
        $this->assertSame('ada@example.test', $message->email);
        $this->assertSame('+57 300 000 0000', $message->phone);
        $this->assertSame('new', $message->status);
        $this->assertNotNull($message->ip_address);
        $this->assertNotEmpty($message->user_agent);
        $this->assertNotNull($message->created_at);
        Mail::assertSent(ContactMessageReceived::class, fn (ContactMessageReceived $mail): bool => $mail->hasTo('cms@example.test'));
    }

    public function test_submission_is_rejected_without_valid_fields_and_privacy_acceptance(): void
    {
        $this->from(route('contact.create'))->post(route('contact.store'), $this->validPayload([
            'email' => 'invalid-email',
            'message' => 'short',
            'privacy_accepted' => '0',
        ]))->assertRedirect(route('contact.create'))
            ->assertSessionHasErrors(['email', 'message', 'privacy_accepted']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_notification_renders_message_content_as_escaped_text(): void
    {
        Mail::fake();
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.example.test',
            'contact.notification_email' => 'cms@example.test',
        ]);

        $this->post(route('contact.store'), $this->validPayload([
            'message' => '<script>alert("unsafe")</script>',
        ]))->assertRedirect(route('contact.create'));

        Mail::assertSent(ContactMessageReceived::class, function (ContactMessageReceived $mail): bool {
            $rendered = $mail->render();

            return str_contains($rendered, '&lt;script&gt;alert(&quot;unsafe&quot;)&lt;/script&gt;')
                && ! str_contains($rendered, '<script>alert("unsafe")</script>');
        });
    }

    public function test_contact_submission_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('contact.store'), $this->validPayload())->assertRedirect(route('contact.create'));
        }

        $this->post(route('contact.store'), $this->validPayload())->assertTooManyRequests();
        $this->assertDatabaseCount('contact_messages', 5);
    }

    public function test_honeypot_submission_returns_generic_success_without_storing_message(): void
    {
        $this->post(route('contact.store'), $this->validPayload(['website' => 'spam']))
            ->assertRedirect(route('contact.create'))->assertSessionHas('status');

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_admin_contact_messages_require_authentication(): void
    {
        $message = $this->createMessage();

        $this->get(route('admin.contact-messages.index'))->assertRedirect(route('login'));
        $this->get(route('admin.contact-messages.show', $message))->assertRedirect(route('login'));
        $this->patch(route('admin.contact-messages.update', $message), ['status' => 'answered'])->assertRedirect(route('login'));
    }

    public function test_admin_can_view_update_status_and_message_content_is_escaped(): void
    {
        $message = $this->createMessage([
            'name' => '<img src=x onerror=alert(1)>',
            'subject' => '<script>alert(1)</script>',
            'message' => '<script>alert("message")</script>',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.contact-messages.index'))->assertOk()->assertSee('&lt;img src=x onerror=alert(1)&gt;', false);

        $this->get(route('admin.contact-messages.show', $message))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;script&gt;alert(&quot;message&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("message")</script>', false);

        $this->assertDatabaseHas('contact_messages', ['id' => $message->id, 'status' => 'read']);
        $this->patch(route('admin.contact-messages.update', $message), ['status' => 'answered'])
            ->assertRedirect(route('admin.contact-messages.show', $message))->assertSessionHas('status');
        $this->assertDatabaseHas('contact_messages', ['id' => $message->id, 'status' => 'answered']);
    }

    public function test_admin_cannot_set_an_unknown_message_status(): void
    {
        $message = $this->createMessage();
        $this->actingAs(User::factory()->create())->from(route('admin.contact-messages.show', $message))
            ->patch(route('admin.contact-messages.update', $message), ['status' => 'deleted'])
            ->assertRedirect(route('admin.contact-messages.show', $message))->assertSessionHasErrors('status');
        $this->assertDatabaseHas('contact_messages', ['id' => $message->id, 'status' => 'new']);
    }

    /** @return array<string, string> */
    private function validPayload(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.test',
            'phone' => '',
            'subject' => 'Project inquiry',
            'message' => 'I would like to talk about a project.',
            'privacy_accepted' => '1',
        ], $overrides);
    }

    private function createMessage(array $overrides = []): ContactMessage
    {
        $message = new ContactMessage;
        $message->name = 'Grace Hopper';
        $message->email = 'grace@example.test';
        $message->phone = null;
        $message->subject = 'A question';
        $message->message = 'A contact request for the studio.';
        $message->status = 'new';
        $message->ip_address = '127.0.0.1';
        $message->user_agent = 'Feature test';
        foreach ($overrides as $field => $value) {
            $message->{$field} = $value;
        }
        $message->save();

        return $message;
    }
}
