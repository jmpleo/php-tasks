<?php
// Поправите замечания в 6 ПЗ, этого достаточно
session_start();

//количество сообщений на странице
define('MESSAGE_ON_PAGE', 2);

//окружение в сервисном блоке ссылок
define('PAGE_AROUND', 1);

//определение максимальной длины пароля и логина
define('MAX_LEN_LOG_OR_PASS', 20);

//группа администраторов
define('ADMINS_GROUP', array('admin1', 'admin2'));

//обозначение гостевого аккаунта
define('GUEST', '/guest');

//начальная страница
$html = getAuthorization();

//--------------------------Обработка запросов----------------------------------

//цикл, для резкого перехода при обработке соответсвующего запроса
do{

    //если ничего не отправленно - выход из из цикла и печать
    if (empty($_POST)) {
        break;
    }

    //переход на страницу авторизации, при нажатии кнопок авторизации и выхода
    if (isset($_POST['authPage']) || isset($_POST['logOut'])) {
        $html = getAuthorization();
        unset($_SESSION);
        session_destroy();
        break;
    }

    //переход на страницу регистрации
    if (isset($_POST['registrationPage'])) {
        $html = getRegistration();
        break;
    }

    //войти как гость
    if (isset($_POST['logInAsGuest'])) {

        //файл с данными об отправленных сообщениях
        $fileName = 'guestbook.txt';
        touch($fileName);

        //чтение файла
        $infoFp = fopen($fileName, 'r');
        flock($infoFp, LOCK_SH);
        $str = '';
        while (!feof($infoFp)) {
            $str .= fgets($infoFp);
        }

        //закрытие файла
        fclose($infoFp);

        //создание массива данных о сообщениях
        //формирование страницы гостя с соответствующими данными
        $infoArr = $str ? myUnSerialize($str) : array();
        $html    = getMainPage($infoArr);

        //запись в сессию, что зашел гость
        $_SESSION['login']   = GUEST;
        $_SESSION['numPage'] = 1;

        //выход и печать
        break;
    }

    //авторизация пользователя
    if (isset($_POST['logIn'])) {

        //ошибки нет
        $error = '';
        do {

            //запись пароля и логина
            $login    = $_POST['login_auth'];
            $password = $_POST['password_auth'];

            //проверка введенных данных
            if (!correctInput($login) || !correctInput($password)) {
                $error = 'Uncorrect input data!';
                break;
            }

            //файл с данными авторизированных пользователей
            $dataFileName = 'data.txt';
            touch($dataFileName);

            //чтение файла
            $dataFp = fopen($dataFileName, 'r');
            flock($dataFp, LOCK_SH);
            $users = '';
            while (!feof($dataFp)) {
                $users .= fgets($dataFp);
            }

            //закрытие файла
            fclose($dataFp);

            //формирование массива {... [login] => [hashPass] ...}
            $usersArr = strlen($users) ? unserialize($users) : array();

            //проверка пользователя на регистрацию
            if (!array_key_exists($login, $usersArr)) {
                $error = 'The user is not registered';
                break;
            }

            //проверка на совпадение хэш паролей
            $hashPass = md5($password);
            if ($usersArr[$login] !== $hashPass) {
                $error = 'Wrong password';
                break;
            }

            //файл сообщений
            $infoFileName = 'guestbook.txt';
            touch($infoFileName);

            //чтение
            $infoFp = fopen($infoFileName, 'r');
            flock($infoFp, LOCK_SH);
            $str = '';
            while (!feof($infoFp)) {
                $str .= fgets($infoFp);
            }

            //закрытие файла
            fclose($infoFp);

            //формирование массива данных об отправленных сообщениях
            $infoArr = $str ? myUnSerialize($str) : array();

            //формирование страницы пользователя
            $html = getMainPage($infoArr, $login);

            //запись логина в сессию
            $_SESSION['login']   = $login;
            $_SESSION['numPage'] = 1;
        } while (0);

        //если ошибки есть, то формирование страницы с ошибкой
        //и выход из цикла
        if ($error) {
            $html = getAuthorization($error);
            break;
        }
    }

    //регистрация
    if (isset($_POST['register'])) {

        //ошибок нет
        $error = '';
        do {

            //запись логина и пароля
            $login    = $_POST['login_reg'];
            $password = $_POST['password_reg'];

            //проверка введенных данных на корректность
            if (!correctInput($login) || !correctInput($password)) {
                $error = 'Uncorrect input data!';
                break;
            }

            //файл
            $dataFileName = 'data.txt';
            touch($dataFileName);

            //открытие файла для чтения и возможной записи
            $dataFp = fopen($dataFileName, 'r+');
            flock($dataFp, LOCK_EX);
            $users = '';
            while (!feof($dataFp)) {
                $users .= fgets($dataFp);
            }

            //формирования массива данных о зарегистрированных пользователях
            $usersArr = $users ? unserialize($users) : array();

            //проверка на совпадение логина уже зарегистрированного пользователя
            if (array_key_exists($login, $usersArr)) {
                fclose($dataFp);
                $error = 'The user this login is exist';
                break;
            }

            //очистка файла
            ftruncate($dataFp, 0);
            fseek($dataFp, 0, SEEK_SET);

            //добавление нового пользователя
            $usersArr[$login] = md5($password);

            //запись нового массива в файл
            $dataStr = serialize($usersArr);
            fputs($dataFp, $dataStr);
            fclose($dataFp);

            //файл сообщений
            $infoFileName = 'guestbook.txt';
            touch($infoFileName);

            //открытие файла для чтения
            $infoFp = fopen($infoFileName, 'r');
            flock($infoFp, LOCK_SH);
            $str = '';
            while (!feof($infoFp)) {
                $str .= fgets($infoFp);
            }

            //закрытие
            fclose($infoFp);

            //формирование массива с данными об отправленных сообщений
            $infoArr = $str ? myUnSerialize($str) : array();

            //формирование страницы пользователя
            $html = getMainPage($infoArr, $login);

            //запись логина в сессию
            $_SESSION['login'] = $login;
        } while (0);

        //проверка ошибок
        if ($error) {
            $html = getRegistration($error);
        }
    }

    //Формирование страницы с номером numPage
    if (isset($_POST['numPage'])) {

        //проверка на авторизацию пользователя
        if (!isset($_SESSION['login'])) {
            $html = getAuthorization();
            break;
        }

        //файл с данными о сообщениях
        $infoFileName = 'guestbook.txt';
        touch($infoFileName);

        //открытие файла для чтения
        $infoFp = fopen($infoFileName, 'r');
        flock($infoFp, LOCK_SH);
        $str = '';
        while (!feof($infoFp)) {
            $str .= fgets($infoFp);
        }

        //формирование массива данных
        $infoArr = $str ? myUnSerialize($str) : array();

        //запись логина
        //преобразование строки в число
        $login   = $_SESSION['login'];
        $numPage = (int)$_POST['numPage'];

        //запись страницы в сессию
        $_SESSION['numPage'] = $numPage;

        //формирование страницы для пользователя с номером numPage
        $html = getMainPage($infoArr, $login, $numPage);
    }

    //Отправление сообщения
    if (isset($_POST['send'])) {

        //файл с данными о сообщениях
        $infoFileName = 'guestbook.txt';
        touch($infoFileName);

        //открытие для чтения и не только
        $infoFp = fopen($infoFileName, 'r+');
        flock($infoFp, LOCK_EX);
        $str = '';
        while (!feof($infoFp)) {
            $str .= fgets($infoFp);
        }

        //формирование массива данных
        $infoArr = $str ? myUnSerialize($str) : array();

        //проверка на авторизацию
        if (!isset($_SESSION['login'])) {
            break;
        }

        //запись логина и сообщения
        $login     = $_SESSION['login'];
        $predmMess = isset($_POST['message']) ? trim($_POST['message']) : '';

        //запрещенные слова
        $forbidden = str_replace('[:|||:]', '/forbidden word/', $predmMess);
        $message   = str_replace('[:||:]', '/forbidden word/', $forbidden);

        //сообщение не записывается при пустом сообщении
        if (strlen($message) === 0) {
            $html = getMainPage($infoArr, $login);
            fclose($infoFp);
            break;
        }

        //дата записи, адрес
        $date    = date('H:i:s j.m.Y');
        $address = $_SERVER['REMOTE_ADDR'];

        //информация о сообщении
        $newMessageArr = array("$login", "$date", "$message", "$address");


        //запсись в общий массив данными о новом сообщении
        $infoArr[]  = $newMessageArr;
        $strNewData = mySerialize($infoArr);

        //очистка файла
        ftruncate($infoFp, 0);
        fseek($infoFp, 0, SEEK_SET);

        //запись массива
        fputs($infoFp, $strNewData);
        fclose($infoFp);

        //страница пользователя с новыми сообщением
        $html = getMainPage($infoArr, $login);
    }

    //удаление сообщения
    if (isset($_POST['remove'])) {

        //проверка авторизации
        if (!isset($_SESSION['login'])) {
            break;
        }

        //идентификатор блока который необходимо удалить
        $keyDel = (int)$_POST['remove'];

        //файл с данными о сообщениях
        $infoFileName = 'guestbook.txt';
        touch($infoFileName);

        //открытие файла для чтения и изменения
        $infoFp = fopen($infoFileName, 'r+');
        flock($infoFp, LOCK_EX);
        $str = '';
        while (!feof($infoFp)) {
            $str .= fgets($infoFp);
        }

        //формирование массива данных
        $infoArr = $str ? myUnSerialize($str) : array();

        //удаление сообщения с соответствующим ключом
        unset($infoArr[$keyDel]);
        $strNewData = mySerialize($infoArr);

        //очистка файла
        ftruncate($infoFp, 0);
        fseek($infoFp, 0, SEEK_SET);

        //запись новых данных без удаленного сообщения
        fputs($infoFp, $strNewData);
        fclose($infoFp);

        //пользователь
        $login   = $_SESSION['login'];
        $numPage = isset($_SESSION['numPage']) ? $_SESSION['numPage'] : 1 ;

        //страница с актуальными данными
        $html = getMainPage($infoArr, $login, $numPage);
        break;
    }

    //обработка запроса редактирования
    if (isset($_POST['editWindow'])) {

        //файл с данными о сообщениях
        $infoFileName = 'guestbook.txt';
        touch($infoFileName);

        //открытие файла для чтения
        $infoFp = fopen($infoFileName, 'r');
        flock($infoFp, LOCK_SH);
        $str = '';
        while (!feof($infoFp)) {
            $str .= fgets($infoFp);
        }

        //закрытие файла
        fclose($infoFp);

        //формирование массива данных
        $infoArr = $str ? myUnSerialize($str) : array();

        //ключи изменяемого сообщения, и текста в нем
        $keyMess = (int)$_POST['editWindow'];
        $keyText = 2;

        //запись ключа изменяемого сообщения
        $_SESSION['keyEditMess'] = $keyMess;

        //формаитруемый текст
        $text = $infoArr[$keyMess][$keyText];

        //страница редакции
        $html = getEditor($text);
        break;
    }

    //изменить текст сообщения
    if (isset($_POST['edit'])) {

        //файл с данными о сообщениях
        $infoFileName = 'guestbook.txt';
        touch($infoFileName);

        //открытие файла для чтения и поправок
        $infoFp = fopen($infoFileName, 'r+');
        flock($infoFp, LOCK_EX);
        $str = '';
        while (!feof($infoFp)) {
            $str .= fgets($infoFp);
        }

        //формирование массива данных
        $infoArr = $str ? myUnSerialize($str) : array();

        //проверка на авторизацию
        if (!isset($_SESSION['login'])) {
            break;
        }

		//запись логина, ключа изменяемого сообщения и изменненного сообщения
        $login    = $_SESSION['login'];
        $keyMess  = $_SESSION['keyEditMess'];
		$predMess = isset($_POST['messageEdited']) ? trim($_POST['messageEdited']) : '';

		//запрещенные слова
        $forbidden = str_replace('[:|||:]', '/forbidden word/', $predMess);
        $editMess   = str_replace('[:||:]', '/forbidden word/', $forbidden);
        $numPage  = isset($_SESSION['numPage']) ? $_SESSION['numPage'] : 1;

        //сообщение не записывается при пустом вводе
        if (strlen($editMess) === 0) {
            $html = getMainPage($infoArr, $login, $numPage);
            fclose($infoFp);
            break;
        }

        //метка изменненого сообщения
        $thisDate = date('H:i:s j.m.Y');

        //запсись в общий массив данными о новом сообщении;
        $infoArr[$keyMess][2] = $editMess;
        $infoArr[$keyMess][1] = "<i>edited:</i> $thisDate";

        //запись новых данных
        $strNewData = mySerialize($infoArr);

        //очистка файла
        ftruncate($infoFp, 0);
        fseek($infoFp, 0, SEEK_SET);

        //запись массива
        fputs($infoFp, $strNewData);
        fclose($infoFp);

        //страница пользователя с новыми сообщением
        $html = getMainPage($infoArr, $login, $numPage);
        break;
    }

    //вернуться из редактора
    if (isset($_POST['back'])) {

        //проверка авторизации
        if (!isset($_SESSION['login'])) {
            break;
        }

        //файл с данными о сообщениях
        $infoFileName = 'guestbook.txt';
        touch($infoFileName);

        //открытие файла для чтения
        $infoFp = fopen($infoFileName, 'r');
        flock($infoFp, LOCK_SH);
        $str = '';
        while (!feof($infoFp)) {
            $str .= fgets($infoFp);
        }

        //закрытие файла
        fclose($infoFp);

        //формирование массива данных
        $infoArr = $str ? myUnSerialize($str) : array();

        //формирование страницы
        $login   = $_SESSION['login'];
        $numPage = isset($_SESSION['numPage']) ? $_SESSION['numPage'] : 1;
        $html    = getMainPage($infoArr, $login, $numPage);
    }
} while (0);

