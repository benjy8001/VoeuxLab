# Design : Section Q&R dans Preview et PDF

**Date :** 2026-04-07
**Scope :** Affichage des questions et réponses du parcours vœux dans la page preview et l'export PDF

---

## Contexte

La page preview (`Voeux/Preview`) affiche uniquement le texte des vœux générés (blocs glissables).
L'export PDF (`pdf/vows.blade.php`) affiche uniquement le texte généré + le nom de l'auteur.

L'utilisateur souhaite retrouver, sous les vœux générés, un récapitulatif de ses réponses aux 14 questions — chaque question affichée avant sa réponse, dans l'ordre du parcours, avec les variantes selon le ton choisi.

---

## Approche retenue

**Section Q&R simple en bas**, sans interactivité supplémentaire. La section apparaît dans les deux surfaces (preview React et PDF Blade). Les questions sans réponse sont ignorées.

---

## Données

### VowsController::preview()
- Déjà : `$draft->with(['answers', ...])`
- Ajouter : `questions` → `VowsQuestions::forTone($draft->tone ?? 'balanced')`
- Ajouter : `answers` → `$draft->answers->pluck('answer_text', 'question_key')`

### ExportController::vows()
- Ajouter : `->with('answers')` au chargement du draft
- Passer à la vue : `questions` et `answers` (même format que ci-dessus)

---

## Preview React (`Voeux/Preview.tsx`)

### Props supplémentaires
```ts
questions: { key: string; label: string }[];
answers: Record<string, string>;
```

### Rendu
Sous `<VowsInsights>`, avant le bloc partage :

```
[Titre] Mes réponses

Pour chaque question dans questions (ordre du parcours) :
  - Si answers[question.key] existe :
    - <p> label de la question (style : italic, gris clair)
    - <p> texte de la réponse
```

Pas de composant séparé — inline dans `VoeuxPreview`.

---

## PDF Blade (`resources/views/pdf/vows.blade.php`)

### Données reçues
- `$questions` : array `[['key' => ..., 'label' => ...], ...]`
- `$answers` : collection ou tableau clé → texte

### Rendu
Après `<div class="content">`, une nouvelle section :

```html
<div class="qa-section">
  <div class="qa-title">Mes réponses</div>
  @foreach($questions as $q)
    @if(!empty($answers[$q['key']]))
      <div class="qa-item">
        <p class="qa-question">{{ $q['label'] }}</p>
        <p class="qa-answer">{{ $answers[$q['key']] }}</p>
      </div>
    @endif
  @endforeach
</div>
```

Styles CSS inline dans le `<style>` existant :
- `.qa-section` : séparateur en haut, margin-top
- `.qa-title` : serif, taille légèrement réduite, couleur dorée comme `.header h1`
- `.qa-question` : italique, gris clair
- `.qa-answer` : normal, couleur corps

---

## Ce qui ne change pas

- Logique de génération des vœux
- Structure de drag & drop des blocs
- Partage, régénération, édition
- Politique d'accès (Gate)

---

## Tests

Pas de nouveaux tests nécessaires — les données sont déjà couvertes par les tests existants (`VowsTest`, `VowsPolicyTest`). L'ajout est purement de présentation.
