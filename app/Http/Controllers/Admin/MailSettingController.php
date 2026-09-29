<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MailSettingRequest;
use App\Services\AuditLogger;
use App\Services\MailConfigurationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MailSettingController extends Controller
{
    public function __construct(
        private readonly MailConfigurationService $mailConfiguration,
        private readonly AuditLogger $audit,
    ) {}

    public function edit(): View
    {
        $setting = $this->mailConfiguration->current();
        Gate::authorize('view', $setting);

        return view('admin.settings.mail', [
            'setting' => $setting,
            'hasPassword' => $this->mailConfiguration->hasStoredPassword($setting),
        ]);
    }

    public function update(MailSettingRequest $request): RedirectResponse
    {
        $setting = $this->mailConfiguration->update($request->validated(), (int) $request->user()->getKey());
        $this->audit->record('smtp_updated', $setting, request: $request);

        return to_route('admin.settings.mail.edit')->with('status', 'Configuración de correo guardada.');
    }

    public function sendTest(MailSettingRequest $request): RedirectResponse
    {
        $sent = $this->mailConfiguration->sendTest(
            $request->validated('test_recipient'),
            (int) $request->user()->getKey(),
        );

        return to_route('admin.settings.mail.edit')->with(
            $sent ? 'status' : 'error',
            $sent ? 'Correo de prueba enviado correctamente.' : 'No fue posible enviar el correo de prueba. Revisa la configuración o el servicio de correo.',
        );
    }
}
