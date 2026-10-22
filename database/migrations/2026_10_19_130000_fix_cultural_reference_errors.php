<?php

use App\Models\CulturalDomain;
use App\Models\CulturalElement;
use App\Models\TouristCity;
use Database\Seeders\Data\CulturalCorrections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige des erreurs factuelles du référentiel culturel d'origine (voir CulturalCorrections) :
 *  - fausses mentions UNESCO (échasses dan, kora, épopée de Soundjata, masque Tanglé) ;
 *  - attiéké : inscrit en 2024 (et non « candidature ») ;
 *  - rattachements : Zaouli → Gouro, Abissa → N'Zima, Ananse → peuples akan ;
 *  - sources ciblées pour les éléments qui n'en avaient pas ;
 *  - suppression d'un domaine culturel parasite (« A - Résumé de la leçon… », sans contenu) ;
 *  - Yamoussoukro : district autonome, et non région des Lacs ;
 *  - fiches Dan (pas d'inscription UNESCO) et Adjoukrou (Dabou ; l'Abissa est n'zima).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cultural_elements')) {
            return;
        }

        foreach (CulturalCorrections::elements() as $slug => $fix) {
            $element = CulturalElement::withTrashed()->where('slug', $slug)->first();
            if ($element) {
                $element->forceFill(CulturalCorrections::apply($slug, []))->save();
            }
        }

        if (Schema::hasColumn('cultural_elements', 'sources')) {
            foreach (CulturalCorrections::sources() as $slug => $sources) {
                $element = CulturalElement::withTrashed()->where('slug', $slug)->first();
                if ($element && empty($element->sources)) {
                    $element->forceFill(['sources' => $sources])->save();
                }
            }
        }

        foreach (CulturalCorrections::peoples() as $slug => $fix) {
            \App\Models\CulturalPeople::where('slug', $slug)->first()?->forceFill($fix)->save();
        }

        // Domaine parasite : supprimé seulement s'il est vide (aucun élément, aucun sous-domaine).
        $junk = CulturalDomain::where('slug', 'a-resume-de-la-lecon-et-exercices-de-fixation')->first();
        if ($junk && ! CulturalElement::withTrashed()->where('domain_id', $junk->id)->exists()
            && ! CulturalDomain::where('parent_id', $junk->id)->exists()) {
            $junk->delete();
        }

        TouristCity::where('slug', 'yamoussoukro')->where('region_administrative', 'Lacs')
            ->update(['region_administrative' => 'District autonome de Yamoussoukro']);
    }

    public function down(): void
    {
        // Corrections de contenu : pas de retour arrière vers des données erronées.
    }
};
