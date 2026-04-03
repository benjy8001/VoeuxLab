# Régénération des vœux & Partage mutuel — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ajouter un bouton de régénération des vœux depuis les réponses, et un mécanisme de partage mutuel permettant à chaque époux de lire les vœux de l'autre après consentement des deux ou après la date de cérémonie.

**Architecture:** Feature 1 ajoute une route `POST /voeux/regenerate` qui efface `generated_text` et déclenche la régénération existante. Feature 2 ajoute une colonne `shared_at` sur `vows_drafts`, une Policy `viewPartner`, deux nouvelles routes (`POST /voeux/share`, `GET /voeux/partner`), et une page lecture-seule `Voeux/Partner.tsx`.

**Tech Stack:** Laravel 13, Pest PHP, Inertia.js, React/TypeScript, Tailwind CSS 4, MariaDB.

**Commandes utiles :**
- Lancer tous les tests : `make tests`
- Test ciblé : `docker compose exec phpfpm php artisan test --filter="nom du test"`
- Migration : `docker compose exec phpfpm php artisan migrate`

---

## Fichiers touchés

### Feature 1 — Régénération
| Action | Fichier |
|--------|---------|
| Créer | `tests/Feature/VowsRegenerateTest.php` |
| Modifier | `app/Http/Controllers/VowsController.php` |
| Modifier | `routes/web.php` |
| Modifier | `resources/js/Pages/Voeux/Preview.tsx` |

### Feature 2 — Partage mutuel
| Action | Fichier |
|--------|---------|
| Créer | `database/migrations/2026_04_03_000001_add_shared_at_to_vows_drafts_table.php` |
| Modifier | `app/Models/VowsDraft.php` |
| Créer | `tests/Unit/VowsDraftIsReadableByPartnerTest.php` |
| Modifier | `app/Policies/VowsDraftPolicy.php` |
| Créer | `tests/Feature/VowsShareTest.php` |
| Modifier | `app/Http/Controllers/VowsController.php` |
| Modifier | `app/Http/Controllers/DashboardController.php` |
| Modifier | `routes/web.php` |
| Modifier | `resources/js/Pages/Voeux/Preview.tsx` |
| Créer | `resources/js/Pages/Voeux/Partner.tsx` |
| Modifier | `resources/js/Pages/Dashboard.tsx` |

---

## Feature 1 — Régénération des vœux

---

### Task 1 : Test route régénération

**Files:**
- Create: `tests/Feature/VowsRegenerateTest.php`

- [ ] **Étape 1 : Écrire les tests échouants**

```php
<?php

use App\Models\Couple;
use App\Models\User;
use App\Models\VowsDraft;

test('user peut régénérer ses vœux et generated_text est effacé', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $draft = VowsDraft::factory()->create([
        'user_id'        => $user->id,
        'couple_id'      => $couple->id,
        'status'         => 'completed',
        'generated_text' => 'texte existant',
    ]);

    $this->actingAs($user)
        ->post(route('voeux.regenerate'))
        ->assertRedirect(route('voeux.preview'));

    expect($draft->fresh()->generated_text)->toBeNull();
});

test('user ne peut pas régénérer le draft d\'un autre', function () {
    $owner = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $owner->id]);
    VowsDraft::factory()->create([
        'user_id'   => $owner->id,
        'couple_id' => $couple->id,
    ]);

    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->post(route('voeux.regenerate'))
        ->assertStatus(404);
});
```

- [ ] **Étape 2 : Vérifier que les tests échouent**

```bash
docker compose exec phpfpm php artisan test --filter="régénérer"
```

Résultat attendu : FAIL (route non trouvée)

---

### Task 2 : Implémenter `regenerate()` et la route

**Files:**
- Modify: `app/Http/Controllers/VowsController.php`
- Modify: `routes/web.php`

- [ ] **Étape 1 : Ajouter la méthode dans `VowsController`**

Ajouter après la méthode `setTone()` :

```php
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
```

- [ ] **Étape 2 : Ajouter la route dans `routes/web.php`**

Dans le groupe `auth + verified`, après la ligne `Route::post('/voeux/tone', ...)` :

```php
Route::post('/voeux/regenerate', [VowsController::class, 'regenerate'])->name('voeux.regenerate');
```

- [ ] **Étape 3 : Lancer les tests**

```bash
docker compose exec phpfpm php artisan test --filter="régénérer"
```

Résultat attendu : PASS (2 tests)

