<?php

namespace App\Services;

class ConversationContentGuardService
{
    /**
     * Analyse non bloquante d'un message : le contenu est toujours envoyé,
     * seul un signalement (is_flagged + raisons) et un avertissement à
     * l'expéditeur sont produits.
     *
     * @return array{flagged: bool, reasons: array<int, string>}
     */
    public function scan(string $body): array
    {
        $reasons = [];

        if (preg_match('/\bhttps?:\/\/\S+/i', $body) || preg_match('/\bwww\.\S+/i', $body)) {
            $reasons[] = 'lien_externe';
        }

        if (preg_match('/\b\+?\d[\d\s\-.]{7,}\d\b/', $body)) {
            $reasons[] = 'numero_telephone';
        }

        $lower = mb_strtolower($body);

        foreach ((array) config('chat.payment_bypass_keywords', []) as $keyword) {
            if ($keyword !== '' && str_contains($lower, mb_strtolower($keyword))) {
                $reasons[] = 'contournement_paiement';
                break;
            }
        }

        foreach ((array) config('chat.prohibited_words', []) as $word) {
            if ($word !== '' && str_contains($lower, mb_strtolower($word))) {
                $reasons[] = 'contenu_interdit';
                break;
            }
        }

        $reasons = array_values(array_unique($reasons));

        return [
            'flagged' => $reasons !== [],
            'reasons' => $reasons,
        ];
    }

    public function warningMessageFor(array $reasons): ?string
    {
        if ($reasons === []) {
            return null;
        }

        if (in_array('contournement_paiement', $reasons, true) || in_array('numero_telephone', $reasons, true) || in_array('lien_externe', $reasons, true)) {
            return 'Pour votre sécurité, effectuez vos paiements exclusivement via la plateforme. Évitez de communiquer vos coordonnées bancaires ou de convenir d\'un contact en dehors de la plateforme.';
        }

        return 'Votre message a été signalé pour vérification par notre équipe.';
    }
}
