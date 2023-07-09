<?php

/**
 * 1) При ошибке сбрасывается система счисления.
 */

//пофиксил

//массив с системами счисления
$basesArr = array(
    'Bin'  => '01',
    'Dec'  => '0123456789',
    'Hex'  => '0123456789abcdef',
    'Oct'  => '01234567',
    'User' => 'qwerty'
);

//код пустого калькулятора
$html = getHtml($basesArr);

//если данные с формы отправлены, то обработка этих данных
if (isset($_POST['go'])) {
    
    //пока ошибок нет
    $errors = '';
    
    //цикл, для возможности резкого выхода с помощью break, при обнаружении ошибки
    do {
        
        //выбранная система
        $selectedBase = $_POST['base'];
        
        //если данные пришли не все, то ошибка 
        $enoughData = isset($_POST['number1']) && isset($_POST['number2']);
        if (!$enoughData) {
            $errors = 'Not enough input data';
            break;
        }
        
        //если данные пришли, но среди них пустые строки, то данных по-прежнему не хватает
        $isEmptyData = strlen($_POST['number1']) === 0 || strlen($_POST['number2']) === 0;
        if ($isEmptyData) {
            $errors = 'Not enough input data';
            break;
        }
        
        //алфавит системы и операция выбранные пользователем
        $alphabetBase = $basesArr[$_POST['base']];
        $operation    = $_POST['operation'];
       
        //если данные некорректные, то расстраиваем пользователя
        $incorrectionNumbers = isCorrect($alphabetBase, $_POST['number1']) === false || isCorrect($alphabetBase, $_POST['number2']) === false;
        if ($incorrectionNumbers) {
            $errors = 'The data does not match the selected number system!';
            break;
        }
        
        //для дальнейших вычислений
        $number1 = $_POST['number1'];
        $number2 = $_POST['number2'];
        
        //равен ли второй аргумент нулю, а операция разности
        $zero           = $alphabetBase[0];
        $num2IsZero     = $number2 === $zero;
        $operationIsDiv = $operation === 'div';
        
        //проверка делителя на нуль
        if ($operationIsDiv && $num2IsZero) {
            $errors = 'Second number is zero when division!';
            break;
        }
        
        //формирование результата
        $result = '';
       
        //вычисление результата соответсвующей операции
        switch ($operation) {
            case 'sum': $result = sum($alphabetBase, $number1, $number2);
                break;
            case 'diff': $result = diff($alphabetBase, $number1, $number2);
                break;
            case 'mult': $result = mult($alphabetBase, $number1, $number2);
                break;
            case 'div': $result = div($alphabetBase, $number1, $number2);
        }
        
        //код страницы с результатом вычисления
        $html = getHtml($basesArr, $selectedBase, $result);
    } while (0);
    
    //если ошибка обнаружена, то формирование страницы с ошибкой
    if ($errors) {
        $html = getHtml($basesArr, $selectedBase, '', $errors);
    }   
}

//печать калькулятора
print $html;




/**
 * Функция возвращает строку с html-кодом полей результата и ошибок
 * 
 * @param string $__result Результат вычислений.
 * @param string $__errors Найденные ошибки.
 * 
 * @return string
 */
function getAreaOutput(string $__result='', string $__errors='')
{   
    //строки с html-кодом результата и ошибок соответственно 
    $resultStr = '<input type="text" name="result" disabled value="'.$__result.'" class="res" placeholder="Result"></input>';
    $errorsStr = '<input type="text" name="errors" disabled value="'.$__errors.'" class="err" placeholder="Nice! There are no errors"></input>';
    return $resultStr.$errorsStr;
}

/**
 * Функция возвращает строку с html-кодом поля выражения
 * 
 * @param string $__result Результат вычислений.
 * 
 * @return string
 */
function getAreaExpression(string $__result='')
{
    //поля ввода чисел и кнопка "вычислить"
    $input1 = '<input type="text" name="number1" placeholder="Number 1" value="'.$__result.'"></input>';
    $input2 = '<input type="text" name="number2" placeholder="Number 2" value ="" autofocus></input>';
    $submit = '<input type="submit" name="go" value="=" class="brace"></input>';
    
    //список доступных операций
    $operations = '<select name="operation">
                    <option value="sum">+</option>
                    <option value="diff">-</option>
                    <option value="mult">*</option>
                    <option value="div">/</option>
                 </select>';
    
    //область с выражением
    $areaExpression = '<p>Expression</p>
                       <ul class="input">
                           <li>'.$input1.'</li><li class="operation">'.$operations.'</li><li>'.$input2.'</li>
                       </ul>'.$submit;
    return $areaExpression;
}

/**
 * Функция возвращает строку с html-кодом поля выбора системы
 * 
 * @param array  $__basesArr     Массив с доступными системами.
 * @param string $__selectedBase Выбранная система счисления.
 * 
 * @return string
 */