- [ ] **Étape 4 : Lancer la suite complète**

```bash
make tests
```

Résultat attendu : tous les tests passent

- [ ] **Étape 5 : Commit**

```bash
git add app/Http/Controllers/VowsController.php routes/web.php tests/Feature/VowsRegenerateTest.php
git commit -m "feat: route POST /voeux/regenerate pour régénérer les vœux"
```

---

### Task 3 : Bouton "Régénérer" sur Preview.tsx

**Files:**
- Modify: `resources/js/Pages/Voeux/Preview.tsx`

- [ ] **Étape 1 : Ajouter le bouton dans la zone d'actions**

Remplacer le bloc `<div className="flex flex-wrap gap-4 mt-8">` jusqu'à la fermeture `</div>` dans `Voeux/Preview.tsx` par :

```tsx
<div className="flex flex-wrap gap-4 mt-8">
    <Link
        href={route('voeux.edit')}
        className="px-6 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 transition-colors"
    >
        Modifier le texte
    </Link>
    <Link
        href={route('voeux.index')}
        className="px-6 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 transition-colors"
    >
        Revoir mes réponses
    </Link>
    <button
        onClick={() => {
            if (window.confirm('Cette action remplacera le texte actuel par une nouvelle génération depuis vos réponses. Continuer ?')) {
                router.post(route('voeux.regenerate'));
            }
        }}
        className="px-6 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 transition-colors"
    >
        Régénérer depuis mes réponses
    </button>
    <a
        href={route('voeux.export')}
        className="px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors"
    >
        Télécharger PDF
    </a>
</div>
```

- [ ] **Étape 2 : Vérifier visuellement dans le navigateur**

Naviguer vers `/voeux/preview` — le bouton "Régénérer depuis mes réponses" doit apparaître à gauche de "Télécharger PDF". Cliquer dessus doit afficher une confirmation, puis régénérer.

- [ ] **Étape 3 : Commit**

```bash
git add resources/js/Pages/Voeux/Preview.tsx
git commit -m "feat: bouton de régénération des vœux sur la page d'aperçu"
```

---

## Feature 2 — Partage mutuel des vœux

---

### Task 4 : Migration + modèle `VowsDraft`

**Files:**
- Create: `database/migrations/2026_04_03_000001_add_shared_at_to_vows_drafts_table.php`
- Modify: `app/Models/VowsDraft.php`

- [ ] **Étape 1 : Créer la migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vows_drafts', function (Blueprint $table) {
            $table->timestamp('shared_at')->nullable()->after('generated_text');
        });
    }

    public function down(): void
    {
        Schema::table('vows_drafts', function (Blueprint $table) {
            $table->dropColumn('shared_at');
        });
    }
};
```

- [ ] **Étape 2 : Lancer la migration**

```bash
docker compose exec phpfpm php artisan migrate
```

- [ ] **Étape 3 : Écrire les tests unitaires échouants pour `isReadableByPartner()`**

Créer `tests/Unit/VowsDraftIsReadableByPartnerTest.php` :

```php
<?php

use App\Models\Couple;
use App\Models\User;
use App\Models\VowsDraft;
use Illuminate\Support\Carbon;

test('retourne false si les deux époux n\'ont pas partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::tomorrow(),
    ]);
    $myDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => null,
    ]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse2->id,
        'couple_id' => $couple->id,
        'shared_at' => null,
    ]);

    expect($myDraft->isReadableByPartner($couple))->toBeFalse();
});

test('retourne false si seulement moi ai partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::tomorrow(),
    ]);
    $myDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse2->id,
        'couple_id' => $couple->id,
        'shared_at' => null,
    ]);

    expect($myDraft->isReadableByPartner($couple))->toBeFalse();
});

test('retourne true si les deux époux ont partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::tomorrow(),
    ]);
    $myDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse2->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);

    expect($myDraft->isReadableByPartner($couple))->toBeTrue();
});

test('retourne true si la date de cérémonie est passée', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::yesterday(),
    ]);
    $myDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => null,
    ]);

    expect($myDraft->isReadableByPartner($couple))->toBeTrue();
});

