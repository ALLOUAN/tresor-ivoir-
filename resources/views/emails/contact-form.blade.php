<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Message de contact</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b;">
    <p><strong>De :</strong> {{ $senderName }} &lt;{{ $senderEmail }}&gt;</p>
    <p><strong>Objet :</strong> {{ $subjectLine }}</p>
    <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 1rem 0;">
    <p style="white-space: pre-wrap;">{{ $bodyMessage }}</p>
</body>
</html>
