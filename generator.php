<?php

/**
 * Todas as coisas concorrem para o bem daqueles que amam a Deus - Romanos, 8.
 */

function entry () {


    readme_generator();
}

function readme_generator () {
    $en = "
<div align='center'>

<img src='images/banner.jpg' width='100%' >

</div>

<br>

Language: <a href='readme-pt.md'>PT-BR</a>

<br>

In this project my statistics about my public and private projects are stored. It is important to say that only codes written by me are counted, third-party codes do not count.



    ";

    $pt = "
<div align='center'>

<img src='images/banner.jpg' width='100%' >

</div>

<br>

Language: <a href='readme.md'>EN-US</a>

<br>

Neste projeto são armazenadas minhas estatísticas a respeito de meus projetos públicos e privados. É importante dizer que somente códigos escritos por mim é que são contabilizados, códigos de terceiros não entram na contagem.



    ";

    file_put_contents("readme.md", $en);
    file_put_contents("readme-pt.md", $pt);
}

$r = entry();


