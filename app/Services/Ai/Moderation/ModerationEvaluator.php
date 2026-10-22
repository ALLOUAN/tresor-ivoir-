<?php

namespace App\Services\Ai\Moderation;

use Illuminate\Support\Facades\DB;

/**
 * Évaluation de la modération assistée.
 *  1. Jeu de test SYNTHÉTIQUE de textes étiquetés (problématiques / sains) : précision, rappel, F1.
 *  2. Jeu de test d'images GÉNÉRÉES (nettes, floues, sombres, surexposées, petites, doublon).
 *  3. Décisions réelles de l'administrateur (confirmé / écarté) quand il y en a.
 * Les jeux de test sont écrits par le développeur : ils vérifient que les règles fonctionnent,
 * ils ne remplacent pas une évaluation sur de vrais contenus relus par un humain.
 */
class ModerationEvaluator
{
    public function __construct(private readonly TextAnalyzer $text, private readonly ImageAnalyzer $images) {}

    /** @return array<int,array{kind:string,title:string,text:string,bad:bool,why:string}> */
    public static function textCases(): array
    {
        $long = str_repeat('La côte ivoirienne offre des plages de sable fin, des lagunes paisibles et une cuisine généreuse que les visiteurs apprécient beaucoup. ', 12);
        $bad = fn ($kind, $title, $text, $why) => compact('kind', 'title', 'text', 'why') + ['bad' => true];
        $ok = fn ($kind, $title, $text, $why) => compact('kind', 'title', 'text', 'why') + ['bad' => false];

        return [
            $bad('review', 'Nul', 'Quel connard ce gérant, une vraie ordure, ne venez jamais ici', 'injures'),
            $bad('review', 'Arnaque', 'Allez sur mon site pour gagner de l argent facile, investissement garanti, cliquez ici http://bit.ly/xyz', 'spam + lien'),
            $bad('review', 'Super', 'Super endroit, contactez-moi sur whatsapp au +225 07 00 00 00 00 pour un paiement direct moins cher', 'contournement'),
            $bad('review', 'GENIAL', 'TOUT EST VRAIMENT PARFAIT ET JE RECOMMANDE A TOUT LE MONDE SANS AUCUNE HESITATION VENEZ VITE', 'majuscules'),
            $bad('review', 'Ok', 'qsdqsd fghjkl wxcvbn zxcvbn mlkjhg poiuyt', 'charabia'),
            $bad('review', 'Promo', 'Visitez http://promo-a.com et http://promo-b.net et http://promo-c.xyz maintenant', 'liens multiples'),
            $bad('review', 'bien', 'bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien bien', 'répétition'),
            $bad('article', 'Test', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. '.str_repeat('Lorem ipsum dolor sit amet. ', 25), 'texte de remplissage'),
            $bad('article', 'Casino', 'Jouez au casino en ligne dès maintenant et gagnez de l argent. '.$long, 'spam'),
            $bad('article', 'Brève', 'Un article très court sans intérêt.', 'trop court'),
            $bad('article', 'Pari', 'Les paris sportifs et le bitcoin vous rendront riche, investissement garanti à 100% gratuit. '.$long, 'spam multiple'),
            $bad('provider', 'Chez moi', 'resto', 'description minuscule'),
            $bad('provider', 'Promo', 'Notre agence est la meilleure. Cliquez ici pour une offre exclusive 100% gratuit sans risque pour vous.', 'formules de spam'),
            $bad('provider', 'Hôtel', 'xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx xxxxx', 'remplissage'),
            $bad('event', 'Soirée', 'Soirée sexe et porno gratuit, rencontre coquine garantie ce week-end à Abidjan pour tous les curieux de la ville.', 'contenu interdit'),
            $bad('review', 'Salope', 'Cette salope de réceptionniste est une pute', 'injures'),
            $bad('review', 'Nul!!!!!!!', 'horrible!!!!!!!!!!!!!!!!!! jamais plus', 'caractères répétés'),
            $bad('article', 'Crypto', 'Investissez dans le bitcoin et la crypto dès aujourd hui avec notre plateforme. '.$long, 'spam crypto'),

            $ok('review', 'Très bon séjour', 'Séjour très agréable, personnel accueillant et chambre propre. Le petit déjeuner était copieux, je recommande.', 'avis normal'),
            $ok('review', 'Belle table', 'Le kedjenou était délicieux et le service rapide. Nous reviendrons avec plaisir lors de notre prochain passage à Abidjan.', 'avis normal'),
            $ok('review', 'Correct', 'Bien situé mais un peu bruyant la nuit. Rapport qualité prix correct.', 'avis nuancé'),
            $ok('review', 'Excellent', 'Excellent accueil à Grand-Bassam, la plage est magnifique et l hôtel très bien tenu. Merci à toute l équipe !', 'avis enthousiaste'),
            $ok('review', 'Décevant', 'Chambre un peu défraîchie et climatisation bruyante, mais le personnel a été très gentil et a changé notre chambre.', 'avis critique poli'),
            $ok('article', 'Le kedjenou', $long, 'article normal'),
            $ok('article', 'Grand-Bassam', 'Classée au patrimoine mondial de l UNESCO, la ville de Grand-Bassam séduit par son quartier colonial. '.$long, 'sigle UNESCO'),
            $ok('article', 'Où dormir', 'Notre sélection d hôtels de Cocody, dont certains proposent un casino et une piscine, pour tous les budgets. '.$long, 'casino dans un hôtel'),
            $ok('article', 'Source', 'Pour en savoir plus, consultez le site officiel https://www.unesco.org de l organisation. '.$long, 'un lien légitime'),
            $ok('provider', 'Maquis du Plateau', 'Maquis familial au cœur d Abidjan, spécialités de poisson braisé et d alloco. Réservations au +225 07 00 00 00 00, ouvert tous les jours.', 'fiche avec téléphone'),
            $ok('provider', 'Hôtel Ivoire', 'Hôtel de standing de 300 chambres avec piscine, spa, casino et plusieurs restaurants, face à la lagune Ébrié, à dix minutes du centre.', 'hôtel avec casino'),
            $ok('provider', 'Atelier Aya', 'Atelier de tissage de pagnes traditionnels où l on découvre les gestes des artisans et achète des créations uniques.', 'fiche normale'),
            $ok('event', 'Festival des Masques', 'Trois jours de danses, de masques et de musiques traditionnelles dans la région de Man, avec les villages voisins.', 'événement'),
            $ok('review', 'OK', 'Très bien, merci.', 'avis très court mais correct'),
            $ok('review', 'Magnifique', 'Magnifique cascade, le guide était passionné. Un des plus beaux souvenirs de mon voyage en Côte d Ivoire !', 'avis normal'),
            $ok('article', 'Gastronomie', 'La cuisine ivoirienne se découvre dans les maquis. Voici où manger de l attiéké et du poisson braisé à Abidjan. '.$long, 'article normal'),
            $ok('event', 'Nuit de l Ivoire', 'Soirée de gala avec concert, danse et dîner, dans un cadre élégant au bord de la lagune, ouverte à tous.', 'événement'),
            $ok('provider', 'Taxi Abidjan', 'Service de transport avec chauffeur, transferts depuis l aéroport et excursions à la journée dans les environs d Abidjan et de Bassam.', 'fiche normale'),
        ];
    }

    /** @return array<string,mixed> */
    public function run(): array
    {
        return ['text' => $this->runText(), 'images' => $this->runImages(), 'decisions' => $this->decisions(), 'real' => $this->realStats()];
    }

    private function runText(): array
    {
        $tp = $fp = $fn = $tn = 0;
        $misses = [];
        $thr = (int) config('ai.moderation.review_threshold');
        foreach (self::textCases() as $c) {
            $res = $this->text->analyze($c['title'].' . '.$c['text'], ['kind' => $c['kind']]);
            $flagged = $res['score'] >= $thr;
            match (true) {
                $c['bad'] && $flagged => $tp++,
                $c['bad'] && ! $flagged => $fn++,
                ! $c['bad'] && $flagged => $fp++,
                default => $tn++,
            };
            if ($c['bad'] !== $flagged) {
                $misses[] = ['kind' => $c['kind'], 'title' => $c['title'], 'why' => $c['why'], 'expected' => $c['bad'] ? 'à signaler' : 'sain', 'score' => $res['score'], 'flags' => array_keys($res['flags'])];
            }
        }
        $precision = ($tp + $fp) ? $tp / ($tp + $fp) : 1.0;
        $recall = ($tp + $fn) ? $tp / ($tp + $fn) : 1.0;

        return [
            'cases' => $tp + $fp + $fn + $tn, 'tp' => $tp, 'fp' => $fp, 'fn' => $fn, 'tn' => $tn,
            'precision' => round($precision, 3), 'recall' => round($recall, 3),
            'f1' => round(($precision + $recall) ? 2 * $precision * $recall / ($precision + $recall) : 0, 3), 'misses' => $misses,
        ];
    }

    private function runImages(): array
    {
        $dir = sys_get_temp_dir().'/ti-mod-'.getmypid();
        @mkdir($dir);
        $cases = [];
        $paths = [];
        $mk = function (string $name, int $w, int $h, callable $draw) use ($dir, &$paths) {
            $im = imagecreatetruecolor($w, $h);
            $draw($im, $w, $h);
            $p = $dir.'/'.$name.'.jpg';
            imagejpeg($im, $p, 90);
            imagedestroy($im);

            return $paths[$name] = $p;
        };
        $scene = function ($im, $w, $h, int $seed = 1, int $base = 130) {
            mt_srand($seed);
            imagefill($im, 0, 0, imagecolorallocate($im, $base, $base + 20, $base + 40));
            for ($i = 0; $i < 220; $i++) {
                $c = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
                $x = mt_rand(0, $w);
                $y = mt_rand(0, $h);
                imagefilledrectangle($im, $x, $y, $x + mt_rand(10, 140), $y + mt_rand(10, 140), $c);
                imageline($im, mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h), $c);
            }
        };

        $mk('sharp', 1200, 800, fn ($im, $w, $h) => $scene($im, $w, $h, 1));
        $mk('sharp2', 1000, 700, fn ($im, $w, $h) => $scene($im, $w, $h, 7));
        $mk('blurry', 1200, 800, function ($im, $w, $h) use ($scene) {
            $scene($im, $w, $h, 3);
            for ($i = 0; $i < 25; $i++) {
                imagefilter($im, IMG_FILTER_GAUSSIAN_BLUR);
            }
        });
        $mk('dark', 1200, 800, function ($im, $w, $h) use ($scene) {
            $scene($im, $w, $h, 4);
            imagefilter($im, IMG_FILTER_BRIGHTNESS, -220);
        });
        $mk('bright', 1200, 800, function ($im, $w, $h) use ($scene) {
            $scene($im, $w, $h, 5);
            imagefilter($im, IMG_FILTER_BRIGHTNESS, 230);
        });
        $mk('lowres', 300, 180, fn ($im, $w, $h) => $scene($im, $w, $h, 6));
        $mk('panorama', 2400, 300, fn ($im, $w, $h) => $scene($im, $w, $h, 8));
        // copie réduite de « sharp » : doublon visuel
        $src = imagecreatefromjpeg($paths['sharp']);
        $dup = imagecreatetruecolor(800, 533);
        imagecopyresampled($dup, $src, 0, 0, 0, 0, 800, 533, 1200, 800);
        imagejpeg($dup, $paths['dup'] = $dir.'/dup.jpg', 85);

        $expected = ['sharp' => null, 'sharp2' => null, 'blurry' => 'blurry', 'dark' => 'too_dark', 'bright' => 'too_bright', 'lowres' => 'low_resolution', 'panorama' => 'odd_format', 'dup' => 'visual_duplicate'];
        $hashes = [];
        $hit = $miss = $falseAlarm = $clean = 0;
        $rows = [];
        foreach ($expected as $name => $code) {
            $r = $this->images->analyze($paths[$name]);
            $flags = $r['flags'];
            if ($code === 'visual_duplicate') {
                $d = ImageAnalyzer::distance($r['dhash'], $hashes['sharp'] ?? str_repeat('0', 16));
                if ($d <= config('ai.moderation.image.duplicate_distance')) {
                    $flags['visual_duplicate'] = ['weight' => 35, 'detail' => 'distance '.$d];
                }
            }
            $hashes[$name] = $r['dhash'];
            $strong = array_keys(array_filter($flags, fn ($f) => $f['weight'] >= 10));
            if ($code === null) {
                $clean++;
                $falseAlarm += $strong ? 1 : 0;
            } else {
                isset($flags[$code]) ? $hit++ : $miss++;
            }
            $rows[] = ['name' => $name, 'expected' => $code, 'detected' => array_keys($flags), 'sharpness' => isset($r['metrics']['relative_sharpness']) ? round($r['metrics']['relative_sharpness'], 3) : null];
        }
        foreach (glob($dir.'/*') as $f) {
            @unlink($f);
        }
        @rmdir($dir);

        return [
            'cases' => count($expected), 'detected' => $hit, 'missed' => $miss, 'false_alarms' => $falseAlarm, 'clean_cases' => $clean,
            'recall' => round($hit / max(1, $hit + $miss), 3), 'rows' => $rows,
        ];
    }

