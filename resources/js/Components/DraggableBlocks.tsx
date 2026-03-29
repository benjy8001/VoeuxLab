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
import { useState } from 'react';

interface Block {
    id: string;
    text: string;
}

interface Props {
    initialText: string;
    onChange: (newText: string) => void;
}

function SortableBlock({ block }: { block: Block }) {
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
            className={`group relative bg-white border rounded-xl p-5 transition-shadow ${
                isDragging
                    ? 'shadow-xl border-amber-300 z-10 opacity-90'
                    : 'border-stone-200 hover:border-stone-300'
            }`}
        >
            {/* Poignée de glissement */}
            <div
                {...attributes}
                {...listeners}
                className="absolute left-3 top-1/2 -translate-y-1/2 text-stone-300 cursor-grab active:cursor-grabbing opacity-0 group-hover:opacity-100 transition-opacity touch-none select-none"
                aria-label="Réordonner ce bloc"
            >
                ⠿
            </div>
            <p className="text-stone-700 leading-relaxed whitespace-pre-wrap pl-5">{block.text}</p>
        </div>
    );
}

export default function DraggableBlocks({ initialText, onChange }: Props) {
    const toBlocks = (text: string): Block[] =>
        text
            .split('\n\n')
            .filter((t) => t.trim() !== '')
            .map((text, i) => ({ id: `block-${i}`, text: text.trim() }));

    const [blocks, setBlocks] = useState<Block[]>(() => toBlocks(initialText));

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
        onChange(newBlocks.map((b) => b.text).join('\n\n'));
    };

    return (
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
                    {blocks.map((block) => (
                        <SortableBlock key={block.id} block={block} />
                    ))}
                </div>
            </SortableContext>
        </DndContext>
    );
}
