<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeamMemberRequest;
use App\Http\Requests\Admin\UpdateTeamMemberRequest;
use App\Models\TeamMember;
use App\Services\TeamMemberManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TeamMemberController extends Controller
{
    public function __construct(private readonly TeamMemberManager $team) {}

    public function index(): View
    {
        Gate::authorize('viewAny', TeamMember::class);

        return view('admin.team.index', ['teamMembers' => $this->team->paginateAdmin()]);
    }

    public function create(): View
    {
        Gate::authorize('create', TeamMember::class);

        return view('admin.team.create', ['teamMember' => new TeamMember]);
    }

    public function store(StoreTeamMemberRequest $request): RedirectResponse
    {
        $this->team->create($request->validated());

        return to_route('admin.team.index')->with('status', 'Miembro agregado al equipo.');
    }

    public function edit(TeamMember $teamMember): View
    {
        Gate::authorize('update', $teamMember);

        return view('admin.team.edit', compact('teamMember'));
    }

    public function photo(TeamMember $teamMember): BinaryFileResponse
    {
        Gate::authorize('view', $teamMember);
        abort_unless($teamMember->photo && Storage::disk('local')->exists($teamMember->photo), 404);

        $mime = Storage::disk('local')->mimeType($teamMember->photo);
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

        return response()->file(Storage::disk('local')->path($teamMember->photo), [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember): RedirectResponse
    {
        $this->team->update($teamMember, $request->validated());

        return to_route('admin.team.index')->with('status', 'Miembro actualizado correctamente.');
    }

    public function toggle(TeamMember $teamMember): RedirectResponse
    {
        Gate::authorize('update', $teamMember);
        $this->team->setActive($teamMember, ! $teamMember->is_active);

        return to_route('admin.team.index')->with('status', 'Estado del miembro actualizado.');
    }

    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        Gate::authorize('delete', $teamMember);
        $this->team->delete($teamMember);

        return to_route('admin.team.index')->with('status', 'Miembro eliminado correctamente.');
    }
}
