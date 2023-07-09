<?php

/**
 * Я тестил много запросов, что именно не нашел?
 * Возможно конечно при дефолтном запросе могло что-то пойти не так,
 * т.к подсказка отличалась от настоящего запроса
 */

//функционал
require_once 'FinderInGoogle.php';
require_once 'FinderInYandex.php';
require_once 'FinderInBing.php';

//для вывода
require_once 'template/Template.php';

//для возможности поиска
set_time_limit(500);

//для страницы
$dataArr  = [];
$template = file_get_contents('template/input.tpl');

//обработка запроса
if (!empty($_POST)) {

    //выражение для входных данных
    $regExpIsWebSite = '[
            ^(                    # начало строки
            (http|https)://)?     # протокол
            (
                ([\w-]+\.){0,3}   # поддомены перед главным доменным именем
                ([-\w]+)          # обязательное доменное имя
                (\.[\w]+)?        # зона
            )
            (/.*)?                # месиво после домена
        ]xi';

    //Проверка глубины
    $regexpInRange = '#^[0-4]?[0-9]?[0-9]$|^500$#';
    $regexpIsNum   = '#^[0-9]+$#';

    //Значения по умолчанию
    $defaultSite    = 'ozon.ru';
    $defaultRequest = 'купить лыжи';
    $defaultDepth   = 50;
    $maxDepth       = 500;

    //проверка корректности
    $isNum  = preg_match($regexpIsNum, $_POST['dep']);
    $isSite = preg_match($regExpIsWebSite, $_POST['site']);

    //задание корректных параметров
    $site  = $isSite ? $_POST['site'] : $defaultSite;
    $depth = $isNum ?
        preg_match($regexpInRange, $_POST['dep']) ? (int)$_POST['dep'] : $maxDepth
        : $defaultDepth;

    //Задание корректных параметров
    $request = $_POST['req'] ?: $defaultRequest;

    //создание искателя в определенном поисковике
    switch ($_POST['flexRadioDefault']) {
        case 'yandex' : $finder = new FinderInYandex($request, $site, $depth);
            break;
        case 'google' : $finder = new FinderInGoogle($request, $site, $depth);
            break;
        case 'bing'   : $finder = new FinderInBing($request, $site, $depth);
            break;
    }

    //поиск
    $infoFound = $finder->ToFind();

    //шаблон
    $template = file_get_contents('template/output.tpl');
    $dataArr = [
     'site'       => $site,
     'request'    => $request,
     'search'     => $_POST['flexRadioDefault'],
     'info_cycle' => is_array($infoFound)
        ? array(
            ['head' => 'Ссылка',        'info' => $infoFound['url']],
            ['head' => 'Полный домен',  'info' => $infoFound['full_domain']],
            ['head' => 'Главный домен', 'info' => $infoFound['domain']],
            ['head' => 'Зона',          'info' => $infoFound['zone']],
            ['head' => 'Заголовок',     'info' => $infoFound['header']],
            ['head' => 'Описание',      'info' => $infoFound['description']],
            ['head' => 'Позиция',       'info' => $infoFound['position']],
            ['head' => 'Доступность',   'info' => $infoFound['availability']]
        )
        : array(
            ['head' => 'ERROR: ', 'info' => $infoFound]
        )
    ];
}

print Template::build($template, $dataArr);


