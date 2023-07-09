<?php

//функционал
require_once 'FunctionalFinder.php';
use Facebook\WebDriver\WebDriverKeys;

/**
 * Искатель в выдаче поисковиком Google
 *
 * @author g193219
 */
class FinderInGoogle extends FunctionalFinder
{
    public function ToFind()
    {
        //переход на страницу и мониторинг ошибок
        $this->GoToUrl('https://www.google.com');
        if ($this->thereError) {
            return $this->thereError;
        }

        //Поиск поля ввода текста
        $input = $this->WaitAndGive('//input[@title=\'Поиск\']');
        if ($this->thereError) {
            return $this->thereError;
        }

        //Ввод запроса
        $input->sendKeys($this->request.WebDriverKeys::ENTER);

        //xpath путь до элемента(ов)
        $xpathNextPage = "//h1[text()='Навигация по страницам']"
                . '/following-sibling::table//tr/td[last()]/a';
        $xpathHideRes = "//a[text()='Показать скрытые результаты']|//i/a";
        $xpathSite    = "//cite/../parent::a";

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

                //Элемненты содержащие информацию о сайте
                $xPathDescription = "//a[@href='".$href."']/parent::div/following-sibling::div[1]";
                $xPathHeader      = "//a[@href='".$href."']/h3";

                //инфрормация о сайте
                $info = [];
                $info['url']         = $thisSite['href'];
                $info['full_domain'] = $thisSite['domain'];
                $info['domain']      = $thisSite['main_domain'];
                $info['description'] = $this->WaitAndGive($xPathDescription)->getText();
                $info['header']      = $this->WaitAndGive($xPathHeader)->getText();
                $info['position']    = $position + $thisSite['position'];
                $info['zone']        = $thisSite['zone'];

                //прокрутка до сайта и клик на него
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
            $next  = $this->WaitAndGive($xpathNextPage) ?? $this->WaitAndGive($xpathHideRes);
            if ($this->thereError) {
                return $this->thereError;
            }

            //проверка на скрытые результаты
            if ($next->getText() == 'Показать скрытые результаты') {
                $this->ScrollTo($next);
                $next->click();
                $position = 1;
                continue;
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
