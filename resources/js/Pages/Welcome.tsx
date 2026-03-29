import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function Welcome({ auth }: PageProps) {
    return (
        <>
            <Head title="Vœux de cérémonie laïque">
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link
                    rel="stylesheet"
                    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400;1,600&display=swap"
                />
            </Head>

            <div className="min-h-screen bg-stone-50 text-stone-800">

                {/* Header */}
                <header className="flex items-center justify-between px-6 py-5 max-w-4xl mx-auto">
                    <span className="font-['Cormorant_Garamond'] italic text-xl text-amber-700 tracking-wide">
                        ✦ Vœux
                    </span>
                    <nav className="flex gap-3 text-sm">
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
                <main className="max-w-3xl mx-auto px-6 py-24 text-center">
                    <p className="text-amber-600 text-xs tracking-[0.3em] uppercase mb-8">
                        Cérémonie laïque
                    </p>
                    <h1 className="font-['Cormorant_Garamond'] font-light text-5xl md:text-6xl text-stone-800 leading-tight mb-8">
                        Écrivez vos vœux<br />
                        <em className="text-amber-700 not-italic font-['Cormorant_Garamond'] italic">avec le cœur</em>
                    </h1>
                    <p className="text-stone-500 text-lg leading-relaxed mb-12 max-w-xl mx-auto">
                        Un parcours guidé de 14 questions pour vous aider à mettre des mots
                        sur ce que vous ressentez — à votre rythme, en toute confidentialité.
                    </p>

                    {auth.user ? (
                        <Link
                            href={route('dashboard')}
                            className="inline-block px-10 py-4 bg-amber-600 text-white rounded-xl text-base hover:bg-amber-700 transition-colors shadow-sm font-['Cormorant_Garamond'] text-lg tracking-wide"
                        >
                            Reprendre mes vœux →
                        </Link>
                    ) : (
                        <Link
                            href={route('register')}
                            className="inline-block px-10 py-4 bg-amber-600 text-white rounded-xl text-base hover:bg-amber-700 transition-colors shadow-sm font-['Cormorant_Garamond'] text-lg tracking-wide"
                        >
                            Commencer gratuitement →
                        </Link>
                    )}
                </main>

                {/* Comment ça marche */}
                <section className="max-w-4xl mx-auto px-6 py-20 border-t border-stone-200">
                    <p className="text-center font-['Cormorant_Garamond'] italic text-3xl text-stone-700 mb-16">
                        Comment ça marche
                    </p>
                    <div className="grid md:grid-cols-3 gap-12">
                        {[
                            {
                                num: '01',
                                title: 'Créez votre espace',
                                desc: 'Inscrivez-vous et créez votre espace couple. Partagez le code d\'invitation à votre partenaire.',
                            },
                            {
                                num: '02',
                                title: 'Répondez aux questions',
                                desc: '14 questions guidées pour explorer vos souvenirs, vos promesses et vos espoirs — à votre rythme.',
                            },
                            {
                                num: '03',
                                title: 'Téléchargez votre PDF',
                                desc: 'Vos vœux mis en page avec élégance, prêts à être relus, répétés ou partagés.',
                            },
                        ].map(({ num, title, desc }) => (
                            <div key={num} className="text-center">
                                <div className="font-['Cormorant_Garamond'] font-light text-8xl text-amber-100 leading-none mb-4 select-none">
                                    {num}
                                </div>
                                <h3 className="font-['Cormorant_Garamond'] text-xl text-stone-800 mb-3">
                                    {title}
                                </h3>
                                <p className="text-stone-500 text-sm leading-relaxed">
                                    {desc}
                                </p>
                            </div>
                        ))}
                    </div>
                </section>

                {/* Confidentialité */}
                <section className="bg-amber-50 border-y border-amber-100">
                    <div className="max-w-2xl mx-auto px-6 py-20 text-center">
                        <div className="text-amber-300 text-2xl tracking-[0.5em] mb-8 select-none">
                            ✦ ✦ ✦
                        </div>
                        <h2 className="font-['Cormorant_Garamond'] italic text-3xl md:text-4xl text-stone-800 mb-6">
                            Vos mots, rien que les vôtres
                        </h2>
                        <p className="text-stone-500 leading-relaxed text-base max-w-lg mx-auto">
                            Chaque époux rédige ses vœux en privé, dans son propre espace.
                            Personne — pas même votre partenaire — ne peut lire vos mots
                            sans votre accord explicite. La surprise du grand jour est préservée.
                        </p>
                    </div>
                </section>

                {/* Footer */}
                <footer className="text-center py-8 text-stone-400 text-sm">
                    Fait avec amour ✦ {new Date().getFullYear()}
                </footer>

            </div>
        </>
    );
}
