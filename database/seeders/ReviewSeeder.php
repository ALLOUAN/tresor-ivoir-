<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $visitor = User::where('role', 'visitor')->first();
        $providerUser = User::where('role', 'provider')->first();
        $admin = User::where('role', 'admin')->first();

        $sofitel = Provider::where('slug', 'sofitel-abidjan-hotel-ivoire')->first();
        $restaurant = Provider::where('slug', 'la-table-du-chef-cocody')->first();
        $parc = Provider::where('slug', 'parc-national-de-tai')->first();

        $reviews = [
            [
                'review' => [
                    'provider_id' => $sofitel->id,
                    'user_id' => $visitor->id,
                    'rating' => 5,
                    'rating_quality' => 5,
                    'rating_price' => 4,
                    'rating_welcome' => 5,
                    'rating_clean' => 5,
                    'title' => 'Une expérience absolument mémorable',
                    'comment' => 'Le Sofitel Hôtel Ivoire reste une adresse mythique. La vue sur la lagune depuis notre suite était époustouflante. Le service impeccable et la piscine olympic sont dans une classe à part. On reviendra sans hésiter !',
                    'author_name' => 'Marie D.',
                    'visit_date' => now()->subMonth(),
                    'status' => 'approved',
                    'moderated_by' => $admin->id,
                    'moderated_at' => now()->subDays(2),
                ],
                'reply' => [
                    'provider_id' => $sofitel->id,
                    'replied_by' => $providerUser->id,
                    'reply_text' => 'Chère Marie, merci infiniment pour ce retour chaleureux ! C\'est avec grand plaisir que nous vous accueillerons à nouveau au Sofitel Hôtel Ivoire. Notre équipe sera ravie de vous retrouver.',
                ],
            ],
            [
                'review' => [
                    'provider_id' => $sofitel->id,
                    'user_id' => null,
                    'rating' => 4,
                    'rating_quality' => 4,
                    'rating_price' => 3,
                    'rating_welcome' => 5,
                    'rating_clean' => 4,
                    'title' => 'Très bon séjour, quelques bémols',
                    'comment' => 'Hôtel magnifique avec une histoire fascinante. La piscine et le casino sont impressionnants. Dommage que certaines parties de l\'hôtel mériteraient une petite rénovation. Le petit-déjeuner reste exceptionnel.',
                    'author_name' => 'Jean-Luc V.',
                    'visit_date' => now()->subMonths(2),
                    'status' => 'approved',
                    'moderated_by' => $admin->id,
                    'moderated_at' => now()->subDays(5),
                ],
                'reply' => null,
            ],
            [
                'review' => [
                    'provider_id' => $restaurant->id,
                    'user_id' => $visitor->id,
                    'rating' => 5,
                    'rating_quality' => 5,
                    'rating_price' => 4,
                    'rating_welcome' => 5,
                    'rating_clean' => 5,
                    'title' => 'La meilleure table d\'Abidjan !',
                    'comment' => 'Le chef Gilles Adiko signe une cuisine ivoirienne d\'exception. Le foie de pintade laqué à la graine de sésamé et l\'attiéké revisité sont des chefs-d\'œuvre. Cadre enchanteur, service attentionné. Réservation indispensable.',
                    'author_name' => 'Aïcha K.',
                    'visit_date' => now()->subWeeks(3),
                    'status' => 'approved',
                    'moderated_by' => $admin->id,
                    'moderated_at' => now()->subDays(1),
                ],
                'reply' => [
                    'provider_id' => $restaurant->id,
                    'replied_by' => $providerUser->id,
                    'reply_text' => 'Merci beaucoup Aïcha pour ces mots qui nous touchent profondément. Le Chef Gilles est particulièrement sensible à vos compliments. Nous vous attendons pour découvrir notre nouvelle carte de saison !',
                ],
            ],
            [
                'review' => [
                    'provider_id' => $parc->id,
                    'user_id' => null,
                    'rating' => 5,
                    'rating_quality' => 5,
                    'rating_price' => 5,
                    'rating_welcome' => 4,
                    'rating_clean' => 5,
                    'title' => 'Une expérience de vie — les chimpanzés sont extraordinaires',
                    'comment' => 'Nous avons eu la chance incroyable d\'observer une famille de chimpanzés pendant plus d\'une heure. Notre guide était formidable et connaissait chaque individu par son nom. Un parc d\'une biodiversité incroyable. Prévoir 2-3 nuits sur place.',
                    'author_name' => 'Dr. Sarah M.',
                    'visit_date' => now()->subMonths(1),
                    'status' => 'approved',
                    'moderated_by' => $admin->id,
                    'moderated_at' => now()->subDays(3),
                ],
                'reply' => null,
            ],
        ];

        foreach ($reviews as $entry) {
            $review = Review::create($entry['review']);

            if ($entry['reply']) {
                ReviewReply::create(array_merge(
                    $entry['reply'],
                    ['review_id' => $review->id]
                ));
            }
        }

        // Mettre à jour les moyennes de notes
        foreach (Provider::whereIn('slug', ['sofitel-abidjan-hotel-ivoire', 'la-table-du-chef-cocody', 'parc-national-de-tai'])->get() as $provider) {
            $approved = $provider->approvedReviews();
            $provider->update([
                'rating_avg' => $approved->avg('rating'),
                'rating_count' => $approved->count(),
            ]);
        }
    }
}
