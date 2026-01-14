<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Abrindo relatório...</title>
</head>
<body>
    <script>
        (function(){
            var url = @json($url);
            // abre em nova aba
            window.open(url, '_blank');
            // tenta voltar para a página anterior (Filament)
            try {
                if (document.referrer) {
                    window.location = document.referrer;
                } else {
                    window.close();
                }
            } catch (e) {
                // se não conseguir redirecionar, exibe link
                document.body.innerHTML = '<p><a href="' + url + '" target="_blank">Abrir relatório</a></p>';
            }
        })();
    </script>
    <noscript>
        <p>Seu navegador precisa permitir JavaScript. <a href="{{ $url }}" target="_blank">Clique aqui</a> para abrir o relatório.</p>
    </noscript>
</body>
</html>