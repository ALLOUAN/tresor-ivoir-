<?php

namespace App\Enums;

enum InformationPageSlug: string
{
    case About = 'about';
    case UserGuide = 'user-guide';
    case Faq = 'faq';
    case LegalNotice = 'legal-notice';
    case PrivacyPolicy = 'privacy-policy';

    public function label(): string
    {
        return match ($this) {
            self::About => 'À propos',
            self::UserGuide => 'Guide d\'utilisation',
            self::Faq => 'FAQ',
            self::LegalNotice => 'Mentions légales',
            self::PrivacyPolicy => 'Politique de confidentialité',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::About => 'Présentation du projet et de la mission du site.',
            self::UserGuide => 'Aide à la navigation et aux fonctionnalités du back-office.',
            self::Faq => 'Questions fréquentes des utilisateurs et administrateurs.',
            self::LegalNotice => 'Éditeur, hébergeur, propriété intellectuelle.',
            self::PrivacyPolicy => 'Données personnelles, cookies et droits des utilisateurs.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::About => 'fa-circle-info',
            self::UserGuide => 'fa-book-open',
            self::Faq => 'fa-circle-question',
            self::LegalNotice => 'fa-scale-balanced',
            self::PrivacyPolicy => 'fa-shield-halved',
        };
    }

    /** @return list<self> */
    public static function ordered(): array
    {
        return [
            self::About,
            self::UserGuide,
            self::Faq,
            self::LegalNotice,
            self::PrivacyPolicy,
        ];
    }
}
