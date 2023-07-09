<?php

/**
 * Функция возвращает подробную информацию о url в виде массива
 *
 * @param type string $__url Строка-url.
 *
 * @return type array Массив содержащий информацию о url
 */
function urlParse(string $__url)
{

    //создается массив, содержащий дефолтную информацию о url
    $resultArray = array(

        //протокол
        'protocol'    => false,

        //доменное имя
        'domain'      => false,
        'zone'        => false,
        'main_domain' => false,
        'port'        => false,

        //путь
        'raw_folder'  => false,
        'folder'      => false,

        //скрипт
        'script_path' => false,
        'script_name' => false,
        'is_php'      => false,

        //параметры
        'parameters'  => array(),

        //ошибка
        'is_error'    => false,
    );

	//позиция признака параметров
    $posTokenParam = strpos($__url, '?');

    //если присутствует, тогда разрез url на части
    if ($posTokenParam !== false) {
        $protocolDomainPathScript = substr($__url, 0, $posTokenParam);
        $stringParameters         = substr($__url, $posTokenParam + 1);
    }

    //иначе остается как есть
    else {
        $protocolDomainPathScript = $__url;
        $stringParameters         = false;
    }

    //------------------Разбор протокола------------------

    //поиск подстроки "://" указывающего на наличие протокола
    $posTokenProtocol = strpos($protocolDomainPathScript, '://');

    //если "://" присутствует, то в $protocol записывается все до "://",
    //а в $domainPathScript записывается все, что находится справа от "://"
    if ($posTokenProtocol !== false) {
        $resultArray['protocol'] = substr($protocolDomainPathScript, 0, $posTokenProtocol);
        $domainPathScript        = substr($protocolDomainPathScript, $posTokenProtocol + 3);
    }

    //иначе весь $protocolDomainPathScript является $domainPathScript
    else {
        $domainPathScript = $protocolDomainPathScript;
    }

    //------------------Разбор параметров------------------

    //если $stringParameters отлична от false , то обработка параметров
    if ($stringParameters !== false) {

        //Дробь стороки на части, разделенные "&"
        $varAndVal = strtok($stringParameters, '&');
        while ($varAndVal !== false) {

            //извлечение имени и значения параметра с помощью позиции знака присваивания
            $posAssignment = strpos($varAndVal, '=');
            $variable      = substr($varAndVal, 0, $posAssignment);
            $value         = substr($varAndVal, $posAssignment + 1);

            //запись параметра в массив данных о url
            $resultArray['parameters'][$variable] = $value;

            //очередной разрез строки до следующего знака '&'
            $varAndVal = strtok('&');
        }
    }

    //------------------Разбор доменного имени------------------

    //позиции точки и слеша для распознавания признака домена
    $posPoint = strpos($domainPathScript, '.');
    $posSlash = strpos($domainPathScript, '/');

    //если символ "." отстает от символа "/" как минимум на 2 позиции, то доменное имя присутствует
    if ($posSlash - $posPoint > 1) {

        //разрез $domainPathScript на путь со скриптом и на доменное имя с портом
        $pathAndScript = substr($domainPathScript, $posSlash + 1);
        $domainAndPort = substr($domainPathScript, 0, $posSlash);

        //разрез на доменное имя и порт
        $domain = strtok($domainAndPort, ':');
        $port   = strtok('');

        //массив доменов - состовляющих доменное имя
        $subDomainsArr = array();

        //разрез на домены
        $pieceDomain = strtok($domain, '.');
        while ($pieceDomain !== false) {
            $subDomainsArr[] = $pieceDomain;
            $pieceDomain     = strtok('.');
        }

        //если число поддоменов более пяти, выставляется флаг ошибки
        $countSubDomains = count($subDomainsArr);
        if ($countSubDomains > 5) {
            $resultArray['is_error'] = true;
        }

        //запись домена, если присутствует протокол
        if ($resultArray['protocol'] !== false) {

            //запись порта
            $resultArray['port'] = $port
                ? $port
                : 80;

            //Заполнение информацией о доменом имени
            $resultArray['domain']      = $domain;
            $resultArray['zone']        = $subDomainsArr[$countSubDomains - 1];
            $resultArray['main_domain'] = $subDomainsArr[$countSubDomains - 2];
        }

        //иначе относительный путь начнется с $domainAndPort, и далее к нему может добавится путь
        else {
            $resultArray['raw_folder'] = $domainAndPort;
        }
    }

    //если домен отсутствует, то $pathAndScript есть просто $domainPathScript
    else {
        $pathAndScript = $domainPathScript;
    }

    //------------------Разбор скрипта и пути------------------

    //поиск последнего слэша для
    $posLastSlash = strrpos($pathAndScript, '/');

    //если последний слэш отсутствует и длина $pathAndScript не ноль, то $pathAndScript либо скрипт либо директория
    if ($posLastSlash === false && strlen($pathAndScript) !== 0) {

        //если есть точка, и не на первом месте, то это имя скрипта и путь до него суть $pathAndScript
        if (strpos($pathAndScript, '.') > 0) {
            $resultArray['script_name'] = $pathAndScript;
            $resultArray['script_path'] = '/'.$pathAndScript;
        }

        //иначе это путь
        else {
            $resultArray['raw_folder'] = $pathAndScript;
            $resultArray['folder']     = $pathAndScript;
        }
    }

    //иначе обработка строки после последнего слэша
    else {

        //потенциальное имя скрипта
        $scriptName = substr($pathAndScript, $posLastSlash + 1);

        //если строка $scriptName пуста, то скриптом является 'index.php'
        if (strlen($scriptName) === 0) {
            $resultArray['script_name'] = 'index.php';
            $resultArray['is_php']      = true;
        }

        //если строка не пуста и точка присутствует не на первой позиции, то скриптом является $scriptName
        elseif (strpos($scriptName, '.') > 0) {
            $resultArray['script_name'] = $scriptName;
            $resultArray['is_php']      = (strrpos($scriptName, '.php') === strlen($scriptName) - 4);
        }

        //иначе скрипт отсутствует
        else {
            $resultArray['raw_folder'] = $pathAndScript;
            $resultArray['folder']     = $pathAndScript;
        }

        //строка потенциального полного пути
        $path = substr($pathAndScript, 0, $posLastSlash);

        //если в пути ничего нет и потенциальный путь не пуст, то запись $path в 'raw_folder'
        if ($resultArray['raw_folder'] === false && strlen($path) > 0) {
            $resultArray['raw_folder'] = $path;
        }

        //просто сцепляются 'raw_folder' и $path
        else {
            $resultArray['raw_folder'] .= $path;
        }

        //упрощение пути
        $resultArray['folder']      = mrProper($path);
        $resultArray['script_path'] = $resultArray['folder'] === '/'
                ? $resultArray['script_name']
                : $resultArray['folder'].'/'.$resultArray['script_name'];
    }

    //
    return $resultArray;
}

