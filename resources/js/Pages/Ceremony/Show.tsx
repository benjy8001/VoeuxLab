import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { router } from '@inertiajs/react';
import {
    DndContext,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
    type DragEndEvent,
} from '@dnd-kit/core';
import {
    arrayMove,
    SortableContext,
    sortableKeyboardCoordinates,
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { useState, useRef, useCallback, useEffect } from 'react';

// Types des blocs du programme
interface ProgramBlock {
    id: string;
    type: 'entrance' | 'welcome' | 'reading' | 'vows' | 'exchange' | 'speech' | 'music' | 'moment' | 'exit' | 'custom';
    title: string;
    time: string | null;
    duration_minutes: number | null;
    description: string | null;
    notes_officiant: string | null;
}

interface CeremonyProps {
    id: number;
    program: ProgramBlock[];
    notes_officiant: string | null;
    status: string;
    couple_id: number;
}

interface CoupleProps {
    id: number;
    spouse1?: { id: number; name: string };
    spouse2?: { id: number; name: string } | null;
    ceremony_date?: string | null;
    ceremony_location?: string | null;
}

interface Props extends PageProps {
    ceremony: CeremonyProps;
    couple: CoupleProps;
    role: 'spouse' | 'officiant';
}

// Labels et couleurs des types de blocs
const BLOCK_TYPE_LABELS: Record<ProgramBlock['type'], string> = {
    entrance: 'Entrée',
    welcome: 'Accueil',
    reading: 'Lecture',
    vows: 'Vœux',
    exchange: 'Échange',
    speech: 'Discours',
    music: 'Musique',
    moment: 'Moment',
    exit: 'Sortie',
    custom: 'Personnalisé',
};

const BLOCK_TYPE_COLORS: Record<ProgramBlock['type'], string> = {
    entrance: 'bg-blue-100 text-blue-700',
    welcome: 'bg-green-100 text-green-700',
    reading: 'bg-purple-100 text-purple-700',
    vows: 'bg-amber-100 text-amber-700',
    exchange: 'bg-rose-100 text-rose-700',
    speech: 'bg-orange-100 text-orange-700',
    music: 'bg-teal-100 text-teal-700',
    moment: 'bg-indigo-100 text-indigo-700',
    exit: 'bg-gray-100 text-gray-700',
    custom: 'bg-stone-100 text-stone-700',
};

// Badge de type de bloc
function BlockTypeBadge({ type }: { type: ProgramBlock['type'] }) {
    return (
        <span className={`inline-block px-2 py-0.5 rounded text-xs font-medium ${BLOCK_TYPE_COLORS[type]}`}>
            {BLOCK_TYPE_LABELS[type]}
        </span>
    );
}

// Informations communes d'un bloc (type, titre, temps, durée, description)
function BlockInfo({ block }: { block: ProgramBlock }) {
    return (
        <div className="flex-1 min-w-0">
            <div className="flex items-center gap-2 mb-1 flex-wrap">
                <BlockTypeBadge type={block.type} />
                {block.time && (
                    <span className="text-xs text-stone-400">{block.time}</span>
                )}
                {block.duration_minutes !== null && (
                    <span className="text-xs text-stone-400">{block.duration_minutes} min</span>
                )}
            </div>
            <p className="font-medium text-stone-800">{block.title}</p>
            {block.description && (
                <p className="text-sm text-stone-500 mt-1">{block.description}</p>
            )}
        </div>
    );
}

// ---- Vue Époux : bloc draggable ----

interface SortableCeremonyBlockProps {
    block: ProgramBlock;
    onDelete: (id: string) => void;
}

function SortableCeremonyBlock({ block, onDelete }: SortableCeremonyBlockProps) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: block.id });

    return (
        <div
            ref={setNodeRef}
            style={{ transform: CSS.Transform.toString(transform), transition }}
            className={`group relative bg-white border rounded-xl p-5 transition-shadow flex items-start gap-3 ${
                isDragging
                    ? 'shadow-xl border-amber-300 z-10 opacity-90'
                    : 'border-stone-200 hover:border-stone-300'
            }`}
        >
            {/* Poignée de glissement */}
            <div
                {...attributes}
                {...listeners}
                className="mt-1 text-stone-300 cursor-grab active:cursor-grabbing opacity-0 group-hover:opacity-100 transition-opacity touch-none select-none shrink-0"
                aria-label="Réordonner ce bloc"
            >
                ⠿
            </div>

            <BlockInfo block={block} />

            {/* Bouton supprimer */}
            <button
                type="button"
                onClick={() => onDelete(block.id)}
                className="shrink-0 mt-1 px-2 py-1 text-xs text-red-500 border border-red-200 rounded hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100"
                aria-label={`Supprimer le bloc "${block.title}"`}
            >
                Supprimer
            </button>
        </div>
    );
}

