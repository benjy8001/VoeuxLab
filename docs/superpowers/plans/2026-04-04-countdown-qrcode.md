# Countdown & QR Code d'Invitation — Plan d'implémentation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ajouter un countdown live (J/H/M/S) et un QR code d'invitation sur le Dashboard des époux authentifiés.

**Architecture:** Deux composants React autonomes (`WeddingCountdown`, `InvitationQRCode`) intégrés dans le Dashboard existant. La page Join est mise à jour pour lire un code pré-rempli depuis un paramètre URL (`?code=`). Aucun nouveau endpoint Laravel, aucune migration.

**Tech Stack:** React, TypeScript, `react-qr-code`, Inertia.js, Pest PHP (tests backend uniquement)

---

## Structure des fichiers

| Fichier | Action | Rôle |
|---------|--------|------|
| `resources/js/Components/WeddingCountdown.tsx` | Créer | Countdown live J/H/M/S avec "C'est le grand jour !" si date passée |
| `resources/js/Components/InvitationQRCode.tsx` | Créer | QR code SVG vers l'URL join pré-remplie |
| `resources/js/Pages/Dashboard.tsx` | Modifier | Intégrer les 2 composants, ajouter `app_url` et `ceremony_date_iso` dans Props |
| `resources/js/Pages/Couple/Join.tsx` | Modifier | Lire la prop `initial_code` pour pré-remplir le formulaire |
| `app/Http/Controllers/DashboardController.php` | Modifier | Ajouter `app_url` et `ceremony_date_iso` dans les props Inertia |
| `app/Http/Controllers/CoupleController.php` | Modifier | Passer `initial_code` depuis `?code=` dans `join()` |
| `tests/Feature/DashboardTest.php` | Modifier | Vérifier `app_url` et `ceremony_date_iso` dans les props |
| `tests/Feature/CoupleTest.php` | Modifier | Vérifier `initial_code` dans les props de la page join |

---

## Task 1 : Installer react-qr-code

**Files:**
- Modify: `package.json`

- [ ] **Étape 1 : Installer la dépendance**

```bash
npm install react-qr-code
```

Expected : `added 1 package`, pas d'erreur.

- [ ] **Étape 2 : Vérifier l'installation**

```bash
grep react-qr-code package.json
```

Expected : `"react-qr-code": "^x.x.x"` présent.

- [ ] **Étape 3 : Committer**

```bash
git add package.json package-lock.json
git commit -m "chore: ajouter react-qr-code"
```

---

## Task 2 : Pré-remplir le code d'invitation dans la page Join

**Files:**
- Modify: `app/Http/Controllers/CoupleController.php:53-56`
- Modify: `resources/js/Pages/Couple/Join.tsx`
- Test: `tests/Feature/CoupleTest.php`

- [ ] **Étape 1 : Écrire les tests échouants**

Ajouter à la fin de `tests/Feature/CoupleTest.php` :

```php
test('join page pré-remplit le code depuis le paramètre URL', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('couple.join', ['code' => 'ABCD1234']))
        ->assertInertia(fn ($page) => $page
            ->component('Couple/Join')
            ->where('initial_code', 'ABCD1234')
        );
});

test('join page initial_code est null si pas de paramètre code', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('couple.join'))
        ->assertInertia(fn ($page) => $page
            ->component('Couple/Join')
            ->where('initial_code', null)
        );
});
```

- [ ] **Étape 2 : Lancer les tests pour vérifier l'échec**

```bash
make tests -- --filter="join page"
```

Expected : FAIL — prop `initial_code` introuvable.

- [ ] **Étape 3 : Mettre à jour CoupleController::join()**

Dans `app/Http/Controllers/CoupleController.php`, remplacer :

```php
public function join(): Response
{
    return Inertia::render('Couple/Join');
}
```

Par :

```php
public function join(Request $request): Response
{
    return Inertia::render('Couple/Join', [
        'initial_code' => $request->query('code'),
    ]);
}
```

- [ ] **Étape 4 : Lancer les tests pour vérifier qu'ils passent**

```bash
make tests -- --filter="join page"
```

Expected : 2 tests PASS.

- [ ] **Étape 5 : Mettre à jour Join.tsx**

Remplacer le contenu entier de `resources/js/Pages/Couple/Join.tsx` :

