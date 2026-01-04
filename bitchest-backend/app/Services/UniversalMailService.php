<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class UniversalMailService
{
    /**
     * Configuration SMTP pour différents fournisseurs d'email
     */
    private const SMTP_CONFIGS = [
        'gmail.com' => [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
        ],
        'outlook.com' => [
            'host' => 'smtp-mail.outlook.com',
            'port' => 587,
            'encryption' => 'tls',
        ],
        'hotmail.com' => [
            'host' => 'smtp-mail.outlook.com',
            'port' => 587,
            'encryption' => 'tls',
        ],
        'live.com' => [
            'host' => 'smtp-mail.outlook.com',
            'port' => 587,
            'encryption' => 'tls',
        ],
        'yahoo.com' => [
            'host' => 'smtp.mail.yahoo.com',
            'port' => 587,
            'encryption' => 'tls',
        ],
        'ymail.com' => [
            'host' => 'smtp.mail.yahoo.com',
            'port' => 587,
            'encryption' => 'tls',
        ],
        'aol.com' => [
            'host' => 'smtp.aol.com',
            'port' => 587,
            'encryption' => 'tls',
        ],
        'protonmail.com' => [
            'host' => '127.0.0.1',
            'port' => 1025,
            'encryption' => null,
        ],
        'proton.me' => [
            'host' => '127.0.0.1',
            'port' => 1025,
            'encryption' => null,
        ],
    ];

    /**
     * Configuration SMTP par défaut (générique)
     */
    private const DEFAULT_SMTP = [
        'host' => null, // Sera défini depuis .env
        'port' => 587,
        'encryption' => 'tls',
    ];

    /**
     * Détecte le domaine d'un email
     */
    private function getEmailDomain(string $email): string
    {
        $parts = explode('@', $email);
        return strtolower($parts[1] ?? '');
    }

    /**
     * Obtient la configuration SMTP pour un domaine donné
     */
    private function getSmtpConfigForDomain(string $domain): array
    {
        // Vérifier si on a une configuration spécifique pour ce domaine
        if (isset(self::SMTP_CONFIGS[$domain])) {
            return self::SMTP_CONFIGS[$domain];
        }

        // Pour les domaines personnalisés, utiliser la configuration par défaut depuis .env
        return [
            'host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        ];
    }

    /**
     * Configure dynamiquement le mailer SMTP pour un email donné
     * Cette méthode configure le mailer pour utiliser les bons paramètres SMTP
     * basés sur le domaine de l'email de destination
     * 
     * Si MAIL_HOST est défini dans .env, il sera utilisé pour tous les emails (configuration manuelle)
     * Sinon, le service détecte automatiquement le serveur SMTP selon le domaine de destination
     */
    public function configureMailerForEmail(string $toEmail): void
    {
        // Si MAIL_HOST est défini dans .env, utiliser cette configuration pour tous les emails
        // C'est la méthode recommandée : configurer un serveur SMTP unique dans .env
        if (env('MAIL_HOST')) {
            Config::set('mail.mailers.smtp.host', env('MAIL_HOST'));
            Config::set('mail.mailers.smtp.port', env('MAIL_PORT', 587));
            Config::set('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', 'tls'));
        } else {
            // Sinon, détecter automatiquement le serveur SMTP selon le domaine
            $domain = $this->getEmailDomain($toEmail);
            $smtpConfig = $this->getSmtpConfigForDomain($domain);
            
            Config::set('mail.mailers.smtp.host', $smtpConfig['host'] ?? 'smtp.mailtrap.io');
            Config::set('mail.mailers.smtp.port', $smtpConfig['port'] ?? 587);
            Config::set('mail.mailers.smtp.encryption', $smtpConfig['encryption'] ?? 'tls');
        }
        
        // Les credentials viennent toujours de .env (universel)
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

