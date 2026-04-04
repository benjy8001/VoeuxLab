<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    /**
     * Signature de la commande Artisan.
     *
     * @var string
     */
    protected $signature = 'admin:grant {email : L\'adresse e-mail de l\'utilisateur à promouvoir}';

    /**
     * Description de la commande.
     *
     * @var string
     */
    protected $description = 'Accorde le rôle administrateur à un utilisateur existant';

    /**
     * Exécute la commande : cherche l'utilisateur et lui attribue le rôle admin.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Aucun utilisateur trouvé avec l'adresse : {$email}");

            return self::FAILURE;
        }

        if ($user->isAdmin()) {
            $this->warn("L'utilisateur {$user->name} est déjà administrateur.");

            return self::SUCCESS;
        }

        $user->update(['role' => 'admin']);

        $this->info("L'utilisateur {$user->name} ({$email}) est maintenant administrateur.");

        return self::SUCCESS;
    }
}
