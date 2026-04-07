# Section Q&R dans Preview et PDF — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Afficher les questions et réponses du parcours vœux sous le texte généré, dans la page preview React et dans l'export PDF.

**Architecture:** Le contrôleur `VowsController::preview()` et `ExportController::vows()` passent chacun `questions` (tableau avec label + key, selon le ton) et `answers` (clé → texte) à leur vue respective. La page Preview.tsx affiche une section Q&R après `VowsInsights`. Le template Blade PDF affiche la même section après le texte des vœux.

**Tech Stack:** Laravel 13, Inertia.js, React + TypeScript, Tailwind CSS 4, DomPDF (barryvdh/laravel-dompdf), Pest PHP.

---

## Fichiers concernés

| Fichier | Action |
|---|---|
| `app/Http/Controllers/VowsController.php` | Modifier `preview()` — ajouter `questions` et `answers` aux props Inertia |
| `app/Http/Controllers/ExportController.php` | Modifier `vows()` — eager load `answers`, ajouter `questions` et `answers` à la vue |
| `resources/js/Pages/Voeux/Preview.tsx` | Modifier — ajouter `questions`/`answers` aux Props, afficher section Q&R |
| `resources/views/pdf/vows.blade.php` | Modifier — ajouter styles et section Q&R |
| `tests/Feature/VowsTest.php` | Modifier — ajouter tests preview props et export status |

---

## Task 1 : Test + VowsController — passer questions & answers à Preview

**Files:**
- Modify: `tests/Feature/VowsTest.php`
- Modify: `app/Http/Controllers/VowsController.php:100-108`

- [ ] **Step 1 : Écrire le test qui échoue**

Ouvrir `tests/Feature/VowsTest.php` et ajouter à la fin :

```php
test('preview passes questions and answers to inertia', function () {
    $user = userWithDraft();
    $draft = VowsDraft::where('user_id', $user->id)->first();

    VowsAnswer::factory()->create([
        'vows_draft_id' => $draft->id,
        'question_key'  => 'meeting_story',
        'answer_text'   => 'Nous nous sommes rencontrés à Paris.',
        'step_order'    => 1,
    ]);

    $draft->update(['generated_text' => 'Vœux générés.', 'status' => 'completed']);

    $this->actingAs($user)
        ->get(route('voeux.preview'))
        ->assertInertia(fn ($page) => $page
            ->component('Voeux/Preview')
            ->has('questions', VowsQuestions::count())
            ->has('answers.meeting_story')
        );
});
```

- [ ] **Step 2 : Lancer le test pour vérifier qu'il échoue**

```bash
make tests
```

Résultat attendu : le test `preview passes questions and answers to inertia` échoue avec une erreur sur `questions` manquant dans les props.

- [ ] **Step 3 : Modifier `VowsController::preview()`**

Dans `app/Http/Controllers/VowsController.php`, remplacer le `return Inertia::render(...)` de la méthode `preview()` (lignes 100–108) :

```php
return Inertia::render('Voeux/Preview', [
    'draft'                  => $draft->only(['id', 'status', 'generated_text']),
    'partner_name'           => $partnerName,
    'has_shared'             => $hasShared,
    'partner_has_shared'     => $partnerHasShared,
    'partner_vows_readable'  => $draft->isReadableByPartner($couple),
    'questions'              => VowsQuestions::forTone($draft->tone ?? 'balanced'),
    'answers'                => $draft->answers->pluck('answer_text', 'question_key'),
]);
```

- [ ] **Step 4 : Lancer les tests pour vérifier qu'ils passent**

```bash
make tests
```

Résultat attendu : tous les tests passent, y compris `preview passes questions and answers to inertia`.

- [ ] **Step 5 : Commit**

```bash
git add tests/Feature/VowsTest.php app/Http/Controllers/VowsController.php
git commit -m "feat: passer questions et réponses à la page preview"
```

---

## Task 2 : Preview.tsx — afficher la section Q&R

**Files:**
- Modify: `resources/js/Pages/Voeux/Preview.tsx`

- [ ] **Step 1 : Mettre à jour l'interface Props**

Dans `resources/js/Pages/Voeux/Preview.tsx`, remplacer l'interface `Props` :

```ts
interface Props {
    draft: { id: number; status: string; generated_text: string };
    partner_name: string | null;
    has_shared: boolean;
    partner_has_shared: boolean;
    partner_vows_readable: boolean;
    questions: { key: string; label: string; order: number }[];
    answers: Record<string, string>;
}
```

- [ ] **Step 2 : Ajouter `questions` et `answers` à la déstructuration**

Remplacer la ligne de déclaration de la fonction :

```tsx
export default function VoeuxPreview({ draft, partner_name, has_shared, partner_has_shared, partner_vows_readable, questions, answers }: Props) {
```

- [ ] **Step 3 : Ajouter la section Q&R après `<VowsInsights>`**

Après la ligne `<VowsInsights text={text} partnerName={partner_name} />` et avant le bloc `{/* Bloc partage */}`, insérer :

