<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVowsAnswerRequest;
use App\Models\VowsAnswer;
use App\Models\VowsDraft;
use App\Services\VowsGeneratorService;
use App\Support\VowsQuestions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VowsController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        if ($request->user()->couple_id === null) {
            return redirect()->route('couple.create');
        }

        $draft = VowsDraft::where('user_id', $request->user()->id)
            ->where('couple_id', $request->user()->couple_id)
            ->with('answers')
            ->firstOrFail();

        Gate::authorize('view', $draft);

        return Inertia::render('Voeux/Index', [
            'draft'     => $draft->only(['id', 'current_step', 'status', 'tone']),
            'questions' => VowsQuestions::forTone($draft->tone ?? 'balanced'),
            'answers'   => $draft->answers->pluck('answer_text', 'question_key'),
        ]);
    }

    public function answer(StoreVowsAnswerRequest $request): RedirectResponse
    {
        $draft = VowsDraft::where('user_id', $request->user()->id)
            ->where('couple_id', $request->user()->couple_id)
            ->firstOrFail();

        Gate::authorize('update', $draft);

        VowsAnswer::updateOrCreate(
            ['vows_draft_id' => $draft->id, 'question_key' => $request->question_key],
            ['answer_text' => $request->answer_text, 'step_order' => $request->current_step],
        );

        $draft->update(['current_step' => $request->current_step]);

        if ($request->boolean('final')) {
            $draft->update(['status' => 'completed']);
            return redirect()->route('voeux.preview');
        }

        return back();
    }

    public function preview(Request $request): Response|RedirectResponse
    {
        if ($request->user()->couple_id === null) {
            return redirect()->route('couple.create');
        }

        $draft = VowsDraft::where('user_id', $request->user()->id)
            ->where('couple_id', $request->user()->couple_id)
            ->with(['answers', 'couple.spouse1', 'couple.spouse2'])
            ->firstOrFail();

        Gate::authorize('view', $draft);

        if (! $draft->generated_text) {
            $text = app(VowsGeneratorService::class)->generate($draft);
            $draft->update(['generated_text' => $text]);
        }

        $couple      = $draft->couple;
        $partnerName = $couple->spouse_1_id === $request->user()->id
            ? $couple->spouse2?->name
            : $couple->spouse1->name;

        $hasShared = $draft->shared_at !== null;
        $partnerHasShared = false;

        if ($couple->isFull()) {
            $partnerId = $couple->spouse_1_id === $request->user()->id
                ? $couple->spouse_2_id
                : $couple->spouse_1_id;
            $partnerDraft = VowsDraft::where('user_id', $partnerId)
                ->where('couple_id', $couple->id)
                ->first();
            $partnerHasShared = $partnerDraft?->shared_at !== null;
        }

        return Inertia::render('Voeux/Preview', [
            'draft'                  => $draft->only(['id', 'status', 'generated_text']),
            'partner_name'           => $partnerName,
            'has_shared'             => $hasShared,
            'partner_has_shared'     => $partnerHasShared,
            // La condition est symétrique : si je peux lire les vœux du partenaire,
            // le partenaire peut aussi lire les miens.
            'partner_vows_readable'  => $draft->isReadableByPartner($couple),
        ]);
    }

    public function edit(Request $request): Response|RedirectResponse
    {
        if ($request->user()->couple_id === null) {
            return redirect()->route('couple.create');
        }

        $draft = VowsDraft::where('user_id', $request->user()->id)
            ->where('couple_id', $request->user()->couple_id)
            ->firstOrFail();

        Gate::authorize('update', $draft);

        return Inertia::render('Voeux/Edit', [
            'draft' => $draft->only(['id', 'generated_text']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $draft = VowsDraft::where('user_id', $request->user()->id)
            ->where('couple_id', $request->user()->couple_id)
            ->firstOrFail();

        Gate::authorize('update', $draft);

        $request->validate([
            'generated_text' => ['required', 'string', 'max:10000'],
        ]);

        $draft->update(['generated_text' => $request->generated_text]);

        return redirect()->route('voeux.preview');
    }

    public function setTone(Request $request): RedirectResponse
    {
        if ($request->user()->couple_id === null) {
            return redirect()->route('couple.create');
        }

        $request->validate([
            'tone' => ['required', 'string', Rule::in(VowsQuestions::validTones())],
        ]);

        $draft = VowsDraft::where('user_id', $request->user()->id)
            ->where('couple_id', $request->user()->couple_id)
            ->firstOrFail();

        Gate::authorize('update', $draft);

        $draft->update(['tone' => $request->tone]);

        return redirect()->route('voeux.index');
    }

    public function regenerate(Request $request): RedirectResponse
    {
        if ($request->user()->couple_id === null) {
            return redirect()->route('couple.create');
        }

        $draft = VowsDraft::where('user_id', $request->user()->id)
            ->where('couple_id', $request->user()->couple_id)
            ->firstOrFail();

        Gate::authorize('update', $draft);

        $draft->update(['generated_text' => null]);

        return redirect()->route('voeux.preview');
    }

    public function share(Request $request): RedirectResponse
    {
        if ($request->user()->couple_id === null) {
            return redirect()->route('couple.create');
        }

        $draft = VowsDraft::where('user_id', $request->user()->id)
            ->where('couple_id', $request->user()->couple_id)
            ->firstOrFail();

        Gate::authorize('update', $draft);

        // Marque les vœux comme partagés (idempotent)
        if (!$draft->shared_at) {
            $draft->update(['shared_at' => now()]);
        }

        return redirect()->route('voeux.preview');
    }

    public function partner(Request $request): Response|RedirectResponse
    {
        if ($request->user()->couple_id === null) {
            return redirect()->route('couple.create');
        }

        $couple = $request->user()->couple->load(['spouse1', 'spouse2']);

        // Détermine l'identifiant du partenaire
        $partnerId = $couple->spouse_1_id === $request->user()->id
            ? $couple->spouse_2_id
            : $couple->spouse_1_id;

        if (!$partnerId) {
            return redirect()->route('voeux.preview');
        }

        $partnerDraft = VowsDraft::where('user_id', $partnerId)
            ->where('couple_id', $couple->id)
            ->firstOrFail();

        Gate::authorize('viewPartner', $partnerDraft);

        $partnerName = $partnerId === $couple->spouse_1_id
            ? $couple->spouse1?->name
            : $couple->spouse2?->name;

        return Inertia::render('Voeux/Partner', [
            'generated_text' => $partnerDraft->generated_text,
            'partner_name'   => $partnerName,
        ]);
    }
}
