<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class UniversalMailService
{
    /**
     * Configure dynamiquement le mailer SMTP pour un email donné.
     * Utilise toujours la configuration MAIL_* pour fonctionner avec tous les domaines d'email.
     */
    public function configureMailerForEmail(string $toEmail): void
    {
        Config::set('mail.mailers.smtp.host', env('MAIL_HOST', config('mail.mailers.smtp.host')));
        Config::set('mail.mailers.smtp.port', env('MAIL_PORT', config('mail.mailers.smtp.port')));
        Config::set('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption')));
        Config::set('mail.mailers.smtp.username', env('MAIL_USERNAME', config('mail.mailers.smtp.username')));
        Config::set('mail.mailers.smtp.password', env('MAIL_PASSWORD', config('mail.mailers.smtp.password')));

        // Forcer la recréation des transports avec la config qu'on vient de poser.
        Mail::purge('smtp');
        Mail::purge('failover');
    }

    /**
     * Envoie un email en configurant automatiquement le mailer.
     *
     * Ne lève jamais d'exception : si le transport principal échoue (limite d'envoi
     * SMTP, serveur mail injoignable...), l'email est écrit dans les logs via le
     * mailer "log" pour ne jamais bloquer le flux métier (inscription, validation...).
     *
     * @return bool  true si l'email est réellement parti, false s'il a basculé sur le log.
     */
    public function send($mailable, string $toEmail): bool
    {
        $this->configureMailerForEmail($toEmail);

        $primary = config('mail.default', 'smtp');

        try {
            Mail::mailer($primary)->to($toEmail)->send($mailable);
            return true;
        } catch (\Throwable $e) {
            Log::warning('Mail transport failed, falling back to log mailer', [
                'to' => $toEmail,
                'mailer' => $primary,
                'error' => $e->getMessage(),
            ]);

            try {
                Mail::mailer('log')->to($toEmail)->send($mailable);
            } catch (\Throwable $inner) {
                Log::error('Log mailer fallback also failed', [
                    'to' => $toEmail,
                    'error' => $inner->getMessage(),
                ]);
            }

            return false;
        }
    }

    /**
     * Vérifie si un email est syntaxiquement valide.
     */
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
