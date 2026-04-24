<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Abonnement expirant bientôt</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111;">
    <h2>Votre abonnement expire bientôt</h2>
    <p>
        L'abonnement {{ strtoupper($subscription->plan->code ?? 'N/A') }}
        pour {{ $subscription->provider->name ?? 'votre établissement' }}
        expire le {{ optional($subscription->ends_at)->format('d/m/Y') }}.
    </p>
    <p>Renouvelez votre abonnement pour éviter toute interruption de service.</p>
</body>
</html>
