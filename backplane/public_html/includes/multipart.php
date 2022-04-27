<?php


  
function build_multipart_data_files($delimiter, $fields, $files) {
    # Inspiration from: https://gist.github.com/maxivak/18fcac476a2f4ea02e5f80b303811d5f :)
    $data = '';
    $eol = "\r\n";
  
    foreach ($fields as $name => $content) {
        $data .= "--" . $delimiter . $eol
            . 'Content-Disposition: form-data; name="' . $name . "\"".$eol.$eol
            . $content . $eol;
    }
  
    foreach ($files as $name => $content) {
        $data .= "--" . $delimiter . $eol
            . 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $name . '"' . $eol
            . 'Content-Transfer-Encoding: binary'.$eol
            ;
        $data .= $eol;
        $data .= $content . $eol;
    }
    $data .= "--" . $delimiter . "--".$eol;

    return $data;
}