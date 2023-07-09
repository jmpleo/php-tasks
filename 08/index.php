<?php

// не подсвечивается текущая дата
// добавил названия. Текущий день подсвечивается зеленым

//текущая дата
define('TODAY_YEAR', (int)date('Y'));
define('TODAY_MONTH', (int)date('n'));
define('TODAY_DAY', (int)date('j'));
define('HOLIDAYS', array(

    //месяцы и праздничные дни
    '1'  => ['1', '2', '3', '4', '5', '6', '7', '8'],
    '2'  => ['23'],
    '3'  => ['8'],
    '4'  => ['1'],
    '5'  => ['9'],
    '6'  => ['12'],
    '7'  => [],
    '8'  => [],
    '9'  => ['1'],
    '10' => [],
    '11' => [],
    '12' => ['31'],
));

//обработка запроса
if (isset($_POST['year']) && isset($_POST['month'])) {

    //год и месяц
    $year   = $_POST['year'];
    $month  = $_POST['month'];
    $regExp = '/^[0-9]+$/';

    //проверка на корректность введенных данных
    if (preg_match($regExp, $year) === 0 || preg_match($regExp, $month) === 0
        || !checkdate($month, 1, $year)
    ) {
        exit();
    }

    //формирование ответа
    $response = getTable(getCalendarArr($year, $month), $month, $year);
}

//иначе - календарь на текущую дату
else {
    $response = getCalendar(getTable(getCalendarArr()));
}

//отаправка ответа
print $response;

/**
 * Максимальная длина подмассива исходного массива $__array
 *
 * @param array $__array Исходный массив.
 *
 * @return integer
 */
function maxSubArray(array $__array)
{
    $maxSize = 0;

    //проход слов в массиве
    foreach ($__array as $subArray) {
        $size = count($subArray);

        //если больше, то максимальный - $size
        if ($size > $maxSize) {
            $maxSize = $size;
        }
    }

    //возврат длины наибольшего слова
    return $maxSize;
}

/**
 * Возвращает массив месяца, содержащий массивы из дней в качестве недели
 *
 * @param integer $__year  Год.
 * @param integer $__month Месяц.

 * @return integer
 */
function getCalendarArr(int $__year=TODAY_YEAR, int $__month=TODAY_MONTH)
{
    //количество дней в месяце
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $__month, $__year);

    //классификация по неделям
    $weeksArr = array();

    //распределение по неделям
    $numberWeek = 0;
    for ($day = 1; $day <= $daysInMonth; $day++) {

        //день недели
        $current = mktime(0, 0, 0, $__month, $day, $__year);
        $weekDay = date('l', $current);

        //заполнение массива очередным днем
        $weeksArr[$numberWeek][] = $day;

        //если конец недели, то новая
        if ($weekDay === 'Sunday') {
            $numberWeek++;
        }
    }

    //первые и последние недели извлекаются для формирования пустых ячеек
    $sizeWeeks = maxSubArray($weeksArr);
    $firstWeek = array_shift($weeksArr);
    $lastWeek  = array_pop($weeksArr);

    //сформированная неделя
    $firstWeekMod = array_reverse(
        array_pad(array_reverse($firstWeek), $sizeWeeks, '')
    );

    //сформированная неделя
    $lastWeekMod = array_pad($lastWeek, $sizeWeeks, '');

    //вставка изменных недель
    array_unshift($weeksArr, $firstWeekMod);
    array_push($weeksArr, $lastWeekMod);
    return $weeksArr;
}

/**
 * Формирование страницы календаря
 *
 * @param string $__table Сформированная таблица календаря.
 *
 * @return string
 */
function getCalendar(string $__table)
{
    //обратная связь
    $inputYear  = '<input type="text" name="year" placeholder="year">';
    $inputMonth = '<input type="text" name="month" placeholder="month">';
    $submit     = '<input type="submit" id="send" value="Get Calendar">';
    $input      = '<div class="cal">'.$inputYear.$inputMonth.$submit.'</div>';
    $script     = '<script src="script.js"></script>';

    //страница
    return '<!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="utf-8">
                    <title>Calendar</title>
                    <link rel="stylesheet" href="style.css">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css"
                    rel="stylesheet"
                    integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x"
                    crossorigin="anonymous">
                </head>
                <body>
                    <div class="content">
                       '.$input.'
                       <div id="calendar">'.$__table.'</div>
                    </div>
                    '.$script.'
                </body>
            </html>';
}

/**
 * Формирование таблицы календаря
 *
 * @param array   $__weeksArr Массив с неделями месяца.
 * @param integer $__month    Номер месяца.
 * @param integer $__year     Номер года.
 *
 * @return string
 */
function getTable(array $__weeksArr, int $__month=TODAY_MONTH, int $__year=TODAY_YEAR)
{
    //строчки
    $table = '<table class="table table-bordered">
                <tr>
                    <td colspan="7" id="head">
                       '.date('F Y', mktime(0, 0, 0, $__month, 1, $__year)).'
                    </td>
                </tr>';
    foreach ($__weeksArr as $week) {

        //ячейки
        $table .= '<tr>';
        foreach ($week as $weekDay => $day) {

            //выходные
            $saturday = 5;
            $sunday   = 6;

            //Маркер определенных дней
            $isWeekend = $weekDay === $saturday || $weekDay === $sunday;
            $isHoliday = in_array($day, HOLIDAYS[$__month]);

            //метка сегодняшнего дня
            $isToday = mktime(
                0, 0, 0, (int)$__month, (int)$day, (int)$__year
            ) === mktime(0, 0, 0, TODAY_MONTH, TODAY_DAY, TODAY_YEAR);

            //рабочий день
            $class = 'work';

            //выходной
            if ($isWeekend) {
                $class = 'chill';
            }

            //праздник
            if ($isHoliday) {
                $class = 'holiday';
            }

            //текущий день
            if ($isToday) {
                $class = 'today';
            }

            //ячейка
            $table .= "<td class=\"$class\">".$day.'</td>';
        }

        //конец строчки
        $table .= '</tr>';
    }

    //конец таблицы
    $table .= '</table>';
    return $table;
}
