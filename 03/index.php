<?php

//массив фруктов, овощей, людей, предметов, планет
$wordArraysArr = array(
    
    //Фрукты
    array("apple", "pineapple", "banana", "orange", "mandarin", "mango"),
    
    //Овощи
    array("tomato", "carrot", "onion", "patato", "papper", "asparagus", "broccoli", "eggplant"),
    
    //Люди
    array("vanya", "oleg", "borya", "irina", "svyatoslav", "senya", "katya"),
    
    //Предметы
    array("table", "chair", "computer"),
    
    //Планеты
    array("mercury", "venus", "earth", "mars", "jupiter", "saturn", "Uranus", "Neptune"),
);

/**
 * Функция возвращающая длину самого большого слова в массиве
 * 
 * @param type array $__subArray Подмассив слов.
 * 
 * @return type int Длина наибольшего слова
 */
function lengthMaxWord(array $__subArray)
{
    $maxLength = 0;
    
    //проход слов в массиве
    foreach ($__subArray as $word) {
        $length = strlen($word);
        
        //если длина текущего слова больше длины наибольшего, то значение наибольшего = длине текущего
        if ($length > $maxLength) {
            $maxLength = $length;
        }
    }
    
    //возврат длины наибольшего слова
    return $maxLength;
}


/**
 * Функция возвращающая размер самого большого массива слов в заданном массиве
 *  
 * @param type array $__wordArraysArr Массив состоящий из массивов слов.
 * 
 * @return type int Размер наибольшего массива
 */
function sizeMaxSubArr(array $__wordArraysArr)
{
    $sizeMaxSubArr = 0;
    
    //проход по подмассивам
    foreach ($__wordArraysArr as $array) {
        $size = count($array);
        
        //если размер текущего массива больше, то размеру наибольшего присваивается размер текущего 
        if ($size > $sizeMaxSubArr) {
            $sizeMaxSubArr = $size;
        }
    }
    
    //возврат размера наибольшего подмассива 
    return $sizeMaxSubArr;
}

/**
 * Функция добавляет к подмассивам заданного массива строки вида: "  ",
 * где количество пробелов равно длине наибольшего слова в подмассиве.
 *
 * @param type array $__wordArraysArr Массив состоящий из массивов слов.
 * 
 * @return type bool
 */
function fillArrayBySeps(array &$__wordArraysArr)
{
    //проверка корректности параметра
    if (!isset($__wordArraysArr)) {
        return false;
    }
    
    //размер наибольшего подмассива
    $__countStrings = sizeMaxSubArr($__wordArraysArr);
   
    //проход по подмассиивам и добавление строк
    foreach ($__wordArraysArr as $key => $arrayOfWords) {
         
        //Составляется строка из пробелов, длины $countSeps равной длине наибольшего слова в подмассиве
        $countSeps      = lengthMaxWord($arrayOfWords);
        $stringWithSeps = str_pad('', $countSeps);
    
        //Строки добавляются в конец подмассива пока его размер не станет равным наибольшему размеру из подмассивов 
        for ($iter = count($arrayOfWords); $iter < $__countStrings; ++$iter) {
            $__wordArraysArr[$key][$iter] = $stringWithSeps;
        }
    }
    
    //возвращаю код успешного выполнения функции, Теперь я использую успешный возврат
    return true;
}

/**
 * Функция формирует вывод массива в нужном формате
 * 
 * @param type array $__wordArraysArr Массив состоящий из массивов слов.
 * 
 * @return type string Сформированная строка для вывода
 */
function formatting(array $__wordArraysArr)
{
    //количесвто строчек в выводе
    $countOfLines = sizeMaxSubArr($__wordArraysArr);
    
    //формируемая строка для вывода
    $string = "<pre>";
    
    //Цикл проходит по i-ым элементам подмассивов, чтобы образовать первую строчку в выводе 
    for ($line = 0; $line < $countOfLines; ++$line) {
        
        //Для коректного вывода в конец подмассивов добавляются строчки из пробелов.
        if (!fillArrayBySeps($__wordArraysArr)) {
            return 'Array was empty:(';
        }
        
        //Цикл проходит по подмассивам, чтобы образовать слова в i-ой строчке
        foreach ($__wordArraysArr as $columnIndex => $column) {
            
            //Длина наибольшего слова в подмассиве, необходима для формирования "столбца" слов в дальнейшем
            $columnWidth = lengthMaxWord($column);
          
            //если "столбец" четный, то выравнивается по правому краю - к слову добавляется строчка из пробелов слева
            //иначе по левому краю
            $columnIndex % 2 === 0 
                ? $string .= str_pad($column[$line], $columnWidth, ' ', STR_PAD_LEFT).' '
                : $string .= str_pad($column[$line], $columnWidth, ' ', STR_PAD_RIGHT).' ';
        }
        
        //Сформировав строчку - переход на новую
        $string .= "\n";
    }
    $string .= "</pre>";
    return $string;
}

//форматный вывод массива
print formatting($wordArraysArr);