test('retourne false si le partenaire n\'a pas encore rejoint le couple', function () {
    $spouse1 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => null,
        'ceremony_date' => Carbon::tomorrow(),
    ]);
    $myDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);

    expect($myDraft->isReadableByPartner($couple))->toBeFalse();
});
```

- [ ] **Étape 4 : Vérifier que les tests échouent**

```bash
docker compose exec phpfpm php artisan test --filter="isReadableByPartner"
```

Résultat attendu : FAIL (méthode non définie)

- [ ] **Étape 5 : Implémenter `isReadableByPartner()` dans `VowsDraft`**

Modifier `app/Models/VowsDraft.php` :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VowsDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'couple_id', 'status', 'tone', 'current_step', 'generated_text', 'shared_at',
    ];

    protected $casts = [
        'shared_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(VowsAnswer::class)->orderBy('step_order');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Vérifie si les vœux du partenaire sont lisibles par l'utilisateur propriétaire de ce draft.
     * Conditions : les deux ont partagé, ou la date de cérémonie est passée.
     */
    public function isReadableByPartner(Couple $couple): bool
    {
        // Déverrouillage automatique après la date de cérémonie
        if ($couple->ceremony_date && $couple->ceremony_date->lte(now())) {
            return true;
        }

        // Partage mutuel requis
        if (!$couple->spouse_2_id) {
            return false;
        }

        $partnerId = $couple->spouse_1_id === $this->user_id
            ? $couple->spouse_2_id
            : $couple->spouse_1_id;

        $partnerDraft = self::where('user_id', $partnerId)
            ->where('couple_id', $couple->id)
            ->first();

        return $this->shared_at !== null && $partnerDraft?->shared_at !== null;
    }
}
```

- [ ] **Étape 6 : Vérifier que les tests passent**

```bash
docker compose exec phpfpm php artisan test --filter="isReadableByPartner"
```

Résultat attendu : PASS (5 tests)

- [ ] **Étape 7 : Commit**

```bash
git add database/migrations/2026_04_03_000001_add_shared_at_to_vows_drafts_table.php \
        app/Models/VowsDraft.php \
        tests/Unit/VowsDraftIsReadableByPartnerTest.php
git commit -m "feat: colonne shared_at et méthode isReadableByPartner sur VowsDraft"
```

---

### Task 5 : Policy `viewPartner`

**Files:**
- Modify: `app/Policies/VowsDraftPolicy.php`

- [ ] **Étape 1 : Écrire les tests échouants pour la policy**

Ajouter dans `tests/Feature/VowsPolicyTest.php` :

```php
test('un époux peut consulter les vœux du partenaire si les deux ont partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => \Illuminate\Support\Carbon::tomorrow(),
    ]);
    $spouse1->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    $partnerDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse2->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);

    expect($spouse1->can('viewPartner', $partnerDraft))->toBeTrue();
});

test('un époux ne peut pas consulter les vœux du partenaire si l\'un d\'eux n\'a pas partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => \Illuminate\Support\Carbon::tomorrow(),
    ]);
    $spouse1->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    $partnerDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse2->id,
        'couple_id' => $couple->id,
        'shared_at' => null,
    ]);

    expect($spouse1->can('viewPartner', $partnerDraft))->toBeFalse();
});

test('un tiers ne peut jamais accéder aux vœux du partenaire', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id' => $spouse1->id,
        'spouse_2_id' => $spouse2->id,
    ]);
    $partnerDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse2->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    $intruder = User::factory()->create();

    expect($intruder->can('viewPartner', $partnerDraft))->toBeFalse();
});
```

- [ ] **Étape 2 : Vérifier que les tests échouent**

```bash
docker compose exec phpfpm php artisan test --filter="viewPartner|vœux du partenaire"
```

Résultat attendu : FAIL (méthode non définie)

- [ ] **Étape 3 : Implémenter `viewPartner()` dans `VowsDraftPolicy`**

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VowsDraft;

class VowsDraftPolicy
{
    public function view(User $user, VowsDraft $draft): bool
    {
        return $user->id === $draft->user_id;
    }

    public function update(User $user, VowsDraft $draft): bool
    {
        return $user->id === $draft->user_id;
    }

