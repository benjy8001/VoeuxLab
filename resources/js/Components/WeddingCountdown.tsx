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