//печать страницы
print $html;

//-------------html разметка регистрации и авторизации--------------------------

/**
 * Функция возвращает строку кнопок входа и кнопки регистрации
 *
 * @return string
 */
function getButtons()
{
    //возврат кнопок входа и кнопки регистрации
    $logIn        = '<input type="submit" name="logIn" value="Log In">';
    $logInAsGuest = '<input type="submit" name="logInAsGuest" value="As guest">';
    $registerNow  = '<input type="submit" name="registrationPage" value="Register now">';
    return $logIn.$logInAsGuest.$registerNow;
}

/**
 * Функция возвращает окно с ошибкой если параметр $__error не пуст
 * и пустую строку иначе
 *
 * @param string $__error Строка с ошибкой.
 *
 * @return string
 */
function getError(string $__error='')
{
    //возврат окна с ошибкой если строка с ошибками не пуста
    $errorWindow = '<input type="text" name="error"
                                       value="'.$__error.'" disabled id="err">';
    return strlen($__error) !== 0 ? $errorWindow : '';
}

/**
 * Функция формирует страницу авторизации
 *
 * @param string $__error Строка с ошибкой.
 *
 * @return string
 */
function getAuthorization(string $__error='')
{
    //блок ввода
    $login    = '<input type="text" autofocus name="login_auth" autocomplete="off" placeholder="Login">';
    $password = '<input type="password" name="password_auth" autocomplete="off" placeholder="Password">';
    $input    = $login.$password;

    //формирование формы
    $form = '<form action="index.php" method="POST"><div>
            '.$input.getButtons().getError($__error).'</div></form>';

    //формирование страницы авторизации
    $html = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>Форум программистов</title>
                <link rel="stylesheet" href="styleLogIn.css">
            </head>
            <body>'.$form.'</body></html>';

    //возврат страницы авторизации
    return $html;
}

