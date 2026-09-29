<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Services\TeamMemberManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TeamController extends Controller
{
    public function __construct(private readonly TeamMemberManager $team) {}

    public function index(): View
    {
        return view('public.team.index', ['teamMembers' => $this->team->active()]);
    }

    public function photo(TeamMember $teamMember): BinaryFileResponse
    {
        abort_unless($teamMember->is_active && $teamMember->show_photo && $teamMember->photo, 404);
        abort_unless(Storage::disk('local')->exists($teamMember->photo), 404);

        $mime = Storage::disk('local')->mimeType($teamMember->photo);
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

        return response()->file(Storage::disk('local')->path($teamMember->photo), [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
