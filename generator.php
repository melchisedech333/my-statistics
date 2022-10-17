<?php

/**
 * Todas as coisas concorrem para o bem daqueles que amam a Deus - Romanos, 8.
 */

function entry () {
    $items = prepare_data();
    
    $public = process_data($items['public']);
    $private = process_data($items['private']);
    $fail_or_stopped = process_data($items['fail_or_stopped']);

    readme_generator($public, $private, $fail_or_stopped);
}

function process_data ($items) {
    $data = array();

    for ($a=0; $a<count($items); $a++) {
        $item = $items[$a];
        $project_name = substr($item['path_file'], 0, strlen($item['path_file']) - 4);
        $total_files = count($item['files']);
        $files = array();

        for ($b=0; $b<count($item['files']); $b++) {
            $file = $item['files'][$b];
            $found = false;
            $index = -1;

            for ($c=0; $c<count($files); $c++) {
                if ($files[$c]['type'] == $file['ext']) {
                    $found = true;
                    $index = $c;
                    break;
                }
            }

            if ($found === false) {
                $files []= array(
                    'type' => $file['ext'],
                    'lines' => $file['lines'],
                );
            } else if ($found === true && $index != -1) {
                $files[ $index ]['lines'] += $file['lines'];
            }
        }

        $data []= array(
            'project' => $project_name,
            'files' => $files,
        );
    }

    return $data;
}

function prepare_data () {
    return array(
        'public' => load_paths('../projects-directories/public/'),
        'private' => load_paths('../projects-directories/private/'),
        'fail_or_stopped' => load_paths('../projects-directories/fail-or-stopped/'),
    );
}

function load_paths ($path) {
    $paths = array();

    if ($handle = opendir($path)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $paths []= array(
                    'path_file' => $entry,
                    'files' => array(),
                );
            }
        }

        closedir($handle);
    }

    for ($a=0; $a<count($paths); $a++) {
        $content = file_get_contents($path . $paths[$a]['path_file']);
        $content = explode("\n", $content);

        for ($b=0; $b<count($content); $b++) {
            if (!empty(trim($content[$b]))) {
                $project_name = substr($paths[$a]['path_file'], 0, strlen($paths[$a]['path_file']) - 4);
                $file_path = '../'. $project_name .'/'. $content[$b];
                $file_content = file_get_contents($file_path);
                $size = strlen($file_content);
                $file_ext = '';
                $file_lines = 0;

                for ($c=(strlen($file_path)-1); $c>=0; $c--) {
                    if ($file_path[$c] == '.') {
                        $c++;
                        $file_ext = substr($file_path, $c, strlen($file_path) - $c);
                        $file_ext = trim($file_ext);
                        break;
                    }
                }

                for ($c=0; $c < $size; $c++) {
                    if ($file_content[$c] == "\n") {
                        $file_lines++;
                    }
                }
                

                $paths[$a]['files'] []= array(
                    'name' => $content[$b],
                    'content' => $file_content,
                    'ext' => $file_ext,
                    'lines' => $file_lines,
                );
            }
        }
    }

    return $paths;
}

function generalize_item ($languages, $area) {

    for ($a=0; $a<count($area); $a++) {
        for ($b=0; $b<count($area[$a]['files']); $b++) {
            $item = $area[$a]['files'][$b];
            $found = false;
            $index = -1;

            for ($c=0; $c<count($languages); $c++) {
                if ($languages[$c]['type'] == $item['type']) {
                    $found = true;
                    $index = $c;
                    break;
                }
            }

            if ($found === false) {
                $languages []= array(
                    'type' => $item['type'],
                    'lines' => $item['lines'],
                );
            } else if ($found === true && $index != -1) {
                $languages[ $index ]['lines'] += $item['lines'];
            }
        }
    }

    return $languages;
}

