<?php

namespace App\Enums;

enum PartnershipType: string
{
    case Educational = 'educational';
    case Government = 'government';
    case Misc = 'misc';
    case Technology = 'technology';
    case Ngo = 'ngo';

    public function label(): string
    {
        return match ($this) {
            self::Educational => 'Éducatif',
            self::Government => 'Gouvernement',
            self::Misc => 'Divers',
            self::Technology => 'Technologie',
            self::Ngo => 'ONG',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Educational => 'bg-blue-900/50 text-blue-200 border-blue-700/50',
            self::Government => 'bg-red-900/40 text-red-200 border-red-800/50',
            self::Misc => 'bg-amber-900/40 text-amber-200 border-amber-800/50',
            self::Technology => 'bg-slate-800 text-slate-200 border-slate-600',
            self::Ngo => 'bg-slate-700/60 text-slate-300 border-slate-600',
        };
    }

    /** @return array<string, string> slug => label */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }
}
