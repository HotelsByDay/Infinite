<?php
$data = array(
    'content' => (string)$content,
    'action_name'   => (string)$action_name,
    'action_result' => (string)$action_result,
    'action_status' => (string)$action_status,
    'id'            => (string)$id,
    'preview'       => (string)$preview, //zde bude preview editovaneho zaznamu
    'headline'      => (string)$headline,   //aktualizovany nadpis formulare (meni se napr. pri prvnim ulozeni zaznam)
    //asoc. pole ve tvaru 'nazev prvku' => hodnota. Tyto hodnoty jsou po uspesnem 
    //ulozeni zaznamu vyplneny na formulari - toto se pouziva pro specialni pripad
    //kdy je na AppFormItemRelSelect povoleno vytvoreni noveho relacniho prvku
    //v dialogovem okne - po ulozeni noveho zaznamu jsou predvyplneny formularova
    //pole danymi hodnotami.
    'fill'          => isset($fill) ? $fill : array(),


    'extra'         => isset($extra) ? $extra : array(),
);

//url na kterou ma byt uzivatel presmerovan po uspesnem provedeni form akce
if (isset($redir) && ! empty($redir))
{
    $data['redir'] = (string)$redir;
}

echo json_encode($data);
?>