function generalize_data ($public, $private, $fail_or_stopped) {
    $langs = array();

    $languages = generalize_item(array(), $public);
    $languages = generalize_item($languages, $private);
    $languages = generalize_item($languages, $fail_or_stopped);
    
    // Prepare.
    for ($a=0; $a<count($languages); $a++) {
        $type = $languages[$a]['type'];

        if ($type == 'sass' || $type == 'css')
            continue;
        else if ($type == 'c' || $type == 'cc' || 
                 $type == 'h' || $type == 'cpp' )
            continue;
        else if ($type == 'verbum' || $type == 'conf' || $type == 'html')
            continue;
        else if ($type == 'l' || $type == 'y' || $type == 'g4')
            continue;

        $langs []= $languages[$a];
    }

    // Sass, CSS.
    $css_item = array(
        'type' => 'sass_css',
        'lines' => 0,
        'items' => array(),
    );

    for ($a=0; $a<count($languages); $a++) {
        $type = $languages[$a]['type'];

        if ($type == 'sass' || $type == 'css') {
            $css_item['lines'] += $languages[$a]['lines'];
            $css_item['items'] []= $languages[$a];
        }
    }

    $langs []= $css_item;

    // C, h, cpp, cc.
    $c_cpp_item = array(
        'type' => 'c_cpp',
        'lines' => 0,
        'items' => array(),
    );

    for ($a=0; $a<count($languages); $a++) {
        $type = $languages[$a]['type'];

        if ($type == 'c' || $type == 'cc' || 
            $type == 'h' || $type == 'cpp' )
        {
            $c_cpp_item['lines'] += $languages[$a]['lines'];
            $c_cpp_item['items'] []= $languages[$a];
        }
    }

    $langs []= $c_cpp_item;

    // Grammar files.
    $grammar_item = array(
        'type' => 'grammar',
        'lines' => 0,
        'items' => array(),
    );

    for ($a=0; $a<count($languages); $a++) {
        $type = $languages[$a]['type'];

        if ($type == 'l' || $type == 'y' || $type == 'g4') {
            $grammar_item['lines'] += $languages[$a]['lines'];
            $grammar_item['items'] []= $languages[$a];
        }
    }

    $langs []= $grammar_item;

    // Total.
    $total = array(
        'type' => 'total',
        'lines' => 0,
    );

    for ($a=0; $a<count($langs); $a++) {
        $total['lines'] += $langs[$a]['lines'];
    }

    $langs []= $total;

    return $langs;
}

function generalize_html ($data) {
    $html = "<table width='100%'>\n";

    for ($a=0; $a<count($data); $a++) {
        $item = $data[$a];
        if ($item['type'] == 'total') {
            $html .= process_total_area($item);
        }
    }

    $html .= process_item_area($data, 'c_cpp', 'C, C++, and headers');
    $html .= process_item_area($data, 'grammar', 'Grammar (Lex, Yacc, and ANTLR4');
    $html .= process_item_area($data, 'php', 'PHP');
    $html .= process_item_area($data, 'ts', 'Typescript');
    $html .= process_item_area($data, 'js', 'Javascript');
    $html .= process_item_area($data, 'sass_css', 'Sass and Pure CSS');
    $html .= process_item_area($data, 'sql', 'SQL');
    $html .= process_item_area($data, 'sh', 'Shell script');
    
    $html .= "\n</table>";

    return $html;
}

function process_total_area ($item) {
    $html = "<tr>
    <td>
        <b>Total lines</b>
    </td>
    <td>
        ". $item['lines'] ."
    </td>
    </tr>";
    
    return $html;
}

function process_item_area ($data, $name, $area_name) {
    $html = '';

    for ($a=0; $a<count($data); $a++) {
        $item = $data[$a];

        if ($item['type'] == $name) {
            $html = "<tr>
<td>
    <b>". $area_name ."</b>
</td>
<td>
    ". $item['lines'] ."
</td>
</tr>";
        }
    }

    return $html;
}

function readme_generator ($public, $private, $fail_or_stopped) {
    $data_generalized = generalize_data($public, $private, $fail_or_stopped);

    $en = "
<div align='center'>

<img src='images/banner.jpg' width='100%' >

</div>

<br>

Language: <a href='readme-pt.md'>PT-BR</a>

<br>

In this project my statistics about my public and private projects are stored. It is important to say that only codes written by me are counted, third-party codes do not count.

<br>

". generalize_html($data_generalized) ."\n\n\n";

    $pt = "
<div align='center'>

<img src='images/banner.jpg' width='100%' >

</div>

<br>

Language: <a href='readme.md'>EN-US</a>

<br>

Neste projeto são armazenadas minhas estatísticas a respeito de meus projetos públicos e privados. É importante dizer que somente códigos escritos por mim é que são contabilizados, códigos de terceiros não entram na contagem.

<br>

". generalize_html($data_generalized) ."\n\n\n";

    file_put_contents("readme.md", $en);
    file_put_contents("readme-pt.md", $pt);
}

$r = entry();