// Formulaire d'ajout de bloc
const BLOCK_TYPES: ProgramBlock['type'][] = [
    'entrance', 'welcome', 'reading', 'vows', 'exchange',
    'speech', 'music', 'moment', 'exit', 'custom',
];

interface AddBlockFormData {
    title: string;
    type: ProgramBlock['type'];
    time: string;
    duration_minutes: string;
    description: string;
}

interface AddBlockFormProps {
    onCancel: () => void;
}

function AddBlockForm({ onCancel }: AddBlockFormProps) {
    const [form, setForm] = useState<AddBlockFormData>({
        title: '',
        type: 'custom',
        time: '',
        duration_minutes: '',
        description: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!form.title.trim()) return;

        router.post(route('ceremony.blocks.add'), {
            title: form.title.trim(),
            type: form.type,
            time: form.time || null,
            duration_minutes: form.duration_minutes ? parseInt(form.duration_minutes, 10) : null,
            description: form.description.trim() || null,
        });
        onCancel();
    };

    return (
        <form
            onSubmit={handleSubmit}
            className="bg-white border border-amber-200 rounded-xl p-5 space-y-3"
        >
            <h3 className="font-semibold text-stone-700 text-sm">Nouveau bloc</h3>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {/* Titre */}
                <div className="sm:col-span-2">
                    <label className="block text-xs text-stone-500 mb-1" htmlFor="block-title">
                        Titre <span className="text-red-400">*</span>
                    </label>
                    <input
                        id="block-title"
                        type="text"
                        required
                        value={form.title}
                        onChange={(e) => setForm((f) => ({ ...f, title: e.target.value }))}
                        className="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400"
                        placeholder="Ex. Entrée des mariés"
                    />
                </div>

                {/* Type */}
                <div>
                    <label className="block text-xs text-stone-500 mb-1" htmlFor="block-type">
                        Type
                    </label>
                    <select
                        id="block-type"
                        value={form.type}
                        onChange={(e) => setForm((f) => ({ ...f, type: e.target.value as ProgramBlock['type'] }))}
                        className="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400"
                    >
                        {BLOCK_TYPES.map((t) => (
                            <option key={t} value={t}>{BLOCK_TYPE_LABELS[t]}</option>
                        ))}
                    </select>
                </div>

                {/* Heure */}
                <div>
                    <label className="block text-xs text-stone-500 mb-1" htmlFor="block-time">
                        Heure
                    </label>
                    <input
                        id="block-time"
                        type="text"
                        value={form.time}
                        onChange={(e) => setForm((f) => ({ ...f, time: e.target.value }))}
                        className="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400"
                        placeholder="Ex. 15h30"
                    />
                </div>

                {/* Durée */}
                <div>
                    <label className="block text-xs text-stone-500 mb-1" htmlFor="block-duration">
                        Durée (minutes)
                    </label>
                    <input
                        id="block-duration"
                        type="number"
                        min="1"
                        value={form.duration_minutes}
                        onChange={(e) => setForm((f) => ({ ...f, duration_minutes: e.target.value }))}
                        className="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400"
                        placeholder="Ex. 10"
                    />
                </div>

                {/* Description */}
                <div className="sm:col-span-2">
                    <label className="block text-xs text-stone-500 mb-1" htmlFor="block-description">
                        Description
                    </label>
                    <textarea
                        id="block-description"
                        rows={2}
                        value={form.description}
                        onChange={(e) => setForm((f) => ({ ...f, description: e.target.value }))}
                        className="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400 resize-none"
                        placeholder="Détails optionnels…"
                    />
                </div>
            </div>

            <div className="flex gap-2 justify-end">
                <button
                    type="button"
                    onClick={onCancel}
                    className="px-4 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 text-sm"
                >
                    Annuler
                </button>
                <button
                    type="submit"
                    className="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm"
                >
                    Ajouter
                </button>
            </div>
        </form>
    );
}

// ---- Vue Époux ----

