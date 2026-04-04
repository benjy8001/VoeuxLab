# Design : Stepper cliquable dans le questionnaire vœux

Date : 2026-04-04

## Contexte

Le questionnaire de vœux (`Voeux/Index.tsx`) comporte 14 étapes navigables uniquement de façon séquentielle (Précédent / Suivant). L'objectif est d'ajouter un stepper visuel permettant de sauter directement à n'importe quelle étape, avec sauvegarde intelligente de la réponse en cours.

---

## Décisions

| Question | Choix |
|---|---|
| Sauvegarde au clic | Uniquement si la réponse a été modifiée depuis le dernier autosave (`isDirty` flag) |
| Accès aux étapes futures | Oui, avec indicateur visuel distinct ("empty") |
| Forme visuelle | Barre horizontale de numéros, tooltip natif au survol |

---

## Section 1 : Logique de navigation (`Voeux/Index.tsx`)

### Nouveau state

```tsx
const [isDirty, setIsDirty] = useState(false);
```

- Passe à `true` dans `handleChange` (chaque frappe utilisateur)
- Repasse à `false` dans le callback de l'autosave après POST réussi
- Repasse à `false` après un flush manuel

### Nouvelle fonction `navigateTo(targetStep: number)`

Appelée par le stepper uniquement. Les boutons Précédent/Suivant continuent d'utiliser `saveAndGoTo()` (comportement inchangé — sauvegarde systématique).

```
si isDirty :
    autoSave.flush()
    router.post(voeux.answer, { ..., current_step: targetStep, final: false },
        { preserveState: false })
sinon :
    router.post(voeux.answer, { ..., current_step: targetStep, final: false },
        { preserveState: false })
```

Note : même si non-dirty, on envoie quand même un POST pour mettre à jour `current_step` côté serveur (cohérence avec l'existant). La différence est qu'on ne flush pas l'autosave en attente inutilement.

### Remplacement du `<ProgressBar>`

`<ProgressBar current={step} total={total} />` est remplacé par `<StepperBar ... />`. Le stepper remplit les deux rôles (navigation + progression visuelle). `ProgressBar.tsx` reste dans le projet car il est utilisé sur le Dashboard.

---

## Section 2 : Nouveau composant `StepperBar.tsx`

**Fichier :** `resources/js/Components/StepperBar.tsx`

### Props

```tsx
interface Props {
    questions: { key: string; order: number; label: string }[];
    currentStep: number;
    answers: Record<string, string>;
    onNavigate: (step: number) => void;
}
```

### États visuels

| État | Condition | Style Tailwind |
|---|---|---|
| `current` | `order === currentStep` | `bg-amber-600 text-white ring-2 ring-amber-300 ring-offset-1` |
| `answered` | `answers[key]` non vide et `order !== currentStep` | `bg-amber-600 text-white` |
| `empty` | pas de réponse et `order !== currentStep` | `bg-stone-200 text-stone-500 opacity-60` |

Tous les états sont cliquables (`<button type="button">`).

### Tooltip

Attribut HTML natif `title={question.label}` sur chaque bouton. Pas de dépendance externe.

### Rendu

```tsx
<div className="flex flex-wrap gap-1.5 mb-6">
    {questions.map((q) => {
        const isAnswered = !!answers[q.key];
        const isCurrent = q.order === currentStep;
        // calcul de className selon état
        return (
            <button
                key={q.key}
                type="button"
                title={q.label}
                onClick={() => onNavigate(q.order)}
                className={...}
            >
                {q.order}
            </button>
        );
    })}
</div>
```

### Responsive

`flex flex-wrap gap-1.5` — les pastilles passent à la ligne sur mobile. Chaque bouton : `w-8 h-8` (32px), texte centré.

---

## Ce qui ne change pas

- `saveAndGoTo()` et les boutons Précédent/Suivant — comportement identique
- `autoSave` debounce 2s — inchangé
- `CitationBank` — inchangé
- `ProgressBar.tsx` — conservé pour le Dashboard

---

## Tests à écrire

- `StepperBar` affiche le bon nombre de boutons
- État `current` sur l'étape active
- État `answered` sur une étape avec réponse
- État `empty` sur une étape sans réponse
- `onNavigate` appelé avec le bon numéro d'étape au clic
- `isDirty` : navigateTo flush si dirty, ne flush pas si clean