/**
 * Функция формирует страницу регистрации
 *
 * @param string $__error Строка с ошибкой.
 *
 * @return string
 */
function getRegistration(string $__error='')
{

    //блок ввода
    $login    = '<input type="text" autofocus name="login_reg" autocomplete="off" placeholder="Login">';
    $password = '<input type="password" name="password_reg" autocomplete="off" placeholder="Password">';
    $input    = $login.$password;

    //формирование формы
    $buttReg = '<input type="submit" name="register" value="Register">';
    $form    = '<form action="index.php" method="POST">
                <div>'.$input.$buttReg.getError($__error).'</div>
                </form>';

    //формирование страницы регистрации
    $html = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>Guestbook</title>
                <link rel="stylesheet" href="styleLogIn.css">
            </head>
            <body>'.$form.'</body></html>';

    //возврат страницы регистрации
    return $html;
}

//------------html разметка страниц гостя, пользователя и админа----------------

/**
 * Функция формирует блок с сообщением пользователя
 *
 * @param string  $__nameUser  Имя пользователя.
 * @param string  $__message   Сообщение пользователя.
 * @param string  $__date      Дата отправления.
 * @param string  $__id        Инденитификатор блока.
 * @param boolean $__addButton Добавление кнопки удаления сообщения, если true.
 * @param string  $__address   IP-адрес пользователя.
 *
 * @return string
 */
