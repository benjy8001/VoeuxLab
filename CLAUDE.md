# Projet : App Cérémonie Laïque — Vœux de Mariage

## Stack
- Laravel 13 avec Inertia.js + React + TypeScript
- Tailwind CSS 4
- MariaDB
- Sanctum pour l'authentification (fourni par Breeze)

## Conventions
- Nommer migrations, modèles, contrôleurs en anglais
- Commenter le code en français
- Suivre les conventions Laravel (Form Requests, Policies)
- Tests avec Pest PHP

## Architecture
- Modèles : User, Couple, VowsDraft, VowsAnswer, Ceremony
- Les vœux sont privés par défaut (VowsDraftPolicy stricte)
- Sauvegarde automatique des réponses (debounce 2s côté React)
- Le brouillon est généré côté serveur par VowsGeneratorService
- Contrôleurs retournent Inertia::render() — pas de JSON API

## Règles métier
- Un User appartient à un seul Couple
- Chaque époux a un seul VowsDraft par couple
- Parcours de 14 questions, progression sauvegardée dans current_step
- Les vœux d'un époux ne sont jamais exposés à l'autre

## Setup Docker
- PHP-FPM + Nginx + MariaDB + Redis
- Toutes les commandes via Makefile (make start, make tests, etc.)