/**
 * Функция чистки пути
 *
 * @param string $__dirtyPath Грязный путь, который нужно очистить.
 *
 * @return string $cleanPath Очищенный путь
 */
function mrProper(string $__dirtyPath)
{
    //формирование строки с упрощенным путем
    $cleanPath = '';

    //чистка ненужных "./" и "/"
    $pieceDir = strtok($__dirtyPath, '/');
    while ($pieceDir !== false) {

        //добавление в $coolPath только значимых частей пути
        if ($pieceDir !== '.') {
            $cleanPath .= '/'.$pieceDir;
        }

        //очередной разрез
        $pieceDir = strtok('/');
    }

    //чистка директории перед "/.."
    //позиция первого псевдонима родительского каталога
    $posLevelUp = strpos($cleanPath, '..');
    while ($posLevelUp > 2) {

        //запись части перед псевдонимом
        $allDirBefore = substr($cleanPath, 0, strpos($cleanPath, '..') + 2);

        //перевернутый $allDirBefore для удобства чистки каталога перед псевдонимом
        $revAllDirBefore = strrev($allDirBefore);

        //позиция каталога который нужно убрать
        $posChildDir = strpos($revAllDirBefore, '/', 3) + 1;

        //каталог, который нужно убрать вместе с псевдонимом
        $replace = strrev(substr($revAllDirBefore, 0, $posChildDir));

        //если не пуст, то убирается
        if (strlen($replace)) {
            $cleanPath = str_replace($replace, '', $cleanPath);
        }

        //позиция следующего псевдонима
        $posLevelUp = strpos($cleanPath, '..');
    }

    //чистка подъемов над корнем
    $result = substr($cleanPath, 1);
    while (strpos($result, '..') === 0) {
        $result = substr($result, 3);
    }

    //возврат чистого пути
    return strpos($result, '/') === 0 ? $result : '/'.$result;
}

$url1 = 'http://http.ru/folder/subfolder/../././script.php?var1=val1&var2=val2';
print_r($url1);
var_dump(urlParse($url1));

$url2 = 'https://http.google.com/folder//././?var1=val1&var2=val2';
print_r($url2);
var_dump(urlParse($url2));

$url3 = 'ftp://mail.ru/?hello=world&url=https://http.google.com/folder//././?var1=val1&var2=val2';
print_r($url3);
var_dump(urlParse($url3));

$url4 = 'mail.ru/?hello=world&url=https://http.google.com/folder//././?var1=val1&var2=val2';
print_r($url4);
var_dump(urlParse($url4));

$url5 = 'index.html?mail=ru';
print_r($url5);
var_dump(urlParse($url5));

$url6 = 'domain2.zone:8080/folder/subfolder/../././../asdss/.././//////../myfolder/script.php?var1=val1&var2=val2';
print_r($url6);
var_dump(urlParse($url6));

$url7 = 'http://a.b.c.d.dom.dom.domain2.com:8080/folder/subfolder/./myfolder/script.php?var1=val1&var2=val2?var1=val1&var2=val2';
print_r($url7);
var_dump(urlParse($url7));