```tsx
import { useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

interface Props {
    initial_code?: string | null;
}

export default function CoupleJoin({ initial_code }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        invitation_code: initial_code ?? '',
    });

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Rejoindre un couple
                </h2>
            }
        >
            <Head title="Rejoindre un couple" />
            <div className="max-w-md mx-auto py-8 text-center">
                <h1 className="text-3xl font-serif text-stone-800 mb-4">
                    Rejoindre votre partenaire
                </h1>
                <p className="text-stone-500 mb-8">
                    Entrez le code d'invitation partagé par votre partenaire.
                </p>
                <form onSubmit={(e) => { e.preventDefault(); post(route('couple.attach')); }}>
                    <input
                        type="text"
                        value={data.invitation_code}
                        onChange={(e) => setData('invitation_code', e.target.value.toUpperCase())}
                        placeholder="XXXXXXXX"
                        maxLength={8}
                        className="w-full text-center text-2xl tracking-widest border border-stone-300 rounded-lg p-4 mb-4 uppercase"
                    />
                    {errors.invitation_code && (
                        <p className="text-red-500 text-sm mb-4">{errors.invitation_code}</p>
                    )}
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-amber-600 text-white py-3 rounded-lg hover:bg-amber-700 disabled:opacity-50"
                    >
                        Rejoindre
                    </button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
```

- [ ] **Étape 6 : Committer**

```bash
git add app/Http/Controllers/CoupleController.php resources/js/Pages/Couple/Join.tsx tests/Feature/CoupleTest.php
git commit -m "feat: pré-remplir le code d'invitation dans la page join via paramètre URL"
```

---

## Task 3 : Mettre à jour DashboardController

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`
- Test: `tests/Feature/DashboardTest.php`

- [ ] **Étape 1 : Écrire le test échouant**

Ajouter à la fin de `tests/Feature/DashboardTest.php` :

```php
test('dashboard inclut app_url et ceremony_date_iso dans les props', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $user->id,
        'ceremony_date' => '2026-09-12',
    ]);
    $user->update(['couple_id' => $couple->id]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('app_url', config('app.url'))
            ->where('couple.ceremony_date_iso', '2026-09-12')
        );
});
```

- [ ] **Étape 2 : Lancer le test pour vérifier l'échec**

```bash
make tests -- --filter="dashboard inclut app_url"
```

Expected : FAIL — `app_url` absent des props.

- [ ] **Étape 3 : Mettre à jour DashboardController**

Dans `app/Http/Controllers/DashboardController.php`, remplacer le `return Inertia::render(...)` par :

```php
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
```

- [ ] **Étape 4 : Lancer tous les tests DashboardTest pour vérifier**

```bash
make tests -- --filter="DashboardTest"
```

Expected : tous PASS.

- [ ] **Étape 5 : Committer**

```bash
git add app/Http/Controllers/DashboardController.php tests/Feature/DashboardTest.php
git commit -m "feat: ajouter app_url et ceremony_date_iso aux props Inertia du Dashboard"
```

---

## Task 4 : Créer WeddingCountdown.tsx

**Files:**
- Create: `resources/js/Components/WeddingCountdown.tsx`

- [ ] **Étape 1 : Créer le composant**

Créer `resources/js/Components/WeddingCountdown.tsx` :

```tsx
import { useEffect, useState } from 'react';

interface Props {
    ceremonyDate: string; // format ISO: "YYYY-MM-DD"
}

interface TimeLeft {
    days: number;
    hours: number;
    minutes: number;
    seconds: number;
}

function calculateTimeLeft(ceremonyDate: string): TimeLeft | null {
    const diff = new Date(ceremonyDate).getTime() - Date.now();

    if (diff <= 0) return null;

    return {
        days: Math.floor(diff / (1000 * 60 * 60 * 24)),
        hours: Math.floor((diff / (1000 * 60 * 60)) % 24),
        minutes: Math.floor((diff / (1000 * 60)) % 60),
        seconds: Math.floor((diff / 1000) % 60),
    };
}

export default function WeddingCountdown({ ceremonyDate }: Props) {
    const [timeLeft, setTimeLeft] = useState<TimeLeft | null>(
        () => calculateTimeLeft(ceremonyDate)
    );

    useEffect(() => {
        const timer = setInterval(() => {
            setTimeLeft(calculateTimeLeft(ceremonyDate));
        }, 1000);

        return () => clearInterval(timer);
    }, [ceremonyDate]);

    if (!timeLeft) {
        return (
            <p className="text-amber-700 font-semibold text-sm mt-2">
                C'est le grand jour !
            </p>
        );
    }

    return (
        <div className="mt-3">
            <p className="text-xs text-stone-500 mb-2">Compte à rebours</p>
            <div className="flex gap-3">
                {[
                    { value: timeLeft.days, label: 'j' },
                    { value: timeLeft.hours, label: 'h' },
                    { value: timeLeft.minutes, label: 'min' },
                    { value: timeLeft.seconds, label: 's' },
                ].map(({ value, label }) => (
                    <div key={label} className="text-center">
                        <span className="text-2xl font-bold text-amber-700 font-mono">
                            {String(value).padStart(2, '0')}
                        </span>
                        <p className="text-xs text-stone-400">{label}</p>
                    </div>
                ))}
            </div>
        </div>
    );
}
```

- [ ] **Étape 2 : Committer**

```bash
git add resources/js/Components/WeddingCountdown.tsx
git commit -m "feat: composant WeddingCountdown — countdown live J/H/M/S"
```

---

## Task 5 : Créer InvitationQRCode.tsx

**Files:**
- Create: `resources/js/Components/InvitationQRCode.tsx`

- [ ] **Étape 1 : Créer le composant**

Créer `resources/js/Components/InvitationQRCode.tsx` :

```tsx
import QRCode from 'react-qr-code';