function getMessageBlock(
    string $__nameUser, string $__message,
    string $__date,     string $__id,
    bool $__addButton=false, string $__address=''
)
{
    //сообщение
    $mess = '<textarea rows="3" cols="50" disabled>'.$__message.'</textarea>';

    //кнопка редактирования
    $edit       = '<button name="editWindow" value='."$__id".'" class="buttonEdit">
        edit</button>';
    $buttonEdit = $__addButton ? $edit : '';

    //кнопка удаления сообщения
    $button       = '<button name="remove" value='."$__id".'" class="buttonRed">
        delete</button>';
    $buttonRemove = $__addButton ? $button : '';

    //инструмены, информация о сообщении
    $tool = '<div class="tool">
                <span class="name">'.$__nameUser.'</span
                ><span class="date">'.$__date.'</span
                ><span>'.$__address.'</span>'.$buttonEdit.$buttonRemove.'
            </div>';

    //возврат сформированного блока
    $messageBlock = '<div class="message">
                        '.$mess.$tool.'
                    </div>';
    return $messageBlock;
}

/**
 * Функция формирует блок создания сообщения
 *
 * @param string $__nameUser Имя пользователя.
 *
 * @return string
 */
function getMessageCreationBlock(string $__nameUser)
{
    //зона ввода сообщения
    $textArea = '<textarea rows="3" cols="50" autofocus name="message"></textarea>';

    //кнопка отправить и кнопка выхода из аккаунта
    $send   = '<input type="submit" name="send"
                                              value="Send as '.$__nameUser.'">';
    $logOut = '<input type="submit" name="logOut"
                                            value="Log Out" class="buttonRed">';

    //инструменты,
    $tool = '<div class="tool">
                '.$send.$logOut.'
            </div>';

    //возврат сформированного блока
    $messageCreationBlock = '<div class="create">
                                '.$textArea.$tool.'
                            </div>';
    return $messageCreationBlock;
}


