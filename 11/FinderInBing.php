<?php

//функционал
require_once 'FunctionalFinder.php';
use Facebook\WebDriver\WebDriverKeys;

/**
 * Искатель в выдаче поисковиком Bing
 *
 * @author g193219
 */
class FinderInBing extends FunctionalFinder
{
    public function ToFind()
    {
        //переход на страницу и мониторинг ошибок
        $this->GoToUrl('https://bing.com');
        if ($this->thereError) {
            return $this->thereError;
        }

        //Поиск поля ввода текста
        $input = $this->WaitAndGive('//div/form/input[1]');
        if ($this->thereError) {
            return $this->thereError;
        }

        //Ввод запроса
        $input->sendKeys($this->request.WebDriverKeys::ENTER);

        //xpath путь до элемента(ов)
        $xpathNextPage = "//li/a[@title='Следующая страница']";
        $xpathSite    = '//li/div[@class=\'b_title\']/h2/a';

        //поиск сайта
        $position = 1;
        while ($position < $this->depth) {

            //нахождение элементов
            $elems = $this->WaitAndGiveAll($xpathSite);
            if ($this->thereError) {
                return $this->thereError;
            }

            //присутствует ли сайт в массиве
            $thisSite = self::ThisSite($elems, $this->site);
            if ($thisSite) {

                //ссылка на сайт
                $href = $thisSite['href'];

                //элемент содержащий описание
                $xPathDescription = "//a[@href='".$href."']/parent::h2/following::div/p";

                //информация о сайте
                $info = [];
                $info['url']         = $thisSite['href'];
                $info['full_domain'] = $thisSite['domain'];
                $info['domain']      = $thisSite['main_domain'];
                $info['description'] = $this->WaitAndGive($xPathDescription)->getText();
                $info['header']      = $thisSite['element']->getText();
                $info['position']    = $position + $thisSite['position'];
                $info['zone']        = $thisSite['zone'];

                //прокрутка до сайта и клик по нему, чтобы проверить доступность
                $this->ScrollTo($thisSite['element']);
                $thisSite['element']->click();

                //проверка доступности
                $info['availability'] = substr_count(
                    $this->GetProperty($this->WaitAndGive('//html'), 'baseURI'),
                    'chrome-error'
                ) ? "Unavailabel" : "Available";
                return $info;
            }

            //элемент перехода на следующаю страницу
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
