<?php

class Captcha
{

    private $resultado;
    private $imagem_largura;
    private $imagem_altura;
    private $maior_numero;
    private $tamanho_fonte;
    private $lista_fonte = array();
    private $lista_numeros = array();
    private $objImagem;

    function __construct($largura = 180, $altura = 45, $tamanho_fonte = 28, $maior_numero = 15)
    {
        $this->imagem_largura = $largura;
        $this->imagem_altura = $altura;
        $this->tamanho_fonte = $tamanho_fonte;
        $this->maior_numero = $maior_numero;
        $this->lista_fonte = array(
            'font/Antonio-Regular.ttf',
            'font/Archistico_Simple.ttf',
            'font/FengardoNeue_Regular.otf',
        );
    }

    function setListaFonte($fontes = array())
    {

        if (!empty($fontes)) {
            $this->lista_fonte = $fontes;
        }
    }

    function cor($min = 0, $max = 255)
    {
        return rand($min, $max);
    }

    function buscarPalavra()
    {
        return $this->palavra;
    }

    function gerarNumero()
    {
        return mt_rand(1, $this->maior_numero);
    }

    function gerarTamanhoFonte()
    {
        return mt_rand(16, $this->tamanho_fonte);
    }

    function gerarCalculo()
    {
        $numero1 = $this->gerarNumero();
        $numero2 = $this->gerarNumero();
        $this->lista_numeros = array($numero1, "+", $numero2);
    }

    function buscarResultado()
    {
        $this->resultado = implode("", $this->lista_numeros);
        return $this->resultado;
    }

    function gerarLetras()
    {
        $letras = '123456789ABCDEFGHJKMNPQRSTUVXZW';
        $tamanho = strlen($letras);

        for ($i = 0; $i < 7; $i++) {
            $posicao = rand(0, $tamanho - 1);
            $this->lista_numeros[] = substr($letras, $posicao, 1);
        }
    }

    function gerarImagem()
    {

//        exit;
        header("Content-type: image/jpeg");
        $this->objImagem = imagecreatetruecolor($this->imagem_largura, $this->imagem_altura);
        $this->gerarLetras();
        $this->gerarFundo();
        $this->gerarImagemBase();

        imagejpeg($this->objImagem, null, 40);
        imagedestroy($this->objImagem);
    }

    function gerarFundo()
    {
        $baseCor = imagecolorallocate($this->objImagem, $this->cor(240, 255), $this->cor(240, 255), $this->cor(240, 255));
        imagefilledrectangle($this->objImagem, 0, 0, $this->imagem_largura, $this->imagem_altura, $baseCor);
        $letras = $this->lista_numeros;
        for ($j = 0; $j < 4; $j++) {

            shuffle($letras);
            for ($i = 0; $i < count($letras); $i++) {

                $posicao = array_rand($this->lista_fonte, 1);
                $baseCor = imagecolorallocate($this->objImagem, $this->cor(160, 190), $this->cor(160, 190), $this->cor(160, 190));
                $posX = (int)$this->imagem_largura * rand(0, 100) / 100 - 10;
                $posY = (int)$this->imagem_altura * rand(0, 100) / 100 + 10;

                imagettftext($this->objImagem, rand(15, 24), rand(-25, 25), $posX, $posY, $baseCor, $this->lista_fonte[$posicao], $letras [$i]);
            }
        }
    }

    function gerarImagemBase()
    {
//        $fonte = $this->lista_fonte;
        for ($i = 0; $i < count($this->lista_numeros); $i++) {
            $tamanho_fonte = rand(18, 25);
            $posicao = array_rand($this->lista_fonte, 1);
            $baseCor = imagecolorallocate($this->objImagem, $this->cor(10, 10), $this->cor(10, 10), $this->cor(10, 10));
            imagettftext($this->objImagem, $tamanho_fonte, rand(-15, 15), $i * (1 * 22) + 10, ($tamanho_fonte + 12), $baseCor, $this->lista_fonte[$posicao], $this->lista_numeros[$i]);
        }
    }

}

session_start();
// inicial a sessaoi
$captcha = new Captcha();
$captcha->gerarImagem();
$_SESSION['palavra'] = $captcha->buscarResultado();


