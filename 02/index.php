<?php

/**
 *  Нерекурсивная функция, вычисляющая член последовательности чисел Фибоначчи
 *
 *  @param type int $__index Номер интересующего члена последовательности.
 *
 *  @return int - Член последовательности с номером $__index
 */
function fibonacci(int $__index)
{

    //если меньше 1, то возврат 0
    if ($__index < 1) {
        return 0;
    }

    //переменные являющиеся смежными членами последовательности
    $previous = 1;
    $current  = 1;

    //проход последовательности двумя смежными членами
    for ($iter = 1; $iter < $__index; ++$iter) {
        $temp     = $current;
        $current  = $previous + $current;
        $previous = $temp;
    }

    //возвращаю
    return $previous;
}

/**
 *  Рекурсивная функция, вычисляющая член последовательности чисел Фибоначчи
 *
 *  @param type int $__index Номер интересующего члена последовательности.
 *
 *  @return int - Член последовательности с номером $__index
 */
function fibonacciRecursive(int $__index)
{

    //если меньше 1, то возврат 0
    if ($__index < 1) {
        return 0;
    }

    //если член последовательности первый или второй, то возвращаю 1
    if ($__index === 1 || $__index === 2) {
        return 1;
    }

    //возврат суммы предыдущих смежных членов
    return fibonacciRecursive($__index - 1) + fibonacciRecursive($__index - 2);
}

/**
 *  //вывод последовательности в строку
 *
 *  @return string - Строка с с членами последовательности
 */
function outputFibbanacci()
{
    //вывод нерекурсивно
    $string = '';
    for ($iter = 0; $iter < 10; ++$iter) {
        $string .= fibonacci($iter).' ';
    }

    //вывод рекусивно
    $string .= "</br>";
    for ($iter = 0; $iter < 10; ++$iter) {
        $string .= fibonacciRecursive($iter).' ';
    }

    //возврат строки
    return $string;
}

//печать
print outputFibbanacci();