/**
 * Функция формирует блок редактирования сообщения
 *
 * @param string $__text Редактируемый текст.
 *
 * @return string
 */
function getMessageEditionBlock(string $__text)
{
    //зона ввода сообщения
    $textArea = '<textarea rows="15" cols="50" name="messageEdited" autofocus
            >'.$__text.'</textarea>';

    //кнопка отправить и кнопка выхода из аккаунта
    $send = '<input type="submit" name="edit"
                              value="edit" class="buttonEdit">';
    $back = '<input type="submit" name="back"
                                            value="back" class="buttonRed">';

    //инструменты
    $tool = '<div class="tool">
                '.$send.$back.'
            </div>';

    //возврат сформированного блока
    $messageCreationBlock = '<div class="create">
                                '.$textArea.$tool.'
                            </div>';
    return $messageCreationBlock;
}

/**
 * Возвращает страницу с редактором
 *
 * @param string $__text Редактируемый текст.
 *
 * @return string
 */
function getEditor(string $__text)
{
    //форма
    $form = '<form action="index.php" method="POST">
            '.getMessageEditionBlock($__text).'</form>';

    //код html
    $html = '<!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>Форум программистов</title>
                <link rel="stylesheet" href="stylePage.css">
            </head>
            <body>'.$form.'</body></html>';
    return $html;
}

/**
 * Функция возвращает часть списка сервисного блока ссылок
 *
 * @param integer $__countPages     Количество страниц.
 * @param integer $__numCurrentPage Номер текущей страницы.
 * @param integer $__pagesAround    Радиус окружения.
 *
 * @return string
 */
