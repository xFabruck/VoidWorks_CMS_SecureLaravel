<?php

namespace App\Services;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactMessageManager
{
    public function __construct(private readonly MailConfigurationService $mailConfiguration) {}

    /** @param array<string, mixed> $data */
    public function receive(array $data, Request $request): ContactMessage
    {
        $contactMessage = new ContactMessage;
        $contactMessage->name = $data['name'];
        $contactMessage->email = $data['email'];
        $contactMessage->phone = $data['phone'] ?? null;
        $contactMessage->subject = $data['subject'];
        $contactMessage->message = $data['message'];
        $contactMessage->status = 'new';
        $contactMessage->ip_address = $request->ip();
        $contactMessage->user_agent = mb_substr((string) $request->userAgent(), 0, 1000);
        $contactMessage->save();

        $this->notifyAdmin($contactMessage);

        return $contactMessage;
    }

    public function paginateAdmin(): LengthAwarePaginator
    {
        return ContactMessage::query()->orderByDesc('created_at')->orderByDesc('id')->paginate(20);
    }

    public function markAsReadIfNew(ContactMessage $contactMessage): void
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->status = 'read';
            $contactMessage->save();
        }
    }

    public function updateStatus(ContactMessage $contactMessage, string $status): void
    {
        $contactMessage->status = $status;
        $contactMessage->save();
    }

    private function notifyAdmin(ContactMessage $contactMessage): void
    {
        $recipient = config('contact.notification_email');
        if (blank($recipient) || ! $this->mailConfiguration->configureForSending()) {
            return;
        }

        try {
            Mail::to($recipient)->send(new ContactMessageReceived($contactMessage));
        } catch (Throwable $exception) {
            // Keep the message safely stored even when the mail provider is temporarily unavailable.
            Log::warning('Contact message notification could not be sent.', [
                'contact_message_id' => $contactMessage->id,
                'exception' => $exception::class,
            ]);
        }
    }
}
