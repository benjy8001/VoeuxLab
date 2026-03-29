import { useState, useCallback } from 'react';
import { router, Head } from '@inertiajs/react';
import debounce from 'lodash/debounce';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ProgressBar from '@/Components/ProgressBar';

interface Question {
    key: string;
    order: number;
    label: string;
    placeholder: string;
    hint: string;
}

interface Props {
    draft: { id: number; current_step: number; status: string };
    questions: Question[];
    answers: Record<string, string>;
}

export default function OfficiantIndex({ draft, questions, answers: initialAnswers }: Props) {
    const [step, setStep] = useState(draft.current_step);
    const [localAnswers, setLocalAnswers] = useState<Record<string, string>>(initialAnswers);
    const total = questions.length;
    const question = questions[step - 1];

    const autoSave = useCallback(
        debounce((key: string, text: string, currentStep: number) => {
            router.post(
                route('officiant.answer'),
                { question_key: key, answer_text: text, current_step: currentStep, final: false },
                { preserveState: true, preserveScroll: true, replace: true }
            );
        }, 2000),
        []
    );

    const handleChange = (value: string) => {
        setLocalAnswers((prev) => ({ ...prev, [question.key]: value }));
        autoSave(question.key, value, step);
    };

    const saveAndGoTo = (newStep: number) => {
        const prev = step;
        setStep(newStep);
        autoSave.flush();
        router.post(
            route('officiant.answer'),
            { question_key: question.key, answer_text: localAnswers[question.key] ?? '', current_step: newStep, final: false },
            { preserveState: false, onError: () => setStep(prev) }
        );
    };

    const handleFinish = () => {
        autoSave.flush();
        router.post(route('officiant.answer'), {
            question_key: question.key,
            answer_text: localAnswers[question.key] ?? '',
            current_step: step,
            final: true,
        });
    };

    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl text-gray-800">Discours de cérémonie</h2>}>
            <Head title="Discours de cérémonie" />
            <div className="max-w-2xl mx-auto px-4 py-8">
                <ProgressBar current={step} total={total} />
                <h2 className="font-['Cormorant_Garamond'] text-2xl text-stone-800 mt-8 mb-6">
                    {question.label}
                </h2>
                <textarea
                    value={localAnswers[question.key] ?? ''}
                    onChange={(e) => handleChange(e.target.value)}
                    placeholder={question.placeholder}
                    rows={8}
                    className="w-full p-4 border border-stone-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:outline-none resize-none text-stone-700"
                />
                {question.hint && (
                    <p className="text-sm text-stone-400 mt-2 italic">{question.hint}</p>
                )}
                <div className="flex justify-between mt-8">
                    <button
                        onClick={() => saveAndGoTo(step - 1)}
                        disabled={step === 1}
                        className="px-6 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 disabled:opacity-30 transition-colors"
                    >
                        Précédent
                    </button>
                    {step < total ? (
                        <button
                            onClick={() => saveAndGoTo(step + 1)}
                            className="px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors"
                        >
                            Suivant
                        </button>
                    ) : (
                        <button
                            onClick={handleFinish}
                            className="px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors"
                        >
                            Voir mon discours →
                        </button>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