    /** Accord entre l'IA et l'administrateur sur les dossiers déjà traités. */
    private function decisions(): array
    {
        $rows = DB::table('moderation_reports')->whereIn('status', ['confirmed', 'dismissed'])->where('verdict', '!=', 'ok')->get(['status', 'verdict']);
        $conf = $rows->where('status', 'confirmed')->count();
        $dis = $rows->where('status', 'dismissed')->count();

        return [
            'decided' => $rows->count(), 'confirmed' => $conf, 'dismissed' => $dis,
            'agreement' => $rows->count() ? round($conf / $rows->count(), 3) : null,
            'likely_agreement' => ($l = $rows->where('verdict', 'likely'))->count() ? round($l->where('status', 'confirmed')->count() / $l->count(), 3) : null,
        ];
    }

    private function realStats(): array
    {
        $v = DB::table('moderation_reports')->select('verdict', DB::raw('COUNT(*) c'))->groupBy('verdict')->pluck('c', 'verdict');
        $total = (int) $v->sum();
        $ok = (int) ($v['ok'] ?? 0);

        return [
            'analysed' => $total, 'ok' => $ok, 'review' => (int) ($v['review'] ?? 0), 'likely' => (int) ($v['likely'] ?? 0),
            'auto_ok_share' => $total ? round($ok / $total, 3) : null,
            'pending' => (int) DB::table('moderation_reports')->where('verdict', '!=', 'ok')->where('status', 'pending')->count(),
        ];
    }
}