    /**
     * Un époux peut lire les vœux du partenaire si les deux ont partagé
     * ou si la date de cérémonie est passée.
     */
    public function viewPartner(User $user, VowsDraft $partnerDraft): bool
    {
        $couple = $user->couple;
        if (!$couple) {
            return false;
        }

        // Le draft cible doit appartenir au partenaire du même couple
        if ($partnerDraft->user_id === $user->id) {
            return false;
        }
        if ($partnerDraft->couple_id !== $couple->id) {
            return false;
        }

        $myDraft = VowsDraft::where('user_id', $user->id)
            ->where('couple_id', $couple->id)
            ->first();

        return $myDraft?->isReadableByPartner($couple) ?? false;
    }
}
```

- [ ] **Étape 4 : Vérifier que les tests passent**

```bash
docker compose exec phpfpm php artisan test --filter="viewPartner|vœux du partenaire"
```

Résultat attendu : PASS (3 tests)

- [ ] **Étape 5 : Commit**

```bash
git add app/Policies/VowsDraftPolicy.php tests/Feature/VowsPolicyTest.php
git commit -m "feat: policy viewPartner pour la lecture des vœux du partenaire"
```

---

### Task 6 : Routes + contrôleurs `share()` et `partner()`

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/VowsController.php`
- Create: `tests/Feature/VowsShareTest.php`

- [ ] **Étape 1 : Écrire les tests échouants**

Créer `tests/Feature/VowsShareTest.php` :

```php
<?php

use App\Models\Couple;
use App\Models\User;
use App\Models\VowsDraft;
use Illuminate\Support\Carbon;

test('POST /voeux/share renseigne shared_at sur le draft', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $user->update(['couple_id' => $couple->id]);
    $draft = VowsDraft::factory()->create([
        'user_id'   => $user->id,
        'couple_id' => $couple->id,
        'shared_at' => null,
    ]);

    $this->actingAs($user)
        ->post(route('voeux.share'))
        ->assertRedirect(route('voeux.preview'));

    expect($draft->fresh()->shared_at)->not->toBeNull();
});

test('POST /voeux/share est idempotent (ne change pas shared_at si déjà renseigné)', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $user->update(['couple_id' => $couple->id]);
    $sharedAt = Carbon::parse('2026-01-01 10:00:00');
    $draft = VowsDraft::factory()->create([
        'user_id'   => $user->id,
        'couple_id' => $couple->id,
        'shared_at' => $sharedAt,
    ]);

    $this->actingAs($user)
        ->post(route('voeux.share'));

    expect($draft->fresh()->shared_at->toDateTimeString())->toBe($sharedAt->toDateTimeString());
});

test('GET /voeux/partner retourne 403 si le partenaire n\'a pas encore partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::tomorrow(),
    ]);
    $spouse1->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    VowsDraft::factory()->create([
        'user_id'        => $spouse2->id,
        'couple_id'      => $couple->id,
        'shared_at'      => null,
        'generated_text' => 'vœux partenaire',
    ]);

    $this->actingAs($spouse1)
        ->get(route('voeux.partner'))
        ->assertForbidden();
});

test('GET /voeux/partner retourne le texte du partenaire si les deux ont partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::tomorrow(),
    ]);
    $spouse1->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    VowsDraft::factory()->create([
        'user_id'        => $spouse2->id,
        'couple_id'      => $couple->id,
        'shared_at'      => now(),
        'generated_text' => 'vœux du partenaire',
    ]);

    $this->actingAs($spouse1)
        ->get(route('voeux.partner'))
        ->assertInertia(fn ($page) => $page
            ->component('Voeux/Partner')
            ->where('generated_text', 'vœux du partenaire')
        );
});
```

- [ ] **Étape 2 : Vérifier que les tests échouent**

```bash
docker compose exec phpfpm php artisan test --filter="voeux.share|voeux.partner|partenaire"
```

Résultat attendu : FAIL (routes non définies)

- [ ] **Étape 3 : Ajouter les routes dans `routes/web.php`**

Dans le groupe `auth + verified`, après `Route::post('/voeux/regenerate', ...)` :

```php
Route::post('/voeux/share', [VowsController::class, 'share'])->name('voeux.share');
Route::get('/voeux/partner', [VowsController::class, 'partner'])->name('voeux.partner');
```

- [ ] **Étape 4 : Ajouter `share()` dans `VowsController`**

```php
public function share(Request $request): RedirectResponse
{
    if ($request->user()->couple_id === null) {
        return redirect()->route('couple.create');
    }

    $draft = VowsDraft::where('user_id', $request->user()->id)
        ->where('couple_id', $request->user()->couple_id)
        ->firstOrFail();

    Gate::authorize('update', $draft);

    if (!$draft->shared_at) {
        $draft->update(['shared_at' => now()]);
    }

    return redirect()->route('voeux.preview');
}
```

- [ ] **Étape 5 : Ajouter `partner()` dans `VowsController`**

