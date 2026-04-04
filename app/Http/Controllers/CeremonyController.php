<?php

namespace App\Http\Controllers;

use App\Models\Ceremony;
use App\Models\Couple;
use App\Models\OfficiantDraft;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CeremonyController extends Controller
{
    /**
     * GET /ceremonie — Page principale de la cérémonie.
     *
     * Accessible aux époux et à l'officiant du couple.
     * Redirige vers le dashboard si le user n'a pas de couple ou si la cérémonie n'existe pas.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $couple = $request->user()->couple()->with(['spouse1', 'spouse2'])->first();

        if (! $couple) {
            // Chercher si l'user est officiant d'un couple
            $officiantDraft = OfficiantDraft::where('user_id', $request->user()->id)->first();
            if (! $officiantDraft) {
                return redirect()->route('dashboard');
            }
            $couple = $officiantDraft->couple()->with(['spouse1', 'spouse2'])->first();
        }

        $ceremony = $couple->ceremony()->first();

        if (! $ceremony) {
            return redirect()->route('dashboard');
        }

        Gate::authorize('view', $ceremony);

        // Déterminer le rôle : époux ou officiant
        $isSpouse = $request->user()->couple_id === $couple->id;
        $role = $isSpouse ? 'spouse' : 'officiant';

        return Inertia::render('Ceremony/Show', [
            'ceremony' => $ceremony,
            'couple'   => $couple,
            'role'     => $role,
        ]);
    }

    /**
     * POST /ceremonie/blocks — Ajouter un bloc au programme.
     *
     * Réservé aux époux du couple.
     */
    public function addBlock(Request $request): RedirectResponse
    {
        $couple = $this->resolveCouple($request);
        $ceremony = $this->resolveCeremony($couple);

        Gate::authorize('updateBlocks', $ceremony);

        $validated = $request->validate([
            'type'             => ['required', 'string', 'in:entrance,welcome,reading,vows,exchange,speech,music,moment,exit,custom'],
            'title'            => ['required', 'string', 'max:200'],
            'time'             => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:480'],
            'description'      => ['nullable', 'string', 'max:1000'],
        ]);

        $bloc = [
            'id'               => Str::uuid()->toString(),
            'type'             => $validated['type'],
            'title'            => $validated['title'],
            'time'             => $validated['time'] ?? null,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'description'      => $validated['description'] ?? null,
            'notes_officiant'  => null,
        ];

        $program = $ceremony->program ?? [];
        $program[] = $bloc;
        $ceremony->update(['program' => $program]);

        return redirect()->back();
    }

    /**
     * PUT /ceremonie/blocks — Réordonner et éditer les blocs du programme.
     *
     * Réservé aux époux du couple.
     */
    public function updateBlocks(Request $request): RedirectResponse
    {
        $couple = $this->resolveCouple($request);
        $ceremony = $this->resolveCeremony($couple);

        Gate::authorize('updateBlocks', $ceremony);

        $validated = $request->validate([
            'blocks'                    => ['required', 'array'],
            'blocks.*.id'               => ['required', 'string'],
            'blocks.*.type'             => ['required', 'string', 'in:entrance,welcome,reading,vows,exchange,speech,music,moment,exit,custom'],
            'blocks.*.title'            => ['required', 'string', 'max:200'],
            'blocks.*.time'             => ['nullable', 'string'],
            'blocks.*.duration_minutes' => ['nullable', 'integer', 'min:0', 'max:480'],
            'blocks.*.description'      => ['nullable', 'string', 'max:1000'],
        ]);

        // Conserver les notes_officiant existantes lors du réordonnancement
        $existingBlocks = collect($ceremony->program ?? [])
            ->keyBy('id');

        // Rejeter les ids soumis qui n'existent pas dans le programme actuel
        $existingIds  = $existingBlocks->keys();
        $submittedIds = collect($validated['blocks'])->pluck('id');

        if ($submittedIds->diff($existingIds)->isNotEmpty()) {
            abort(422, 'Un ou plusieurs identifiants de blocs sont invalides.');
        }

        $newProgram = collect($validated['blocks'])->map(function (array $bloc) use ($existingBlocks) {
            $existing = $existingBlocks->get($bloc['id']);
            return [
                'id'               => $bloc['id'],
                'type'             => $bloc['type'],
                'title'            => $bloc['title'],
                'time'             => $bloc['time'] ?? null,
                'duration_minutes' => $bloc['duration_minutes'] ?? null,
                'description'      => $bloc['description'] ?? null,
                'notes_officiant'  => $existing['notes_officiant'] ?? null,
            ];
        })->values()->toArray();

        $ceremony->update(['program' => $newProgram]);

        return redirect()->back();
    }

    /**
     * DELETE /ceremonie/blocks/{blockId} — Supprimer un bloc du programme.
     *
     * Réservé aux époux du couple.
     */
    public function removeBlock(Request $request, string $blockId): RedirectResponse
    {
        $couple = $this->resolveCouple($request);
        $ceremony = $this->resolveCeremony($couple);

        Gate::authorize('updateBlocks', $ceremony);

        $program = collect($ceremony->program ?? [])
            ->filter(fn (array $bloc) => $bloc['id'] !== $blockId)
            ->values()
            ->toArray();

        $ceremony->update(['program' => $program]);

        return redirect()->back();
    }

    /**
     * PATCH /ceremonie/blocks/{blockId}/notes — Annoter un bloc (officiant uniquement).
     */
    public function updateBlockNote(Request $request, string $blockId): RedirectResponse
    {
        $couple = $this->resolveCouple($request);
        $ceremony = $this->resolveCeremony($couple);

        Gate::authorize('updateOfficiantNote', $ceremony);

        $validated = $request->validate([
            'notes_officiant' => ['nullable', 'string', 'max:1000'],
        ]);

        // Vérifier que le bloc existe dans le programme
        abort_unless(
            collect($ceremony->program)->contains('id', $blockId),
            404,
            'Bloc introuvable.'
        );

        $program = collect($ceremony->program ?? [])
            ->map(function (array $bloc) use ($blockId, $validated) {
                if ($bloc['id'] === $blockId) {
                    $bloc['notes_officiant'] = $validated['notes_officiant'];
                }
                return $bloc;
            })
            ->values()
            ->toArray();

        $ceremony->update(['program' => $program]);

        return redirect()->back();
    }

    // ─── Helpers privés ────────────────────────────────────────────────────────

    /**
     * Résout le couple de l'utilisateur courant.
     * Supporte à la fois les époux et les officiants.
     */
    private function resolveCouple(Request $request)
    {
        $couple = $request->user()->couple()->first();

        if (! $couple) {
            $officiantDraft = OfficiantDraft::where('user_id', $request->user()->id)->first();
            abort_if(! $officiantDraft, 403, 'Accès refusé.');
            $couple = $officiantDraft->couple;
        }

        return $couple;
    }

    /**
     * Résout la cérémonie d'un couple, aborte 404 si absente.
     */
    private function resolveCeremony(Couple $couple): Ceremony
    {
        return $couple->ceremony()->firstOrFail();
    }
}
