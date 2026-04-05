<?php

namespace App\Http\Controllers;

use App\Support\VowsQuestions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user   = $request->user();
        $couple = $user->couple?->load(['spouse1', 'spouse2']);
        $draft  = $couple ? $user->vowsDraft : null;

        $partnerVowsReadable = false;
        $partnerName = null;
        if ($couple && $draft) {
            $partnerVowsReadable = $draft->isReadableByPartner($couple);
            $partner = $couple->spouse_1_id === $user->id
                ? $couple->spouse2
                : $couple->spouse1;
            $partnerName = $partner?->name;
        }

        return Inertia::render('Dashboard', [
            'couple' => $couple ? [
                'invitation_code'   => $couple->invitation_code,
                'is_full'           => $couple->isFull(),
                'spouse1_name'      => $couple->spouse1->name,
                'spouse2_name'      => $couple->spouse2?->name,
                'ceremony_date'     => $couple->ceremony_date?->format('d/m/Y'),
                'ceremony_date_iso' => $couple->ceremony_date?->format('Y-m-d'),
                'ceremony_location' => $couple->ceremony_location,
            ] : null,
            'vows_progress' => $draft ? [
                'current_step' => $draft->current_step,
                'total_steps'  => VowsQuestions::count(),
                'status'       => $draft->status,
            ] : null,
            'partner_vows_readable' => $partnerVowsReadable,
            'partner_name'          => $partnerName,
            'app_url'               => config('app.url'),
        ]);
    }
}
