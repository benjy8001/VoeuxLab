// Vitesse de lecture orale typique pour une cérémonie
const WORDS_PER_MINUTE = 130;

interface Props {
    text: string;
}

export default function ReadingTimer({ text }: Props) {
    const wordCount = text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
    const totalSeconds = Math.round((wordCount / WORDS_PER_MINUTE) * 60);
    const mins = Math.floor(totalSeconds / 60);
    const secs = totalSeconds % 60;

    const duration = mins > 0
        ? `${mins} min${secs > 0 ? ` ${secs} s` : ''}`
        : `${secs} s`;

    return (
        <div className="flex items-center gap-4 text-sm text-stone-400 py-3 border-t border-stone-200 mt-6">
            <span>{wordCount} mot{wordCount !== 1 ? 's' : ''}</span>
            <span>·</span>
            <span>~{duration} de lecture</span>
        </div>
    );
}