interface Props {
    invitationCode: string;
    appUrl: string;
}

export default function InvitationQRCode({ invitationCode, appUrl }: Props) {
    const joinUrl = `${appUrl}/couple/join?code=${invitationCode}`;

    return (
        <>
            <div className="flex justify-center my-3">
                <QRCode
                    value={joinUrl}
                    size={160}
                    style={{ height: 'auto', maxWidth: '100%', width: '100%' }}
                    viewBox="0 0 256 256"
                />
            </div>
            <p className="font-mono text-xl tracking-widest text-amber-700 font-bold text-center">
                {invitationCode}
            </p>
        </>
    );
}
```

- [ ] **Étape 2 : Committer**

```bash
git add resources/js/Components/InvitationQRCode.tsx
git commit -m "feat: composant InvitationQRCode — QR code SVG vers URL join pré-remplie"
```

---

## Task 6 : Intégrer les composants dans Dashboard.tsx

**Files:**
- Modify: `resources/js/Pages/Dashboard.tsx`

- [ ] **Étape 1 : Ajouter les imports en haut du fichier**

Après la ligne `import ProgressBar from '@/Components/ProgressBar';`, ajouter :

```tsx
import WeddingCountdown from '@/Components/WeddingCountdown';
import InvitationQRCode from '@/Components/InvitationQRCode';
```

- [ ] **Étape 2 : Mettre à jour l'interface CoupleData**

Remplacer l'interface `CoupleData` existante par :

```tsx
interface CoupleData {
    invitation_code: string;
    is_full: boolean;
    spouse1_name: string;
    spouse2_name: string | null;
    ceremony_date: string | null;
    ceremony_date_iso: string | null;
    ceremony_location: string | null;
}
```

- [ ] **Étape 3 : Mettre à jour l'interface Props**

Remplacer l'interface `Props` existante par :

```tsx
interface Props {
    couple: CoupleData | null;
    vows_progress: VowsProgressData | null;
    partner_vows_readable: boolean;
    partner_name: string | null;
    app_url: string;
}
```

- [ ] **Étape 4 : Mettre à jour la signature de la fonction**

Remplacer :

```tsx
export default function Dashboard({ couple, vows_progress, partner_vows_readable, partner_name }: Props) {
```

Par :

```tsx
export default function Dashboard({ couple, vows_progress, partner_vows_readable, partner_name, app_url }: Props) {
```

- [ ] **Étape 5 : Remplacer le bloc code d'invitation par InvitationQRCode**

Localiser et remplacer le bloc `!couple.is_full` (autour de la ligne 71) :

```tsx
{!couple.is_full && (
    <div className="mt-4 bg-stone-50 rounded-lg p-3">
        <p className="text-xs text-stone-500 mb-1">Code d'invitation à partager</p>
        <p className="font-mono text-xl tracking-widest text-amber-700 font-bold">
            {couple.invitation_code}
        </p>
    </div>
)}
```

Par :

```tsx
{!couple.is_full && (
    <div className="mt-4 bg-stone-50 rounded-lg p-3">
        <p className="text-xs text-stone-500 mb-1">Code d'invitation à partager</p>
        <InvitationQRCode
            invitationCode={couple.invitation_code}
            appUrl={app_url}
        />
    </div>
)}
```

- [ ] **Étape 6 : Ajouter WeddingCountdown après la date de cérémonie**

Localiser le bloc d'affichage de `couple.ceremony_date` :

```tsx
{couple.ceremony_date && (
    <p className="text-stone-500 text-sm mt-1">📅 {couple.ceremony_date}</p>
)}
```

Le remplacer par :

```tsx
{couple.ceremony_date && (
    <p className="text-stone-500 text-sm mt-1">📅 {couple.ceremony_date}</p>
)}
{couple.ceremony_date_iso && (
    <WeddingCountdown ceremonyDate={couple.ceremony_date_iso} />
)}
```

- [ ] **Étape 7 : Vérifier la compilation TypeScript**

```bash
npm run build
```

Expected : compilation sans erreur TypeScript.

- [ ] **Étape 8 : Lancer tous les tests backend**

```bash
make tests
```

Expected : tous PASS.

- [ ] **Étape 9 : Committer**

```bash
git add resources/js/Pages/Dashboard.tsx
git commit -m "feat: intégrer WeddingCountdown et InvitationQRCode dans le Dashboard"
```
