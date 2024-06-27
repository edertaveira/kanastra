<!DOCTYPE html>
<html>

<head>
    <title>Kanastra</title>
</head>

<body>
    <h1>Olá, {{ $user }}</h1>
    <p>Segue o link para pagamento do boleto. Clique no link abaixo para realizar o pagamento.</p>
    <p>
        <a href='{{ $link }}'>Link</a>
    </p>
</body>

</html>