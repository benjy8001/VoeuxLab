interface Props {
    current: number;
    total: number;
}

export default function ProgressBar({ current, total }: Props) {
    const percent = Math.round((current / total) * 100);

    return (
        <div className="w-full">
            <div className="flex justify-between text-xs text-stone-400 mb-1">
                <span>Question {current} sur {total}</span>
                <span>{percent}%</span>
            </div>
            <div className="w-full bg-stone-200 rounded-full h-1.5">
                <div
                    className="bg-amber-600 h-1.5 rounded-full transition-all duration-500"
                    style={{ width: `${percent}%` }}
                />
            </div>
        </div>
    );
}