```php
public function partner(Request $request): Response|RedirectResponse
{
    if ($request->user()->couple_id === null) {
        return redirect()->route('couple.create');
    }

    $couple = $request->user()->couple->load(['spouse1', 'spouse2']);

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
        ? $couple->spouse1->name
        : $couple->spouse2->name;

    return Inertia::render('Voeux/Partner', [
        'generated_text' => $partnerDraft->generated_text,
        'partner_name'   => $partnerName,
    ]);
}
```

- [ ] **Étape 6 : Lancer les tests**

```bash
docker compose exec phpfpm php artisan test --filter="voeux.share|voeux.partner|partenaire"
```

Résultat attendu : PASS (4 tests)

- [ ] **Étape 7 : Lancer la suite complète**

```bash
make tests
```

Résultat attendu : tous les tests passent

- [ ] **Étape 8 : Commit**

```bash
git add routes/web.php app/Http/Controllers/VowsController.php tests/Feature/VowsShareTest.php
git commit -m "feat: routes share et partner, contrôleurs VowsController::share et partner"
```

---

### Task 7 : Mettre à jour `preview()` et `DashboardController`

**Files:**
- Modify: `app/Http/Controllers/VowsController.php`
- Modify: `app/Http/Controllers/DashboardController.php`

- [ ] **Étape 1 : Mettre à jour `VowsController::preview()`**

Remplacer le `return Inertia::render('Voeux/Preview', [...])` dans `preview()` par :

```php
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
    'partner_vows_readable'  => $draft->isReadableByPartner($couple),
]);
```

Note : La variable `$couple` est déjà chargée dans `preview()` via `$draft->couple`. Remplacer la ligne existante `$couple = $draft->couple;` pour s'assurer qu'elle charge les relations : `$couple = $draft->couple->load(['spouse1', 'spouse2']);` (la relation est déjà chargée via `->with(['answers', 'couple.spouse1', 'couple.spouse2'])`, rien à changer sur le `with`).

- [ ] **Étape 2 : Mettre à jour `DashboardController::index()`**

Ajouter après la récupération de `$draft` :

```php
$partnerVowsReadable = false;
if ($couple && $draft) {
    $partnerVowsReadable = $draft->isReadableByPartner($couple);
}
```

Ajouter avant le tableau Inertia, le calcul du nom du partenaire :

```php
$partnerName = null;
if ($couple) {
    $partnerName = $couple->spouse_1_id === $user->id
        ? $couple->spouse2?->name
        : $couple->spouse1->name;
}
```

Ajouter dans le tableau Inertia, après `'vows_progress'` :

```php
'partner_vows_readable' => $partnerVowsReadable,
'partner_name'          => $partnerName,
```

- [ ] **Étape 3 : Lancer la suite complète**

```bash
make tests
```

Résultat attendu : tous les tests passent

- [ ] **Étape 4 : Commit**

```bash
git add app/Http/Controllers/VowsController.php app/Http/Controllers/DashboardController.php
git commit -m "feat: preview passe les props de partage, dashboard passe partner_vows_readable"
```

---

### Task 8 : Nouvelle page `Voeux/Partner.tsx`

**Files:**
- Create: `resources/js/Pages/Voeux/Partner.tsx`

- [ ] **Étape 1 : Créer la page**

```tsx
import { Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface Props {
    generated_text: string;
    partner_name: string;
}

export default function VoeuxPartner({ generated_text, partner_name }: Props) {
    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl text-gray-800">Vœux de {partner_name}</h2>}>
            <div className="max-w-2xl mx-auto px-4 py-8">
                <h1 className="text-3xl font-serif text-stone-800 mb-2">
                    Vœux de {partner_name}
                </h1>
                <p className="text-sm text-stone-400 italic mb-8">Lecture seule</p>

                <div className="bg-stone-50 border border-stone-100 rounded-xl p-6 whitespace-pre-wrap text-stone-700 leading-relaxed font-serif">
                    {generated_text}
                </div>

                <div className="mt-8">
                    <Link
                        href={route('voeux.preview')}
                        className="px-6 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 transition-colors"
                    >
                        ← Retour à mes vœux
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
```

- [ ] **Étape 2 : Commit**

```bash
git add resources/js/Pages/Voeux/Partner.tsx
git commit -m "feat: page lecture seule des vœux du partenaire (Voeux/Partner)"
```

---

### Task 9 : UI de partage dans `Voeux/Preview.tsx`

**Files:**
- Modify: `resources/js/Pages/Voeux/Preview.tsx`

