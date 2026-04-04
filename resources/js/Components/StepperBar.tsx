interface Question {
    key: string;
    order: number;
    label: string;
}

interface Props {
    questions: Question[];
    currentStep: number;
    answers: Record<string, string>;
    onNavigate: (step: number) => void;
}

export default function StepperBar({ questions, currentStep, answers, onNavigate }: Props) {
    return (
        <div className="flex flex-wrap gap-1.5 mb-6">
            {questions.map((q) => {
                const isCurrent  = q.order === currentStep;
                const isAnswered = !isCurrent && !!answers[q.key];

                const className = [
                    'w-8 h-8 rounded-full text-sm font-medium flex items-center justify-center transition-all',
                    isCurrent
                        ? 'bg-amber-600 text-white ring-2 ring-amber-300 ring-offset-1'
                        : isAnswered
                            ? 'bg-amber-600 text-white'
                            : 'bg-stone-200 text-stone-500 opacity-60',
                ].join(' ');

                return (
                    <button
                        key={q.key}
                        type="button"
                        title={q.label}
                        onClick={() => onNavigate(q.order)}
                        className={className}
                    >
                        {q.order}
                    </button>
                );
            })}
        </div>
    );
}
