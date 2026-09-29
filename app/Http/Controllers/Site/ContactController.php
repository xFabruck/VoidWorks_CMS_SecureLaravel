<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Services\ContactMessageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('public.contact');
    }

    public function store(StoreContactMessageRequest $request, ContactMessageManager $messages): RedirectResponse
    {
        if ($request->filled('website')) {
            return to_route('contact.create')->with('status', 'Tu mensaje fue recibido. Nos pondremos en contacto contigo.');
        }

        $messages->receive($request->validated(), $request);

        return to_route('contact.create')->with('status', 'Tu mensaje fue recibido. Nos pondremos en contacto contigo.');
    }
}
