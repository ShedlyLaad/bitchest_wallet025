<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class UniversalMailService
{
    /**
     * Configure dynamiquement le mailer SMTP pour un email donné
     * Utilise toujours la configuration MAIL_* du fichier .env pour fonctionner avec tous les domaines d'email
     */
    public function configureMailerForEmail(string $toEmail): void
    {
        // Utiliser toujours la configuration du .env pour supporter tous les domaines d'email
        Config::set('mail.mailers.smtp.host', env('MAIL_HOST', 'smtp.mailtrap.io'));
        Config::set('mail.mailers.smtp.port', env('MAIL_PORT', 587));
        Config::set('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', 'tls'));
        Config::set('mail.mailers.smtp.username', env('MAIL_USERNAME'));
        Config::set('mail.mailers.smtp.password', env('MAIL_PASSWORD'));
    }

    /**
     * Envoie un email en configurant automatiquement le mailer
     */
    public function send($mailable, string $toEmail): void
    {
        // Configurer le mailer pour cet email
        $this->configureMailerForEmail($toEmail);
        
        // Envoyer l'email
        Mail::to($toEmail)->send($mailable);
    }

    /**
     * Vérifie si un email est valide
     */
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