function getAroundReference(int $__countPages, int $__numCurrentPage, int $__pagesAround=PAGE_AROUND)
{
    //окружение после текущего
    $after = '';
    for ($count = 1; $count <= $__pagesAround; ++$count) {

        //следующий номер страницы
        $nextPage = $__numCurrentPage + $count;
        if ($nextPage > $__countPages) {
            break;
        }

        //ссылка на следующий номер страницы
        $nextRef = '<li><button name="numPage" value='."$nextPage".'">'."$nextPage".'</button></li>';
        $after  .= $nextRef;
    }

    //окружение до текущего
    $before = '';
    for ($count = 1; $count <= $__pagesAround; ++$count) {

        //предыдущий номер страницы
        $prevPage = $__numCurrentPage - $count;
        if ($prevPage <= 0) {
            break;
        }

        //ссылка на предыдущую страницу
        $prevRef = '<li><button name="numPage" value='."$prevPage".'">'."$prevPage".'</button></li>';
        $before  = $prevRef.$before;
    }

    //возврат части списка
    $currentRef = '<li><a href="#" class="numCurrPage">'."$__numCurrentPage".'</a></li>';
    $result     = $before.$currentRef.$after;
    return $result;
}

/**
 * Функция возвращает список сервисного блока ссылок
 *
 * @param integer $__countPages  Количество страниц.
 * @param integer $__numCurrPage Номер текущей страницы.
 * @param integer $__pagesAround Радиус окружения.
 *
 * @return string
 */
function getReferenceBlock(int $__countPages, int $__numCurrPage, int $__pagesAround=PAGE_AROUND)
{
    //формирование составных частей списка
    $listRefs  = '<ul>';
    $firstPage = '<li><button name="numPage" value=1">1</button></li>';
    $lastPage  = '<li><button name="numPage" value=
               '."$__countPages".'">'."$__countPages".'</button></li>';
    $between   = '<li><p>|</p></li>';

    //проверка на существование уже готовой первой ссылки
    //и добавление, если ее нет
    $listRefs .= $__numCurrPage - $__pagesAround > 1 ? $firstPage.$between : '';

    //добавление окружения
    $listRefs .= getAroundReference($__countPages, $__numCurrPage, $__pagesAround);

    //проверка на существование уже готовой последней ссылки
    //и добавление, если ее нет
    $listRefs .= $__numCurrPage + $__pagesAround >= $__countPages ? '' : $between.$lastPage;

    //возврат списка
    return $listRefs.'</ul>';
}

/**
 * Функция формирует основную страницу для пользователя $__login.
 *
 * Пользователь имеет возможности отправлять сообщения и
 * удалять свои.
 * Админ имеет возможности отправлять сообщения и
 * удалять все, а также редактировать и просматривать информацию
 * о пользователях.
 * Гость не имеет возможности отправлять сообщения,
 * удалять их. Может лишь просматривать собщения
 *
 * По умолчанию пользователь является гостем
 *
 * @param array   $__usersData      Массив данных пользователей.
 * @param string  $__login          Имя/Логин пользователя.
 * @param integer $__numberPage     Страница по счету.
 * @param integer $__messageOnPage  Количество сообщений на странице.
 * @param integer $__refPagesAround Количество соседних ссылок в сервисном блоке.
 *
 *  @return string
 */
