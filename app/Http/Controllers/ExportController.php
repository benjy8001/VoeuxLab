<?php

namespace App\Http\Controllers;

use App\Models\VowsDraft;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ExportController extends Controller
{
    public function vows(Request $request): Response
    {
        $draft = VowsDraft::where('user_id', $request->user()->id)
            ->where('couple_id', $request->user()->couple_id)
            ->with('couple')
            ->firstOrFail();

        Gate::authorize('view', $draft);

        abort_if(! $draft->generated_text, 404, 'Aucun texte de vœux à exporter.');

        $pdf = Pdf::loadView('pdf.vows', [
            'draft'  => $draft,
            'author' => $request->user()->name,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("voeux-{$request->user()->name}.pdf");
    }
}
