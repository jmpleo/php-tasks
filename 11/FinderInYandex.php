<?php

//фунционал
require_once 'FunctionalFinder.php';
use Facebook\WebDriver\WebDriverKeys;

/**
 * Искатель в выдаче поисковиком Яндекс
 *
 * @author g193219
 */
class FinderInYandex extends FunctionalFinder
{
    public function ToFind()
    {
        //переход на страницу и мониторинг ошибок
        $this->GoToUrl('https://yandex.ru');
        if ($this->thereError) {
            return $this->thereError;
        }

        //Поиск поля для ввода
        $input = $this->WaitAndGive('//input[@id=\'text\']');
        if ($this->thereError) {
            return $this->thereError;
        }

        //Ввод запроса
        $input->sendKeys($this->request.WebDriverKeys::ENTER);

        //xpath путь до элемента(ов)
        $xpathNextPage = "//a[@aria-label='Следующая страница']";
        $xpathSite    = '//h2/a';

        //поиск сайта
        $position = 1;
        while ($position < $this->depth) {

            //нахождение элементов
            $elems = $this->WaitAndGiveAll($xpathSite);
            if ($this->thereError) {
                return $this->thereError;
            }

            //если сайт оказался в среди найденных элементов
            $thisSite = self::ThisSite($elems, $this->site);
            if ($thisSite) {

                //ссылка на сайт
                $href = $thisSite['href'];

                //элементы содержащие описание
                $xPathHeader      = "//h2/a[@href='".$href."']/div[last()]";
                $xPathDescription = "//a[@href='".$href."']"
                        . "/parent::h2/following-sibling::div[last()]";
                //информация о сайте
                $info = [];
                $info['url']         = $thisSite['href'];
                $info['full_domain'] = $thisSite['domain'];
                $info['domain']      = $thisSite['main_domain'];
                $info['description'] = $this->WaitAndGive($xPathDescription)->getText();
                $info['header']      = $this->WaitAndGive($xPathHeader)->getText();
                $info['position']    = $position + $thisSite['position'];
                $info['zone']        = $thisSite['zone'];

                //прокрутко до элемента и клик на него
                $this->ScrollTo($thisSite['element']);
                $thisSite['element']->click();

                //проверка доступности
                $info['availability'] = substr_count(
                    $this->GetProperty($this->WaitAndGive('//html'), 'baseURI'),
                    'chrome-error'
                ) ? "Unavailabel" : "Available";
                return $info;
            }

            //элемент перехода на следующую страницу
            $next  = $this->WaitAndGive($xpathNextPage);
            if ($this->thereError) {
                return $this->thereError;
            }

            //переход на следующую страницу
            $position += count($elems);
            $this->ScrollTo($next);
            $next->click();
        }

        //сайт не был найден
        return 'Not found among: '.$this->depth;
    }
}