function getAreaBases(array $__basesArr, string $__selectedBase)
{
    $bases = '<select name="base">';
    
    //массив названий систем счисления
    $basesNames = array_keys($__basesArr);
    
    //запись имени системы в список
    foreach ($basesNames as $baseName) {
        
        //и если оказалась выбранной, то зафиксировать строку в списке 
        $bases .= $baseName === $__selectedBase
                ? '<option value="'.$baseName.'" selected>'.$baseName.'</option>'
                : '<option value="'.$baseName.'">'.$baseName.'</option>';
    }
    
    //список закрывается и возврат поля
    $bases    .= '</select>';
    $areaBases = '<ul class="base">
                    <li><p>Base:</p></li><li>'.$bases.'</li>
                  </ul>';
    return $areaBases;
}

/**
 * Формирование строки html-кода страницы
 * 
 * @param array  $__basesArr     Массив доступных систем счисления.
 * @param string $__selectedBase Имя/ключ выбранной системы.
 * @param string $__result       Результат вычислений.
 * @param string $__errors       Обнаруженные ошибки.
 * 
 * @return string
 */
function getHtml(array $__basesArr, string $__selectedBase='', string $__result='', string $__errors='')
{
    return '<!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="utf-8">
                    <title>Интегрирование онлайн без смс и регистрации|Посчитать производную онлайн</title>
                    <link rel="stylesheet" href="style.css">
                </head>
                <body>
                    <form action="index.php" method="POST">
                        <div class="calc">'
                            .getAreaBases($__basesArr, $__selectedBase).getAreaExpression($__result).getAreaOutput($__result, $__errors).
                        '</div>
                    </form>
                </body>
            </html>';
}


/**
 *  Функция провряет корректность объекта $__number, в случае успеха возвращает true
 * 
 *  @param string $__alphabetBase Алфавит системы счисления объекта $__number.
 *  @param string $__number       Объект требующий проверки.
 * 
 *  @return boolean
 */
function isCorrect(string $__alphabetBase, string $__number)
{
    //для определенности замена точки на запятую
    $numberNorm = str_replace('.', ',', $__number);
    
    //если запятых слишком много, то расстраиваем пользователя
    if (substr_count($numberNorm, ',') > 1) {
        return false;
    }
    
    //позиция начала стравнивнения символов объекта  
    $posStart = $numberNorm[0] === '-' ? 1 : 0;
    
    //если запятая следует после минуса или в начале, то ошибка 
    if (strpos($numberNorm, ',') === $posStart) {
        return false;
    }
    
    //проход символов в обЪекте на проверку их принадлежности алфавиту
    $lengthNum = strlen($numberNorm);
    for (; $posStart < $lengthNum; ++$posStart) {
        
        //если такого символа нема, то расстраиваем пользователя
        if (stripos($__alphabetBase, $numberNorm[$posStart]) === false && $numberNorm[$posStart] !== ',') {
            return false;
        }
    }
    
    //успешно
    return true;
}

/**
 * Функция переводит объект $__number соответствующий системе с алфавитом $__base в число системы dec
 * 
 * @param string $__alphabetBase Алфавит системы счисления числа $__number.
 * @param string $__number       Объект перевода.
 * 
 * @return float число в десятичной системе счисления
 */
function transformInDec(string $__alphabetBase, string $__number)
{
    //для определенности замена точки на запятую
    $numberNorm = str_replace('.', ',', $__number);
    
    //признак отрицательного объекта
    $isNeg = $numberNorm[0] === '-' ? true : false;
    
    //временное удаление знака объекта
    if ($isNeg) {
        $numberNorm = substr($numberNorm, 1);
    }
    
    //целая часть объекта до знака "," и дробная соответственно и их размеры
    $intPart         = strtok($numberNorm, ',');
    $fractPart       = strtok('');
    $lengthIntPart   = strlen($intPart);
    $lengthFractPart = strlen($fractPart);
    
    //номер системы счисления
    $numeralSystem = strlen($__alphabetBase);
    
    //классический алгоритм перевода в десятичную систему целой части
    $decNum = 0;
    for ($iter = 0; $iter < $lengthIntPart; ++$iter) {
        
        //Основание системы в соответстующей ему степени
        //Индекс объекта соответстующего $numeralSystemPow 
        $numeralSystemPow = $numeralSystem ** ($lengthIntPart - 1 - $iter);
        $numOfIndex       = strpos($__alphabetBase, $intPart[$iter]);
        
        //вычисление результата
        $decNum += $numeralSystemPow * $numOfIndex; 
    }
    
    //классический алгоритм перевода в десятичную систему дробной части
    for ($iter = 0; $iter < $lengthFractPart; ++$iter) {
        
        //Основание системы в соответстующей ему степени
        //Индекс объекта соответстующего $numeralSystemPow 
        $numeralSystemPow = $numeralSystem ** ( 0 - 1 - $iter);
        $numOfIndex       = strpos($__alphabetBase, $fractPart[$iter]);
        
        //вычисление результата
        $decNum += $numeralSystemPow * $numOfIndex; 
    }
    
    //возврат числа в десятичной системе с учетом знака
    return $isNeg ? -$decNum : $decNum;
}

