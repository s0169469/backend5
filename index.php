<?php
/**
 * Реализовать возможность входа с паролем и логином с использованием
 * сессии для изменения отправленных данных в предыдущей задаче,
 * пароль и логин генерируются автоматически при первоначальной отправке формы.
 */
// Отправляем браузеру правильную кодировку,
// файл index.php должен быть в кодировке UTF-8 без BOM.
header('Content-Type: text/html; charset=UTF-8');
include_once('Addition.php');
// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  // Массив для временного хранения сообщений пользователю.
  $messages = array();
  // В суперглобальном массиве $_COOKIE PHP хранит все имена и значения куки текущего запроса.
  // Выдаем сообщение об успешном сохранении.
  if (!empty($_COOKIE['save'])) {
    // Удаляем куку, указывая время устаревания в прошлом.
    setcookie('save', '', 100000);
    setcookie('login', '', 100000);
    setcookie('pass', '', 100000);
    // Выводим сообщение пользователю.
    $messages[] = 'Спасибо, результаты сохранены.';
    // Если в куках есть пароль, то выводим сообщение.
    if (!empty($_COOKIE['pass'])) {
      $messages[] = sprintf('Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong>
        и паролем <strong>%s</strong> для изменения данных.',
        strip_tags($_COOKIE['login']),
        strip_tags($_COOKIE['pass']));
    }
  }
  // Складываем признак ошибок в массив.
  $errors = array();
  $errors['name'] = !empty($_COOKIE['name_error']);
  $errors['email'] = !empty($_COOKIE['email_error']);
  $errors['date'] = !empty($_COOKIE['date_error']);
  $errors['abilities'] = !empty($_COOKIE['abilities_error']);
  $errors['biog'] = !empty($_COOKIE['biog_error']);
  $errors['check'] = !empty($_COOKIE['check_error']);
  // Выдаем сообщения об ошибках.
  if ($errors['name']) {
    // Удаляем куку, указывая время устаревания в прошлом.
    // setcookie('fio_error', '', 100000);
    // Выводим сообщение.
    $messages[] = '<div class="error">Заполните имя.</div>';
  }
  if ($errors['email']) {
    $messages[] = '<div class="error">Заполните email.</div>';
  }
  if ($errors['date']) {
    $messages[] = '<div class="error">Заполните поле даты рождения.</div>';
  }
  if ($errors['abilities']) {
    $messages[] = '<div class="error">Выберите способность.</div>';
  }
  if ($errors['biog']) {
    $messages[] = '<div class="error">Заполните поле биографии.</div>';
  }
  if ($errors['check']) {
    $messages[] = '<div class="error">Заполните поле ознакомления.</div>';
  }
  // Складываем предыдущие значения полей в массив, если есть.
  // При этом санитизуем все данные для безопасного отображения в браузере.
  $values = array();
  $values['name'] = empty($_COOKIE['name_value']) ? '' : strip_tags($_COOKIE['name_value']);
  $values['email'] = empty($_COOKIE['email_value']) ? '' : strip_tags($_COOKIE['email_value']);
  $values['abilities'] = empty($_COOKIE['abilities_value']) ? '' : strip_tags($_COOKIE['abilities_value']);
  $values['date'] = empty($_COOKIE['date_value']) ? '' : strip_tags($_COOKIE['date_value']);
  $values['biog'] = empty($_COOKIE['biog_value']) ? '' : strip_tags($_COOKIE['biog_value']);
  // Если нет предыдущих ошибок ввода, есть кука сессии, начали сессию и
  // ранее в сессию записан факт успешного логина.
  if (!empty($_COOKIE[session_name()]) && !empty($_SESSION['login'])) {
    // TODO: загрузить данные пользователя из БД
    // и заполнить переменную $values,
    // предварительно санитизовав.
    $stmt = $db->prepare("SELECT * FROM main where username = :username");
    $stmt->execute(['username' => $_SESSION['login']]);
    $result = $stmt->fetchAll();
    foreach($result as $res) {   
      $values['name'] = strip_tags($res['name']);
      $values['email'] = strip_tags($res['email']);
      $values['date'] = strip_tags($res['data']);
      $values['biog'] = strip_tags($res['biog']);
    }
    printf('Вход с логином %s', $_SESSION['login']);
  }
  // Включаем содержимое файла form.php.
  // В нем будут доступны переменные $messages, $errors и $values для вывода 
  // сообщений, полей с ранее заполненными данными и признаками ошибок.
  include('form.php');
}
// Иначе, если запрос был методом POST, т.е. нужно проверить данные и сохранить их в XML-файл.
else {
  // Проверяем ошибки.
  $error = FALSE;
  if (!preg_match( '/^([а-яё\s]+|[a-z\s]+)$/iu', $_POST['name'])) {
    // Выдаем куку до конца сессии с флажком об ошибке в поле name.
    setcookie('name_error', '1', 0);
    $error = TRUE;
  }
  else {
    // Сохраняем ранее введенное в форму значение на год.
    setcookie('name_value', $_POST['name'], time() + 3600*24*30*365);
  }
  if (!preg_match('/^(([^<>()[\].,;:\s@"]+(\.[^<>()[\].,;:\s@"]+)*)|(".+"))@(([^<>()[\].,;:\s@"]+\.)+[^<>()[\].,;:\s@"]{2,})$/', $_POST['email'])) {
    setcookie('email_error', '1', 0);
    $error = TRUE;
  }
  else {
    setcookie('email_value', $_POST['email'], time() + 3600*24*30*365);
  }
  if (!preg_match( '/^[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])$/', $_POST['date'])) {
    setcookie('date_error', '1', 0);
    $error = TRUE;
  }
  else {
    setcookie('date_value', $_POST['date'], time() + 3600*24*30*365);
  }
  if (empty($_POST['abilities'])) {
    setcookie('abilities_error', '1', 0);
    $error = TRUE;
  }
  else {
    setcookie('abilities_value', $_POST['abilities'], time() + 3600*24*30*365);
  }
  if (empty($_POST['biog'])) {
    setcookie('biog_error', '1', 0);
    $error = TRUE;
  }
  else {
    setcookie('biog_value', $_POST['biog'], time() + 3600*24*30*365);
  }
  if (empty($_POST['check'])) {
    setcookie('check_error', '1', 0);
    $error = TRUE;
  }
// *************
// Сохранить в Cookie признаки ошибок и значения полей.
// *************
  if ($error) {
    // При наличии ошибок перезагружаем страницу и завершаем работу скрипта.
    header('Location: index.php');
    exit();
  }
  else {
    // Удаляем Cookies с признаками ошибок.
    setcookie('name_error', '', 100000);
    setcookie('email_error', '', 100000);
    setcookie('date_error', '', 100000);
    setcookie('abilities_error', '', 100000);
    setcookie('biog_error', '', 100000);
    setcookie('check_error', '', 100000);
  }
  // Проверяем меняются ли ранее сохраненные данные или отправляются новые.
  if (!empty($_COOKIE[session_name()]) && !empty($_SESSION['login'])) {
    // TODO: перезаписать данные в БД новыми данными,
    // кроме логина и пароля.
    try {
      $stmt = $db->prepare("UPDATE main SET name = :name, email = :email, data = :data, gender = :gender, limbs = :limbs, biog = :biog WHERE username = :username");
      $stmt->execute(array(':name' => $_POST['name'], ':email' => $_POST['email'], ':data' => $_POST['date'],
      ':gender' => $_POST['gender'],':limbs' => $_POST['limbs'],':biog' => $_POST['biog'],':username' => $_SESSION['login']));
      $stmt = $db->prepare("SELECT AppId from main WHERE username = :username");
      $stmt->execute([':username' => $_SESSION['login']]);
      $res = $stmt->fetch();
      $stmt = $db->prepare("DELETE FROM main_ab where AppID = :id");
      $stmt->execute([':id' => $res['AppId']]);
      $sql = 'INSERT INTO main_ab(AppID, AbId) VALUES(:AppID, :AbId)';
      $stmt = $db->prepare($sql);
      foreach($_POST['abilities'] as $ability)
      {
          $row = [
                'AppID' => $res["AppId"],
                'AbId' =>  $ability
          ];
          $stmt->execute($row); 
      }
    }
    catch(PDOException $e){
      print('Error : ' . $e->getMessage());
      exit();
    }
  }
  else {
    // Генерируем уникальный логин и пароль.
    $login = uniqid();
    $pass = rand();
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    // Сохраняем в Cookies.
    setcookie('login', $login);
    setcookie('pass', $pass);
    try {
      $stmt = $db->prepare("INSERT INTO main(name, email, data, gender, limbs, biog, username, password)
      VALUES('{$_POST['name']}', '{$_POST['email']}', '{$_POST['date']}', '{$_POST['gender']}', '{$_POST['limbs']}','{$_POST['biog']}', '$login', '$hash')");
      $stmt->execute([$_POST['name'], $_POST['email'], $_POST['date'], $_POST['gender'], $_POST['limbs'], $_POST['biog']]);
      $stmt = $db->prepare("SELECT LAST_INSERT_ID()  as AppId");
      $stmt->execute();
      $result = $stmt->fetch();
      $sql = 'INSERT INTO main_ab(AppID, AbId) VALUES(:AppID, :AbId)';
      $stmt = $db->prepare($sql);
      foreach($_POST['abilities'] as $ability)
      {
          $row = [
                'AppID' => $result["AppId"],
                'AbId' =>  $ability
          ];
          $stmt->execute($row); 
      }
    }
    catch(PDOException $e){
      print('Error : ' . $e->getMessage());
      exit();
    }
  }

  // Сохраняем куку с признаком успешного сохранения.
  setcookie('save', '1');

  // Делаем перенаправление.
  header('Location: ./');
}
 
