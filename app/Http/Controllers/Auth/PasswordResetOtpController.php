<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestPasswordResetCodeRequest;
use App\Http\Requests\Auth\UpdatePasswordWithOtpRequest;
use App\Http\Requests\Auth\VerifyPasswordResetCodeRequest;
use App\Services\AuditLogger;
use App\Services\PasswordResetOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordResetOtpController extends Controller
{
    private const GENERIC_MESSAGE = 'Si la información proporcionada corresponde a una cuenta registrada, recibirás instrucciones para continuar.';

    public function __construct(
        private readonly PasswordResetOtpService $passwordReset,
        private readonly AuditLogger $audit,
    ) {}

    public function requestForm(): View
    {
        return view('auth.password-request');
    }

    public function requestCode(RequestPasswordResetCodeRequest $request): RedirectResponse
    {
        $email = mb_strtolower(trim($request->validated('email')));

        $request->session()->forget(['password_reset_email', 'password_reset_code_id']);
        $request->session()->put('password_reset_email', $email);
        $this->audit->record('password_reset_requested', request: $request);
        $this->passwordReset->requestCode($email);

        return to_route('password.otp.form')->with('status', self::GENERIC_MESSAGE);
    }

    public function verifyForm(): View|RedirectResponse
    {
        if (! session()->has('password_reset_email')) {
            return to_route('password.request');
        }

        return view('auth.password-otp');
    }

    public function verify(VerifyPasswordResetCodeRequest $request): RedirectResponse
    {
        $email = (string) $request->session()->get('password_reset_email', '');
        $codeId = $this->passwordReset->verify($email, $request->validated('code'));

        if ($codeId === null) {
            return to_route('password.otp.form')->withErrors(['code' => 'El código no es válido o ha expirado.']);
        }

        $request->session()->regenerate();
        $request->session()->put('password_reset_code_id', $codeId);

        return to_route('password.reset.form');
    }

    public function resend(): RedirectResponse
    {
        $email = (string) session('password_reset_email', '');

        if ($email !== '') {
            $this->audit->record('password_reset_requested');
            $this->passwordReset->requestCode($email);
        }

        return to_route('password.otp.form')->with('status', self::GENERIC_MESSAGE);
    }

    public function resetForm(): View|RedirectResponse
    {
        if (! session()->has('password_reset_email') || ! session()->has('password_reset_code_id')) {
            return to_route('password.otp.form');
        }

        return view('auth.password-reset');
    }

    public function updatePassword(UpdatePasswordWithOtpRequest $request): RedirectResponse
    {
        $completed = $this->passwordReset->resetPassword(
            (string) $request->session()->get('password_reset_email', ''),
            (int) $request->session()->get('password_reset_code_id', 0),
            $request->validated('password'),
        );

        $request->session()->forget(['password_reset_email', 'password_reset_code_id']);

        if (! $completed) {
            return to_route('password.request')->withErrors(['email' => 'El proceso expiró. Solicita un nuevo código para continuar.']);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('status', 'La contraseña se actualizó. Ya puedes iniciar sesión.');
    }
}
