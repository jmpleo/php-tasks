<?php

//шаг
define('STEP', 20);

//класс шаблонизатора
require_once 'Template.php';

//шаблон
$template = file_get_contents('template.tpl');

//исходный массив
$carsArr = array(
    ['manufactor' => 'lada', 'model' => 'calina', 'hp' => 10,],
    ['manufactor' => 'lada', 'model' => 'malina', 'hp' => 15,],
    ['manufactor' => 'mazda', 'model' => 'mx-5', 'hp' => 16,],
    ['manufactor' => 'mazda', 'model' => 'mx-1', 'hp' => 180,],
    ['manufactor' => 'toyota', 'model' => 'malina', 'hp' => 210,],
    ['manufactor' => 'hyundai', 'model' => 'malina', 'hp' => 60,],
    ['manufactor' => 'lexus', 'model' => 'm-4', 'hp' => 78,],
    ['manufactor' => 'porsche', 'model' => 't5', 'hp' => 92,],
    ['manufactor' => 'lamborghini', 'model' => 'aventodor', 'hp' => 1000,],
    ['manufactor' => 'lada', 'model' => '9', 'hp' => 10000,],
  );

//вывод таблички
$dataArr = array('hp_cycle' => hpCycleParse($carsArr));
print Template::build($template, $dataArr);

/**
 * Формиование цикла hp_cycle следующего вида:
 * [
 *    [
 *       'hp' => '0-50',
 *       'manufactor_cycle' => [
 *           [
 *               'rowspan'    => 3,
 *               'manufactor' => 'mazda',
 *               'cars_cycle' => [
 *                   ['model' => '3x', 'hp' => '30'],
 *                   ['model' => '1x', 'hp' => '10'],
 *                   ['model' => '4x', 'hp' => '40'],
 *             ]
 *          ],
 *           ...
 *
 * @param array $__carsArr Исходный массив.
 *
 * @return array
 */
function hpCycleParse(array $__carsArr)
{
    //сортировка по hp
    $hpSortArr  = hpSort($__carsArr);
    $hpCycleArr = array();

    //формирование цикла
    foreach ($hpSortArr as $hpRange => $hpCars) {

        //формирование цикла
        $hpCycleArr[] = array(
                         'hp'               => $hpRange,
                         'manufactor_cycle' => manufactCycleParse($hpCars)
                     );
    }

    //цикл
    return $hpCycleArr;
}

/**
 * Формирование цикла для машин одного производителя
 *
 * @param array $__carsArr Массив машин.
 *
 * @return array
 */
function manufactCycleParse(array $__carsArr)
{
    //распределение по производителям
    $allCarsArr = array();
    foreach ($__carsArr as $car) {
        $manufactor = array_shift($car);
        $allCarsArr[$manufactor][] = $car;
    }

    //формирование цикла
    $manufactorCycleArr = array();
    $manufactorCarsArr  = array();
    foreach ($allCarsArr as $manufactor => $cars) {

        //формирование цикла
        $manufactorCarsArr['rowspan']    = count($cars);
        $manufactorCarsArr['manufactor'] = $manufactor;
        $manufactorCarsArr['cars_cycle'] = $cars;

        //очередной производитель
        $manufactorCycleArr[] = $manufactorCarsArr;
    }

    //цикл
    return $manufactorCycleArr;
}

/**
 * Распределение машин по hp
 *
 * @param array $__carsArr Исходный массив.
 *
 * @return array
 */
function hpSort(array $__carsArr)
{
    //Сортировка по возрастанию hp
    usort($__carsArr, "hpCompare");
    $resArr = array();

    //начальные значения диапазона
    $nextStep = 0;
    $prevStep = -STEP;
    foreach ($__carsArr as $car) {

        //добавляется шаг, пока не найдется подходящий диапазон для машины
        $inRange = $car['hp'] > $prevStep && $car['hp'] <= $nextStep;
        while (!$inRange) {
            $nextStep += STEP;
            $prevStep += STEP;
            $inRange   = $car['hp'] > $prevStep && $car['hp'] <= $nextStep;
        }

        //запись машины в нужный диапазон
        $resArr["$prevStep - $nextStep h.p."][] = $car;
    }

    //возврат сортированного массива
    return $resArr;
}

/**
 * Функция сравнения машин по hp
 *
 * @param array $__car1 Характеристики машины.
 * @param array $__car2 Характеристики машины.
 *
 * @return integer
 */
function hpCompare(array $__car1, array $__car2)
{
    if ($__car1['hp'] === $__car2['hp']) {
        return 0;
    }

    //
    return $__car1['hp'] > $__car2['hp'] ? 1 : -1;
}
