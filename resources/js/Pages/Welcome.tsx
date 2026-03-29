import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function Welcome({ auth }: PageProps) {
    return (
        <>
            <Head title="Vœux de cérémonie laïque" />

            <div className="min-h-screen bg-stone-50 text-stone-800">
                {/* Header */}
                <header className="flex items-center justify-between px-6 py-4 max-w-4xl mx-auto">
                    <span className="font-serif text-xl text-amber-700">✦ Vœux</span>
                    <nav className="flex gap-4 text-sm">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors"
                            >
                                Mon espace
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={route('login')}
                                    className="px-4 py-2 text-stone-600 border border-stone-300 rounded-lg hover:bg-stone-100 transition-colors"
                                >
                                    Connexion
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors"
                                >
                                    Commencer
                                </Link>
                            </>
                        )}
                    </nav>
                </header>

                {/* Hero */}
                <main className="max-w-3xl mx-auto px-6 py-20 text-center">
                    <p className="text-amber-600 text-sm tracking-widest uppercase mb-6">
                        Cérémonie laïque
                    </p>
                    <h1 className="font-serif text-5xl md:text-6xl text-stone-800 leading-tight mb-6">
                        Écrivez vos vœux<br />
                        <span className="text-amber-700 italic">avec le cœur</span>
                    </h1>
                    <p className="text-stone-500 text-lg leading-relaxed mb-10 max-w-xl mx-auto">
                        Un parcours guidé de 14 questions pour vous aider à mettre des mots
                        sur ce que vous ressentez — à votre rythme, en toute confidentialité.
                    </p>

                    {auth.user ? (
                        <Link
                            href={route('dashboard')}
                            className="inline-block px-8 py-4 bg-amber-600 text-white rounded-xl text-lg hover:bg-amber-700 transition-colors shadow-sm"
                        >
                            Reprendre mes vœux →
                        </Link>
                    ) : (
                        <Link
                            href={route('register')}
                            className="inline-block px-8 py-4 bg-amber-600 text-white rounded-xl text-lg hover:bg-amber-700 transition-colors shadow-sm"
                        >
                            Commencer gratuitement →
                        </Link>
                    )}
                </main>

                {/* Features */}
                <section className="max-w-4xl mx-auto px-6 py-16 grid md:grid-cols-3 gap-8 border-t border-stone-200">
                    <div className="text-center">
                        <div className="text-3xl mb-4">💬</div>
                        <h3 className="font-serif text-xl mb-2">14 questions guidées</h3>
                        <p className="text-stone-500 text-sm leading-relaxed">
                            Des questions qui vous aident à explorer vos souvenirs, vos promesses et vos espoirs.
                        </p>
                    </div>
                    <div className="text-center">
                        <div className="text-3xl mb-4">🔒</div>
                        <h3 className="font-serif text-xl mb-2">Confidentialité totale</h3>
                        <p className="text-stone-500 text-sm leading-relaxed">
                            Chaque époux rédige ses vœux en privé. Personne ne peut lire vos mots sans votre accord.
                        </p>
                    </div>
                    <div className="text-center">
                        <div className="text-3xl mb-4">📄</div>
                        <h3 className="font-serif text-xl mb-2">Export PDF élégant</h3>
                        <p className="text-stone-500 text-sm leading-relaxed">
                            Téléchargez vos vœux mis en page pour les relire, les répéter ou les partager.
                        </p>
                    </div>
                </section>

                {/* Footer */}
                <footer className="text-center py-8 text-stone-400 text-sm border-t border-stone-200">
                    Fait avec amour ✦ {new Date().getFullYear()}
                </footer>
            </div>
        </>
    );
}