```tsx
{questions.filter(q => answers[q.key]).length > 0 && (
    <div className="mt-10 border-t border-stone-200 pt-8">
        <h3 className="text-lg font-serif text-stone-700 mb-6">Mes réponses</h3>
        <div className="space-y-6">
            {questions
                .filter(q => answers[q.key])
                .map(q => (
                    <div key={q.key}>
                        <p className="text-sm italic text-stone-400 mb-1">{q.label}</p>
                        <p className="text-stone-700">{answers[q.key]}</p>
                    </div>
                ))
            }
        </div>
    </div>
)}
```

- [ ] **Step 4 : Vérifier que le build TypeScript ne produit pas d'erreur**

```bash
docker compose exec app npm run build 2>&1 | tail -20
```

Résultat attendu : build sans erreur TypeScript.

- [ ] **Step 5 : Commit**

```bash
git add resources/js/Pages/Voeux/Preview.tsx
git commit -m "feat: afficher section Q&R dans la page preview"
```

---

## Task 3 : Test + ExportController — passer questions & answers au PDF

**Files:**
- Modify: `tests/Feature/VowsTest.php`
- Modify: `app/Http/Controllers/ExportController.php`

- [ ] **Step 1 : Écrire le test qui échoue**

Ouvrir `tests/Feature/VowsTest.php` et ajouter à la fin :

```php
test('export vows pdf returns a download with answers', function () {
    $user = userWithDraft();
    $draft = VowsDraft::where('user_id', $user->id)->first();

    VowsAnswer::factory()->create([
        'vows_draft_id' => $draft->id,
        'question_key'  => 'meeting_story',
        'answer_text'   => 'Nous nous sommes rencontrés.',
        'step_order'    => 1,
    ]);

    $draft->update(['generated_text' => 'Vœux.', 'status' => 'completed']);

    $this->actingAs($user)
        ->get(route('voeux.export'))
        ->assertStatus(200)
        ->assertHeader('content-type', 'application/pdf');
});
```

- [ ] **Step 2 : Lancer le test pour vérifier qu'il échoue ou passe déjà**

```bash
make tests
```

Si le test passe déjà (l'export fonctionne sans les nouvelles données), continuer quand même pour s'assurer qu'il passe après les modifications.

- [ ] **Step 3 : Modifier `ExportController::vows()`**

Remplacer le contenu de `app/Http/Controllers/ExportController.php` :

```php
<?php

namespace App\Http\Controllers;

use App\Models\VowsDraft;
use App\Support\VowsQuestions;
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
            ->with(['couple', 'answers'])
            ->firstOrFail();

        Gate::authorize('view', $draft);

        abort_if(! $draft->generated_text, 404, 'Aucun texte de vœux à exporter.');

        $pdf = Pdf::loadView('pdf.vows', [
            'draft'     => $draft,
            'author'    => $request->user()->name,
            'questions' => VowsQuestions::forTone($draft->tone ?? 'balanced'),
            'answers'   => $draft->answers->pluck('answer_text', 'question_key'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("voeux-{$request->user()->name}.pdf");
    }
}
```

- [ ] **Step 4 : Lancer les tests**

```bash
make tests
```

Résultat attendu : tous les tests passent.

- [ ] **Step 5 : Commit**

```bash
git add tests/Feature/VowsTest.php app/Http/Controllers/ExportController.php
git commit -m "feat: passer questions et réponses à l'export PDF"
```

---

## Task 4 : PDF Blade — afficher la section Q&R

**Files:**
- Modify: `resources/views/pdf/vows.blade.php`

- [ ] **Step 1 : Ajouter les styles CSS pour la section Q&R**

Dans `resources/views/pdf/vows.blade.php`, ajouter dans le bloc `<style>`, après le style `.footer` :

```css
.qa-section {
    margin-top: 1.5cm;
    padding-top: 1cm;
    border-top: 1px solid #e8d9c0;
}

.qa-title {
    font-family: 'DejaVu Serif', serif;
    font-size: 16pt;
    font-weight: normal;
    color: #7c6343;
    margin-bottom: 0.8cm;
}

.qa-item {
    margin-bottom: 0.6cm;
}

.qa-question {
    font-style: italic;
    color: #b09878;
    font-size: 10pt;
    margin-bottom: 0.1cm;
}

.qa-answer {
    color: #3d3530;
    font-size: 11pt;
    white-space: pre-wrap;
    word-wrap: break-word;
}
```

- [ ] **Step 2 : Ajouter la section Q&R dans le corps du document**

Dans `resources/views/pdf/vows.blade.php`, après `<div class="content">{{ $draft->generated_text }}</div>` et avant la fermeture de `<div class="page">`, ajouter :

```html
@if(count(array_filter($questions, fn($q) => $answers->has($q['key']))) > 0)
<div class="qa-section">
    <div class="qa-title">Mes réponses</div>
    @foreach($questions as $q)
        @if($answers->has($q['key']))
            <div class="qa-item">
                <p class="qa-question">{{ $q['label'] }}</p>
                <p class="qa-answer">{{ $answers->get($q['key']) }}</p>
            </div>
        @endif
    @endforeach
</div>
@endif
```

- [ ] **Step 3 : Lancer les tests pour s'assurer que rien n'est cassé**

```bash
make tests
```

Résultat attendu : tous les tests passent.

- [ ] **Step 4 : Commit**

```bash
git add resources/views/pdf/vows.blade.php
git commit -m "feat: afficher section Q&R dans l'export PDF"
```