- [ ] **Étape 1 : Ajouter les nouvelles props à l'interface et au composant**

Modifier l'interface `Props` :

```tsx
interface Props {
    draft: { id: number; status: string; generated_text: string };
    partner_name: string | null;
    has_shared: boolean;
    partner_has_shared: boolean;
    partner_vows_readable: boolean;
}
```

Modifier la signature du composant :

```tsx
export default function VoeuxPreview({ draft, partner_name, has_shared, partner_has_shared, partner_vows_readable }: Props) {
```

- [ ] **Étape 2 : Ajouter le bloc de partage après `<VowsInsights />`**

Ajouter avant le `<div className="flex flex-wrap gap-4 mt-8">` existant :

```tsx
{/* Bloc partage */}
<div className="mt-8 border border-stone-200 rounded-xl p-5 bg-stone-50">
    <h3 className="text-sm font-semibold text-stone-600 mb-3">Partage des vœux</h3>

    {partner_vows_readable ? (
        <div className="space-y-2">
            <p className="text-sm text-green-700">Les vœux de votre partenaire sont disponibles.</p>
            {partner_name && (
                <Link
                    href={route('voeux.partner')}
                    className="inline-block px-4 py-2 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700 transition-colors"
                >
                    Lire les vœux de {partner_name}
                </Link>
            )}
        </div>
    ) : (
        <div className="space-y-2">
            {!has_shared ? (
                <button
                    onClick={() => {
                        if (window.confirm('Partager vos vœux permettra à votre partenaire de les lire dès qu\'il·elle aura également partagé les siens. Continuer ?')) {
                            router.post(route('voeux.share'));
                        }
                    }}
                    className="px-4 py-2 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700 transition-colors"
                >
                    Partager mes vœux
                </button>
            ) : (
                <p className="text-sm text-amber-700">✓ Vous avez partagé vos vœux.</p>
            )}
            <p className="text-xs text-stone-400">
                {partner_has_shared
                    ? `${partner_name ?? 'Votre partenaire'} a déjà partagé ses vœux.`
                    : `En attente du partage de ${partner_name ?? 'votre partenaire'}.`
                }
            </p>
        </div>
    )}
</div>
```

- [ ] **Étape 3 : Commit**

```bash
git add resources/js/Pages/Voeux/Preview.tsx
git commit -m "feat: UI de partage des vœux sur la page d'aperçu"
```

---

### Task 10 : Lien dans `Dashboard.tsx`

**Files:**
- Modify: `resources/js/Pages/Dashboard.tsx`

- [ ] **Étape 1 : Ajouter `partner_vows_readable` et `partner_name` aux Props**

Modifier l'interface `Props` :

```tsx
interface Props {
    couple: CoupleData | null;
    vows_progress: VowsProgressData | null;
    partner_vows_readable: boolean;
    partner_name: string | null;
}
```

Modifier la signature du composant :

```tsx
export default function Dashboard({ couple, vows_progress, partner_vows_readable, partner_name }: Props) {
```

- [ ] **Étape 2 : Ajouter le lien dans le bloc "Mes vœux" (statut completed)**

Dans le bloc `vows_progress.status === 'completed'`, après le lien "Voir mes vœux", ajouter :

```tsx
{partner_vows_readable && partner_name && (
    <Link
        href={route('voeux.partner')}
        className="px-4 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 text-sm"
    >
        Lire les vœux de {partner_name}
    </Link>
)}
```

- [ ] **Étape 3 : Lancer la suite complète**

```bash
make tests
```

Résultat attendu : tous les tests passent

- [ ] **Étape 4 : Commit final**

```bash
git add resources/js/Pages/Dashboard.tsx
git commit -m "feat: lien vers les vœux du partenaire sur le dashboard"
```

---

## Récapitulatif des commits attendus

1. `feat: route POST /voeux/regenerate pour régénérer les vœux`
2. `feat: bouton de régénération des vœux sur la page d'aperçu`
3. `feat: colonne shared_at et méthode isReadableByPartner sur VowsDraft`
4. `feat: policy viewPartner pour la lecture des vœux du partenaire`
5. `feat: routes share et partner, contrôleurs VowsController::share et partner`
6. `feat: preview passe les props de partage, dashboard passe partner_vows_readable`
7. `feat: page lecture seule des vœux du partenaire (Voeux/Partner)`
8. `feat: UI de partage des vœux sur la page d'aperçu`
9. `feat: lien vers les vœux du partenaire sur le dashboard`
