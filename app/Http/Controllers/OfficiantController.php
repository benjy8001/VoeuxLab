<?php

namespace App\Http\Controllers;

use App\Models\{Couple, OfficiantAnswer, OfficiantDraft};
use App\Services\OfficiantGeneratorService;
use App\Support\OfficiantQuestions;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\{Inertia, Response};

class OfficiantController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Officiant/Join');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'invitation_code' => ['required', 'string', 'size:8', 'exists:couples,invitation_code'],
        ]);

        $couple = Couple::where('invitation_code', strtoupper($request->invitation_code))->firstOrFail();

        OfficiantDraft::firstOrCreate(
            ['user_id' => $request->user()->id, 'couple_id' => $couple->id],
            ['status' => 'in_progress', 'current_step' => 1]
        );

        $couple->update(['officiant_id' => $request->user()->id]);

        return redirect()->route('officiant.index');
    }

    public function index(Request $request): Response|RedirectResponse
    {
        $draft = OfficiantDraft::where('user_id', $request->user()->id)
            ->with('answers')
            ->first();

        if (! $draft) {
            return redirect()->route('officiant.create');
        }

        Gate::authorize('view', $draft);

        return Inertia::render('Officiant/Index', [
            'draft'     => $draft->only(['id', 'current_step', 'status']),
            'questions' => OfficiantQuestions::all(),
            'answers'   => $draft->answers->pluck('answer_text', 'question_key'),
        ]);
    }

    public function answer(Request $request): RedirectResponse
    {
        $request->validate([
            'question_key' => ['required', 'string', Rule::in(OfficiantQuestions::validKeys())],
            'answer_text'  => ['required', 'string', 'max:5000'],
            'current_step' => ['required', 'integer', 'min:1', 'max:' . OfficiantQuestions::count()],
            'final'        => ['boolean'],
        ]);

        $draft = OfficiantDraft::where('user_id', $request->user()->id)->firstOrFail();
        Gate::authorize('update', $draft);

        OfficiantAnswer::updateOrCreate(
            ['officiant_draft_id' => $draft->id, 'question_key' => $request->question_key],
            ['answer_text' => $request->answer_text, 'step_order' => $request->current_step],
        );

        $draft->update(['current_step' => $request->current_step]);

        if ($request->boolean('final')) {
            $draft->update(['status' => 'completed']);
            return redirect()->route('officiant.preview');
        }

        return back();
    }

    public function preview(Request $request): Response|RedirectResponse
    {
        $draft = OfficiantDraft::where('user_id', $request->user()->id)
            ->with('answers')
            ->first();

        if (! $draft) {
            return redirect()->route('officiant.create');
        }

        Gate::authorize('view', $draft);

        if (! $draft->generated_text) {
            $text = app(OfficiantGeneratorService::class)->generate($draft);
            $draft->update(['generated_text' => $text]);
        }

        return Inertia::render('Officiant/Preview', [
            'draft' => $draft->only(['id', 'status', 'generated_text']),
        ]);
    }
}
