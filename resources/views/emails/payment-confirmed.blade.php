<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de paiement</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111;">
    <h2>Paiement confirmé</h2>
    <p>Votre paiement a bien été reçu.</p>
    <ul>
        <li>Montant: {{ number_format((float) $payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</li>
        <li>Méthode: {{ strtoupper((string) $payment->method) }}</li>
        <li>Transaction: {{ $payment->gateway_txn_id ?: 'N/A' }}</li>
        <li>Plan: {{ strtoupper($payment->subscription->plan->code ?? 'N/A') }}</li>
        <li>Facture: {{ $payment->invoice?->number ?: 'En cours de génération' }}</li>
    </ul>
    <p>Merci pour votre confiance.</p>
</body>
</html>