function SpouseView({ ceremony }: { ceremony: CeremonyProps }) {
    const [blocks, setBlocks] = useState<ProgramBlock[]>(ceremony.program);
    const [showAddForm, setShowAddForm] = useState(false);

    const sensors = useSensors(
        useSensor(PointerSensor),
        useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
    );

    const handleDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;
        if (!over || active.id === over.id) return;

        const oldIndex = blocks.findIndex((b) => b.id === active.id);
        const newIndex = blocks.findIndex((b) => b.id === over.id);
        const newBlocks = arrayMove(blocks, oldIndex, newIndex);

        setBlocks(newBlocks);
        // Envoyer le nouvel ordre au serveur
        router.put(route('ceremony.blocks.update'), { blocks: newBlocks } as unknown as Parameters<typeof router.put>[1]);
    };

    const handleDelete = (blockId: string) => {
        if (!window.confirm('Supprimer ce bloc définitivement ?')) return;
        router.delete(route('ceremony.blocks.remove', blockId));
    };

    return (
        <div className="space-y-4">
            <DndContext
                sensors={sensors}
                collisionDetection={closestCenter}
                onDragEnd={handleDragEnd}
            >
                <SortableContext
                    items={blocks.map((b) => b.id)}
                    strategy={verticalListSortingStrategy}
                >
                    <div className="space-y-3">
                        {blocks.length === 0 && (
                            <p className="text-stone-400 text-sm text-center py-6">
                                Aucun bloc pour l'instant. Ajoutez-en un ci-dessous.
                            </p>
                        )}
                        {blocks.map((block) => (
                            <SortableCeremonyBlock
                                key={block.id}
                                block={block}
                                onDelete={handleDelete}
                            />
                        ))}
                    </div>
                </SortableContext>
            </DndContext>

            {showAddForm ? (
                <AddBlockForm onCancel={() => setShowAddForm(false)} />
            ) : (
                <button
                    type="button"
                    onClick={() => setShowAddForm(true)}
                    className="w-full py-3 border-2 border-dashed border-stone-300 text-stone-400 rounded-xl hover:border-amber-400 hover:text-amber-600 transition-colors text-sm"
                >
                    + Ajouter un bloc
                </button>
            )}
        </div>
    );
}

// ---- Vue Officiant ----

interface OfficiantBlockProps {
    block: ProgramBlock;
}

function OfficiantBlock({ block }: OfficiantBlockProps) {
    const [notes, setNotes] = useState(block.notes_officiant ?? '');
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const handleNotesChange = useCallback((value: string) => {
        setNotes(value);

        // Debounce 1 seconde avant l'envoi
        if (debounceRef.current) {
            clearTimeout(debounceRef.current);
        }
        debounceRef.current = setTimeout(() => {
            router.patch(route('ceremony.blocks.notes', block.id), {
                notes_officiant: value,
            });
        }, 1000);
    }, [block.id]);

    // Cleanup du debounce au démontage du composant
    useEffect(() => {
        return () => {
            if (debounceRef.current) {
                clearTimeout(debounceRef.current);
            }
        };
    }, []);

    return (
        <div className="bg-white border border-stone-200 rounded-xl p-5 space-y-3">
            <BlockInfo block={block} />

            {/* Notes officiant */}
            <div>
                <label
                    className="block text-xs text-stone-500 mb-1"
                    htmlFor={`notes-${block.id}`}
                >
                    Notes officiant·e
                </label>
                <textarea
                    id={`notes-${block.id}`}
                    rows={3}
                    value={notes}
                    onChange={(e) => handleNotesChange(e.target.value)}
                    className="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm text-stone-700 focus:outline-none focus:border-amber-400 resize-none bg-stone-50"
                    placeholder="Vos notes personnelles pour ce moment…"
                />
            </div>
        </div>
    );
}

function OfficiantView({ ceremony }: { ceremony: CeremonyProps }) {
    return (
        <div className="space-y-4">
            {ceremony.program.length === 0 && (
                <p className="text-stone-400 text-sm text-center py-6">
                    Aucun bloc dans le programme.
                </p>
            )}
            {ceremony.program.map((block) => (
                <OfficiantBlock key={block.id} block={block} />
            ))}
        </div>
    );
}

// ---- Page principale ----

export default function CeremonyShow({ ceremony, couple, role }: Props) {
    // Construction du titre du couple
    const spouseName1 = couple.spouse1?.name ?? '';
    const spouseName2 = couple.spouse2?.name ?? null;
    const coupleTitle = spouseName2
        ? `${spouseName1} & ${spouseName2}`
        : spouseName1;

    return (
        <AuthenticatedLayout
            header={
                <h2 className="font-semibold text-xl text-gray-800">
                    Programme de cérémonie
                </h2>
            }
        >
            <div className="max-w-2xl mx-auto px-4 py-8 space-y-6">
                {/* En-tête couple */}
                <div className="bg-white border border-stone-200 rounded-xl p-6">
                    <h1 className="text-2xl font-serif text-stone-800 mb-1">{coupleTitle}</h1>
                    {couple.ceremony_date && (
                        <p className="text-stone-500 text-sm">
                            📅 {couple.ceremony_date}
                        </p>
                    )}
                    {couple.ceremony_location && (
                        <p className="text-stone-500 text-sm mt-0.5">
                            📍 {couple.ceremony_location}
                        </p>
                    )}
                    <p className="text-xs text-stone-400 mt-2">
                        {role === 'officiant' ? 'Vue officiant·e' : 'Vue époux·se'}
                    </p>
                </div>

                {/* Corps de la page selon le rôle */}
                {role === 'spouse' ? (
                    <SpouseView ceremony={ceremony} />
                ) : (
                    <OfficiantView ceremony={ceremony} />
                )}
            </div>
        </AuthenticatedLayout>
    );
}
