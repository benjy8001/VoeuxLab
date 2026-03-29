<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinCoupleRequest;
use App\Models\Couple;
use App\Models\VowsDraft;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CoupleController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Couple/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->couple_id !== null, 403, 'Vous appartenez déjà à un couple.');

        $request->validate([
            'ceremony_date' => ['nullable', 'date', 'after:today'],
            'ceremony_location' => ['nullable', 'string', 'max:255'],
        ]);

        $couple = Couple::create([
            'spouse_1_id' => $request->user()->id,
            'ceremony_date' => $request->ceremony_date,
            'ceremony_location' => $request->ceremony_location,
        ]);

        $request->user()->update(['couple_id' => $couple->id]);

        VowsDraft::create([
            'user_id' => $request->user()->id,
            'couple_id' => $couple->id,
        ]);

        return redirect()->route('couple.show');
    }

    public function join(): Response
    {
        return Inertia::render('Couple/Join');
    }

    public function attach(JoinCoupleRequest $request): RedirectResponse
    {
        $couple = Couple::where('invitation_code', strtoupper($request->invitation_code))->firstOrFail();

        abort_if($couple->isFull(), 422, 'Ce couple est déjà complet.');

        $couple->update(['spouse_2_id' => $request->user()->id]);
        $request->user()->update(['couple_id' => $couple->id]);

        VowsDraft::create([
            'user_id' => $request->user()->id,
            'couple_id' => $couple->id,
        ]);

        return redirect()->route('couple.show');
    }

    public function show(Request $request): Response
    {
        $couple = $request->user()->couple()->with(['spouse1', 'spouse2'])->firstOrFail();

        return Inertia::render('Couple/Show', [
            'couple' => [
                'id' => $couple->id,
                'invitation_code' => $couple->invitation_code,
                'ceremony_date' => $couple->ceremony_date?->format('d/m/Y'),
                'ceremony_location' => $couple->ceremony_location,
                'is_full' => $couple->isFull(),
                'spouse1_name' => $couple->spouse1->name,
                'spouse2_name' => $couple->spouse2?->name,
            ],
        ]);
    }
}
