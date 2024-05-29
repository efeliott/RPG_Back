<!-- resources/views/emails/invite.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Invitation to Join Session</title>
</head>
<body>
    <p>Bonjour,</p>
    <p>Vous avez été invité à rejoindre une session de jeu. Cliquez sur le lien ci-dessous pour rejoindre la session :</p>
    <p><a href="{{ $link }}">Rejoindre la session</a></p>
    <p>Merci !</p>
</body>
</html>