function getMainPage(
    array $__usersData, string $__login=GUEST, int $__numberPage=1,
    int $__messageOnPage=MESSAGE_ON_PAGE, int $__refPagesAround=PAGE_AROUND
)
{

    //формирование блока создания сообщений
    $body = '<body><form action="index.php" method="POST">';
    $adv  = '<div class="adv">Здесь могла быть ваша реклама</div>';
    $auth = '<button name="authPage" id="auth">Authorization</button>';

    //количество пользователей
    $countUsers = count($__usersData);

    //количество страниц
    $countPages  = intval($countUsers / $__messageOnPage);
    $countPages += is_int($countUsers / $__messageOnPage) ? 0 : 1;

    //список ссылок на страницы гостевой
    $listReference = getReferenceBlock(
        $countPages, $__numberPage, $__refPagesAround
    );

    //сервисный блок ссылок
    $body .= $listReference;

    //если гость, то кнопка авторизации, иначе - блок создания сообщения
    $body .= $__login === GUEST ? $auth.$adv : getMessageCreationBlock($__login);

    //формирование $__messageOnPage блоков
    $countPrevious = 0;
    $countCurrent  = 0;
    foreach (array_reverse($__usersData) as $userData) {

        //если счетчик текущих сообщений на странице больше $__messageOnPage,
        //то цикл прекращается
        if ($countCurrent === $__messageOnPage) {
            break;
        }

        //если счетчик меньше количества предшествующих сообщений,
        //то обход шага
        if ($countPrevious < ($__numberPage - 1) * $__messageOnPage) {
            ++$countPrevious;
            continue;
        }

        //информация о пользователе
        $userName = $userData[0];
        $date     = $userData[1];
        $message  = $userData[2];

        //если админ, то показывать IP
        $address = in_array($__login, ADMINS_GROUP) ? $userData[3] : '';

        //если админ, то удалить можно все, иначе только свои сообщения
        $buttDel = in_array($__login, ADMINS_GROUP) ? true : $__login === $userName;

        //ключ сообщения для возможности удалить блок
        $idMess = array_search($userData, $__usersData);

        //добавление очередого блока
        $body .= getMessageBlock(
            $userName, $message, $date, $idMess, $buttDel, $address
        );
        ++$countCurrent;
    }

    //формирование страницы
    $body .= '</form></body>';
    $html  = '<!DOCTYPE html>
              <html lang="en">
                 <head>
                     <meta charset="utf-8">
                     <title>Форум программистов</title>
                     <link rel="stylesheet" href="stylePage.css">
                 </head>
                 '.$body.'</html>';
    return $html;
}

//-------------------Функции сериализации и десериализации----------------------

/**
 * Функция возвращает сериализованный массив данных пользователей
 *
 * @param array $__usersData Массив данных пользователей требующий сериализации.
 *
 * @return string
 */
function mySerialize(array $__usersData)
{
    //проверка на пустой массив
    if (count($__usersData) === 0) {
        return null;
    }

    //разделители пользователей и их данных соответственно
    $tokenUsers    = '[:|||:]';
    $tokenDataUser = '[:||:]';
    $serializeStr  = '';

    //один цикл = один пользователь
    foreach ($__usersData as $userData) {

        //разделитель
        $serializeStr .= $tokenUsers;

        //данные пользователя с соответсвующими разделителями
        foreach ($userData as $data) {
            $serializeStr .= $tokenDataUser;
            $serializeStr .= $data;
        }
    }

    //возврат сериализованных данных
    return $serializeStr;
}

/**
 * Функция десериализует строку в массив данных пользователей
 *
 * @param string $__str Сериализованная строка.
 *
 * @return array
 */
function myUnSerialize(string $__str)
{
    //проверка на пустую строку
    if (strlen($__str) === 0) {
        return null;
    }

    //разделители пользователей и их данных соответственно
    $tokenUsers    = '[:|||:]';
    $tokenDataUser = '[:||:]';
    $usersDataArr  = array();

    //разрез на строки данных пользователей
    $usersArr = explode($tokenUsers, $__str);
    array_shift($usersArr);

    //формирование данных в массив
    foreach ($usersArr as $user) {
        $userDataArr = explode($tokenDataUser, $user);
        array_shift($userDataArr);
        $usersDataArr[] = $userDataArr;
    }

    //десериализованный массив
    return $usersDataArr;
}

/**
 * Функция проверяет корректность лоигна и пароля
 *
 * @param string $__logOrPass Строка логина или пароля.
 *
 * @return boolean
 */
function correctInput(string $__logOrPass)
{
    //если строка пуста, то данные неккоректны
    $length = strlen($__logOrPass);
    if ($length === 0 || $length > MAX_LEN_LOG_OR_PASS) {
        return false;
    }

    //строка корректных символов
    $correctSymbols = 'qwertyuiopasdfghjklzxcvbnm1234567890_.';

    //проверка символов на корректность
    for ($pos = 0; $pos < $length; ++$pos) {
        if (stripos($correctSymbols, $__logOrPass[$pos]) === false) {
            return false;
        }
    }

    //данные корректны
    return true;
}
