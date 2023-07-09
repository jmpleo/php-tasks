<?php

//Selenium
require_once 'composer\vendor\autoload.php';

//Использование пространства имен библеотеки Selenium
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\JavascriptErrorException;
use Facebook\WebDriver\Exception\WebDriverCurlException;

/**
 * Функционал поиска
 *
 * @author g193219
 */
abstract class FunctionalFinder
{
    //параметры поиска
    protected string $thereError;
    protected string $request;
    protected string $site;
    protected int    $depth;

    //драйвер для поиска
    protected RemoteWebDriver $driver;
    protected WebDriverWait   $wait;

    /**
     * Создание искателя
     *
     * @param string  $__request Запрос/ключевые слова.
     * @param string  $__site    Сайт поиска.
     * @param integer $__depth   Глубина поиска.
     */
    public function __construct(
        string $__request,
        string $__site,
        int    $__depth
    )
    {
        //выражение для ссылки
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

        //значения по умолчанию
        $defaultSite    = 'ozon.ru';
        $defaultRequest = 'купить лыжи';
        $defaultDepth   = 50;
        $maxDepth       = 500;

        //проверка корректности
        $this->site  = preg_match($regExpIsWebSite, $__site) ? $__site : $defaultSite;
        $this->depth = $__depth > 0
            ? $__depth < $maxDepth ? $__depth : $maxDepth
            : $defaultDepth;

        $this->thereError = '';
        $this->request    = $__request ?: $defaultRequest;

        //запуск драйвера
        system(`chromedriver --port=4444`);
        $this->driver = RemoteWebDriver::create(
            'http://localhost:4444',
            DesiredCapabilities::chrome()
        );

        //ожидание драйвера
        $this->wait = $this->driver->wait(5, 500);
    }

    /**
     * Уничтожение драйвера, и закрытие ассоциативных окон
     */
    public function __destruct() {
        $this->driver->quit();
    }

    /**
     * Поиск сайта
     */
    abstract public function ToFind();

    /**
     * Скролл до заданного элемента
     *
     * @param WebDriverElement $__elem
     */
    protected function ScrollTo(WebDriverElement $__elem)
    {
        $this->driver->executeScript(
            "arguments[0].scrollIntoView({ behavior: 'smooth', block: 'center'});",
            [ $__elem ]);

        //типо не бот//ожидание скролла
        sleep(1);
    }

    /**
     * Ожидание и возврат найденных элементов
     *
     * @param string $__xpath Путь до элемента.
     *
     * @return array Массив найденных элементов.
     */
    protected function WaitAndGiveAll(string $__xpath)
    {
        try {

            //Возврат найденного элемента
            $this->thereError = '';
            return $this->wait->until(
                WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                    WebDriverBy::xpath($__xpath)
                )
            );
        } catch (NoSuchElementException | TimeoutException $ex) {

            //echo $ex->getTraceAsString();
            $this->thereError = 'Not Found';// by xPath :'.$__xpath;
            return null;
        } catch (InvalidSelectorException $ex) {

            //echo $ex->getTraceAsString();
            $this->thereError = 'Invalid xPath :'.$__xpath;
            return null;
        }
    }

    /**
     * Переход на страницу
     *
     * @param string $__url Страница на которую осуществляется переход.
     *
     */
    protected function GoToUrl(string $__url)
    {
        try {
            $this->thereError = '';
            $this->driver->navigate()->to($__url);
        } catch (WebDriverCurlException $ex)
        {
            $this->thereError = 'bad connection...';
        }
    }

    /**
     * Ожидание и возврат найденного элемента
     *
     * @param string $__xpath Путь до элемента xpath-запросом.
     *
     * @return WebDriverElement Найденный элемент.
     */
    protected function WaitAndGive(string $__xpath)
    {
        try {
            $this->thereError = '';
            return $this->wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath($__xpath)
                )
            );
        } catch (NoSuchElementException | TimeoutException $ex) {

            //echo $ex->getTraceAsString();
            $this->thereError = 'Not Found';// by xPath :'.$__xpath;
            return null;
        } catch (InvalidSelectorException $ex) {

            //echo $ex->getTraceAsString();
            $this->thereError = 'Invalid xPath :'.$__xpath;
            return null;
        }
    }

    /**
     * Возвращает свойство элемента с помощью js
     *
     * @param WebDriverElement $__elem     Элемент.
     * @param string           $__property DOM-свойство элемента.
     *
     * @return string
     */
    protected function GetProperty(WebDriverElement $__elem, string $__property)
    {
        try {
            return $this->driver->executeScript(
                'return arguments[0].'.$__property.';',
                [ $__elem ]
            );
        } catch (JavascriptErrorException $ex) {
            $this->thereError = 'GetProperty: Unknow property: '.$__property;
            return null;
        }
    }

    /**
     * Осуществляет поиск интересующего элемента в массиве.
     *
     * @param array  $__elems DOM элемнты среди которых осуществляется поиск.
     * @param string $__site  Назавние сайта, с которым частично проверяется ссылка.
     *
     * @return array Массив вида
     * [
     *    'position'    => (int)
     *    'element'     => (WebDriverElement)
     *    'href'        => (string)
     *    'domain'      => (string)
     *    'main_domain' => (string)
     *    'zone  '      => (string)
     * ]
     */
    protected static function ThisSite(array $__elems, string $__site)
    {
        //Выражение которому должна удовлетворять ссылка на сайт
        $regExp = "[
            ^(?:                    # начало строки
            (?:http|https)://)?     # протокол
            (?<domain>
                (?:[\w-]+\.){0,3}   # поддомены перед главным доменным именем
                (
                    $__site         # доменное имя или полная ссылка
                    (\.[\w]+)?      # зона
                )
            )
            (/.*)                   # месиво после домена
        ]xi";

        //просмотр всех элемнтов на странце(как правило их не более 15)
        $urlParse = array();
        foreach ($__elems as $position => $elem) {

            //просматриваются только отображаемые элемнты
            if (!$elem->isDisplayed())
            {
                continue;
            }

            //ссылка на сайт
            $href = $elem->getAttribute('href');

            //проверка подходит ли сайт регулярному выражению
            if (preg_match($regExp, $href, $urlParse)) {
                $regExpParse = '[
                    (?<domain>
                        ([\w-]+\.){0,3}         # поддомены перед главным доменным именем
                        (?<main_domain> [-\w]+ )  # главное доменное имя
                        (?: \.(?<zone> \w+) )        # зона
                    )
                ]xi';

                //выборка необходимой информации
                preg_match($regExpParse, $urlParse['domain'], $urlParse);
                return [
                    'position'    => $position,
                    'element'     => $elem,
                    'href'        => $href,
                    'domain'      => $urlParse['domain'],
                    'main_domain' => $urlParse['main_domain'],
                    'zone'        => $urlParse['zone'],
                ];
            }
        }

        //не найдено
        return false;
    }
}
