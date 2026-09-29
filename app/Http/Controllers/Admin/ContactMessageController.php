<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContactMessageRequest;
use App\Models\ContactMessage;
use App\Services\ContactMessageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function __construct(private readonly ContactMessageManager $messages) {}

    public function index(): View
    {
        Gate::authorize('viewAny', ContactMessage::class);

        return view('admin.contact-messages.index', ['messages' => $this->messages->paginateAdmin()]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        Gate::authorize('view', $contactMessage);
        $this->messages->markAsReadIfNew($contactMessage);

        return view('admin.contact-messages.show', compact('contactMessage'));
    }

    public function update(UpdateContactMessageRequest $request, ContactMessage $contactMessage): RedirectResponse
    {
        Gate::authorize('update', $contactMessage);
        $this->messages->updateStatus($contactMessage, $request->validated('status'));

        return to_route('admin.contact-messages.show', $contactMessage)->with('status', 'Estado del mensaje actualizado.');
    }
}
