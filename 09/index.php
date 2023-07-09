<?php

//подопытные урлы
$urlsArr = array(
    '?var=val',
    'http://http.ru/folder/subfolder/../././script.php',
    'http://http.ru/folder/subfolder/../././script.php?var1=val1&var2=val2',
    'https://http.google.com/folder//././?var1=val1&var2=val2',
    'ftp://mail.ru/?hello=world&url=https://http.google.com/folder//././?var1=val1&var2=val2',
    'mail.ru/?hello=world&url=https://http.google.com/folder//././?var1=val1&var2=val2',
    '?mail=ru',
    'domain2.zone:8080/folder/subfolder/../././../asdss/.././//////../myfolder/script.php?var1=val1&var2=val2',
    'http://a.as.c.dom.dom.domain2.com:9080/folder/subfolder/./myfolder/script.php?var1=val1&var2=val2?var1=val1&var2=val2',
    'index.html?mail=ru',
);

//опыты
foreach ($urlsArr as $url) {
    var_dump($url);
    var_dump(urlParse($url));
}

/**
 * Функция возвращает подробную информацию о url в виде массива
 *
 * @param type string $__url Строка-url.
 *
 * @return type array|bool Массив содержащий информацию о url
 * и false в случае ошибки
 */
function urlParse(string $__url)
{
    //регулярное выражение
    $regularExp = '{

        ^(?:                    # начало строки
            (?<protocol> \w+ )  # карман для протокола
        ://                     # должен заканчиваться на ://
        )?                      # протокол может отсутствовать

        (?:                            # домен и порт
            (?<domain>                 # карман для доменного имени
                (?<= :// )             # перед доменом обязателен протокол, иначе съест путь
                (?: \w+ \.)*           # домены
                (?<main_domain> \w+ )  # карман для главного домена
                \.                     # точка м\у зоной и главным доменом
                (?<zone> \w+ )         # карман для зоны
            )
            (?:                   # порт
                : (?<port> \d+ )  # карман порта
            )?                    # порт может отсутствовать
        )?                        # доменное имя может отсутствовать

        (?<raw_folder>         # карман пути
            (?: \w*[^?]* / )+  # символы между слешами
        )?                     # путь может отсутствовать

        (?<script_name>    # карман скрипта
            \w+ \. [a-z]+  # имя скрипта
        )?                 # скрипт может отсутствовать

        (?:                            # строка параметров
            \?                         # начинаются со знака вопроса
            (?<parameters>             # карман строки параметров
                (?: \w+ = [^&]* &? )*  # параметры
                $                      # конец строки
            )?                         # могут отсутствовать
        )?
    }uix';

    //выходной массив после парса регулярным выражением
    $matchesArr = array();

    //ошибка если не url
    if (preg_match($regularExp, $__url, $matchesArr) === 0) {
        return false;
    }

    //var_dump($matchesArr);

    //preg_match() кладет в карман пустую строку если не совпало
    //но после есть совпавшие куски, поэтому нужна проверка на emtpy()
    //на пример в парсе '?var=val' все до параметров инициализированно пустыми строками
    $resArr = array(

        //протокол
        'protocol'    => empty($matchesArr['protocol']) ? false : $matchesArr['protocol'],

        //доменное имя
        'domain'      => empty($matchesArr['domain']) ? false : $matchesArr['domain'],
        'zone'        => empty($matchesArr['zone']) ? false : $matchesArr['zone'],
        'main_domain' => empty($matchesArr['main_domain']) ? false : $matchesArr['main_domain'],
        'port'        => empty($matchesArr['port']) ? 80 : $matchesArr['port'],

        //путь
        'raw_folder'  => empty($matchesArr['raw_folder']) ? false : $matchesArr['raw_folder'],
        'folder'      => false,

        //скрипт
        'script_path' => false,
        'script_name' => empty($matchesArr['script_name']) ? false : $matchesArr['script_name'],
        'is_php'      => false,

        //параметры
        'parameters'  => empty($matchesArr['parameters']) ? array() : $matchesArr['parameters'],

        //ошибка
        'is_error'    => false,
    );

    //если скрипт не указан, но указаны параметры
    //то скрипт по умолчанию - index.php
    if (!$resArr['script_name'] && $resArr['parameters']) {
        $resArr['script_name'] = 'index.php';
    }

    //если script_name - файл php, установка флага
    $resArr['is_php'] = preg_match('{\.php$}', $resArr['script_name']) ? true : false;

    //без протокола доменная часть в пути не нужна по условию
    $dirtyFolder = empty($resArr['protocol'])
        ? preg_replace('{^(\w+[.:])+ \w+ (?= / )}ux', '', $resArr['raw_folder'])
        : $resArr['raw_folder'];

    //клининг сервис
    $resArr['folder'] = mrProper($dirtyFolder);

    //путь скрипта есть просто folder + script_name
    $resArr['script_path'] = $resArr['folder'].$resArr['script_name'];

    //парс параметров в массив
    if ($resArr['parameters']) {
        $resArr['parameters'] = paramParse($resArr['parameters']);
    }

    //ошибка, если поддоменов > 5
    $resArr['is_error'] = preg_match_all('{\.}', $resArr['domain']) > 4 ? true : false;

    //информация о url
    return $resArr;
}

/**
 * Формирует массив из строки параметров
 *
 * @param string $__parameters Строка параметров.
 *
 * @return array Массив параметров
 */
function paramParse(string $__parameters)
{
    //регулярное выражение
    $paramExp = '{
        ( \w+ )     # имя переменной
        =           # разделение имени и значения
        ( .*? )     # значение
        (?= & | $)  # разделение параметров
    }uxi';

    //парс параметров
    $outArr = array();
    if (preg_match_all($paramExp, $__parameters, $outArr) === 0) {
        return false;
    }

    //$outArr[1] содержит названия переменных(первый карман)
    //$outArr[1] содержит значения переменных(второй карман)
    return array_combine($outArr[1], $outArr[2]);
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
    //если чистить нечего, возвращаем корень
    if (empty($__dirtyPath)) {
        return '/';
    }

    //выражение подряд идущих слешей
    $moreSlashExp = '{
        (?<= [^/] )  # слева все, кроме слеша
        /+           # множество слешей эквивалентных одному
        (?= [^/] )   # спрва все, кроме слеша
    }uix';

    //выражение псевдонимов текущих каталогов и родительских
    $dirExp = '{
        ( /\. (?= / ) )?            # текущий каталог
        ( ^( /\.\. )+ )?            # нельзя подняться выше корня
        ( / [\w : \.]+ / \.\. )?    # подняться на уровень выше
    }uix';

    //замена подряд идущих слешей на один
    $clearSlash = preg_replace($moreSlashExp, '/', $__dirtyPath);

    //подъем вверх, пока есть псевдонимы
    //причем, подняться выше корня не получится
    $clearPath = $clearSlash;
    while (preg_match('# /\. #ux', $clearPath) !== 0) {
        $clearPath = preg_replace($dirExp, '', $clearPath);
    }

    //очищенный путь
    return $clearPath;
}