/**
 * Функция переводит число $__number в десятичной системе, в объект системы заданой алфавитом $__base. 
 * 
 * @param string $__alphabetBase Алфавит системы счисления объекта.
 * @param float  $__number       Число перевода.
 * 
 * @return string объект системы $__base
 */
function transformDecInYourBase(string $__alphabetBase, float $__number)
{
    //признак отрицательного числа
    $isNeg = $__number < 0 ? true : false;
    
    //абсолютная величина числа
    $numberAbs = $isNeg ? -$__number : $__number;
   
    //целая и дробная части числа
    $intPart   = intval($numberAbs);
    $fractPart = $numberAbs - $intPart;
    
    //Номер системы счисления
    $numeralSystem = strlen($__alphabetBase);
    
    //формирование результата
    $result = '';
    
    //классический перевод целой части посредством деления и записи остатка
    while ($intPart >= $numeralSystem) {
        
        //запись остатка символами алфавита
        $remain  = $__alphabetBase[$intPart % $numeralSystem];
        $result  = "$remain".$result;
        $intPart = (int)($intPart / $numeralSystem);
    }
    
    //Запись целой части
    $result = "$__alphabetBase[$intPart]".$result.',';
    
    //классический перевод дробного числа. Точность определяется 10-ю итерациями, то есть 10 цифр после запятой.   
    for ($iter = 0; $iter < 10; ++$iter) {
        
        //домножение на номер системы и запись целой части в результат
        $fractPart *= $numeralSystem;
        $firstInt   = intval($fractPart);
        $result    .= "$__alphabetBase[$firstInt]";
        $fractPart -= $firstInt;
    }
    
    //возврат объекта с учетом знака
    return $isNeg ? "-$result" : "$result";
}

/**
 * Функция суммы, но только для корректных объектов
 * 
 * @param string $__alphabetBase  Aлфавит системы счисления.
 * @param string $__firstSummand  Первое слагаемое.
 * @param string $__secondSummand Второе слагаемое.
 * 
 * @return string 
 */
function sum(string $__alphabetBase, string $__firstSummand, string $__secondSummand)
{
    //перевод в десятичную
    $decAnalog1 = transformInDec($__alphabetBase, $__firstSummand);
    $decAnalog2 = transformInDec($__alphabetBase, $__secondSummand);
    
    //сложение
    $resultDec = $decAnalog1 + $decAnalog2;
    
    //перевод в систему заданную алфавитом, и возврат
    return transformDecInYourBase($__alphabetBase, $resultDec);
}

/**
 * Функция произведения, но только для корректных объектов
 * 
 * @param string $__alphabetBase     Aлфавит системы счисления.
 * @param string $__firstMultiplier  Множитель.
 * @param string $__secondMultiplier Множитель.
 * 
 * @return string 
 */
function mult(string $__alphabetBase, string $__firstMultiplier, string $__secondMultiplier)
{
    //перевод в десятичную
    $decAnalog1 = transformInDec($__alphabetBase, $__firstMultiplier);
    $decAnalog2 = transformInDec($__alphabetBase, $__secondMultiplier);
    
    //умножение
    $resultDec = $decAnalog1 * $decAnalog2;
    
    //перевод в систему заданную алфавитом, и возврат
    return transformDecInYourBase($__alphabetBase, $resultDec);
}

/**
 * Функция разности, но только для корректных объектов
 * 
 * @param string $__alphabetBase Aлфавит системы счисления.
 * @param string $__reducible    Уменьшаемое.
 * @param string $__deductible   Вычитаемое.
 * 
 * @return string 
 */
function diff(string $__alphabetBase, string $__reducible, string $__deductible)
{
    //перевод в десятичную
    $decAnalog1 = transformInDec($__alphabetBase, $__reducible);
    $decAnalog2 = transformInDec($__alphabetBase, $__deductible);
    
    //разность
    $resultDec = $decAnalog1 - $decAnalog2;
    
    //перевод и возврат обЪекта
    return transformDecInYourBase($__alphabetBase, $resultDec);
}

/**
 * Функция частного, но только для корректных объектов
 * 
 * @param string $__alphabetBase Aлфавит системы счисления.
 * @param string $__divisible    Делимое.
 * @param string $__divisor      Делитель.
 * 
 * @return string 
 */
function div(string $__alphabetBase, string $__divisible, string $__divisor)
{
    //перевод в десятичную
    $decAnalog1 = transformInDec($__alphabetBase, $__divisible);
    $decAnalog2 = transformInDec($__alphabetBase, $__divisor);
    
    //деление
    $resultDec = $decAnalog1 / $decAnalog2;
    
    //перевод и возврат обЪекта
    return transformDecInYourBase($__alphabetBase, $resultDec);
}

