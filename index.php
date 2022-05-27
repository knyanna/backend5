<?php
header('Content-Type: text/html; charset=UTF-8');
$user = 'u47644';
$pass = '5440513';

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
      $messages[] = sprintf(
        'Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong>
        и паролем <strong>%s</strong> для изменения данных.',
        strip_tags($_COOKIE['login']),
        strip_tags($_COOKIE['pass'])
      );
    }
  }

  // Складываем признак ошибок в массив.
  $errors = array();
  $errors['name'] = !empty($_COOKIE['name_error']);
  $errors['email'] = !empty($_COOKIE['email_error']);
  $errors['birth'] = !empty($_COOKIE['birth_error']);
  $errors['gender'] = !empty($_COOKIE['gender_error']);
  $errors['limbs'] = !empty($_COOKIE['limbs_error']);
  $errors['select'] = !empty($_COOKIE['select_error']);
  $errors['bio'] = !empty($_COOKIE['bio_error']);
  $errors['policy'] = !empty($_COOKIE['policy_error']);


  if ($errors['name']) {
    setcookie('name_error', '', 100000);
    $messages[] = '<div class="error">Введите имя.</div>';
  }
  if ($errors['email']) {
    setcookie('email_error', '', 100000);
    $messages[] = '<div class="error">Введите верный email.</div>';
  }
  if ($errors['birth']) {
    setcookie('birth_error', '', 100000);
    $messages[] = '<div class="error">Введите корректную дату рождения.</div>';
  }
  if ($errors['gender']) {
    setcookie('gender_error', '', 100000);
    $messages[] = '<div class="error">Выберите пол.</div>';
  }
  if ($errors['limbs']) {
    setcookie('limbs_error', '', 100000);
    $messages[] = '<div class="error">Выберите количество конечностей.</div>';
  }
  if ($errors['select']) {
    setcookie('select_error', '', 100000);
    $messages[] = '<div class="error">Выберите суперспособнос(ть/ти).</div>';
  }
  if ($errors['bio']) {
    setcookie('bio_error', '', 100000);
    $messages[] = '<div class="error">Расскажите о себе.</div>';
  }
  if ($errors['policy']) {
    setcookie('policy_error', '', 100000);
    $messages[] = '<div class="error">Ознакомтесь с политикой обработки данных.</div>';
  }

  // Складываем предыдущие значения полей в массив, если есть.
  // При этом санитизуем все данные для безопасного отображения в браузере.
  $values = array();
  $values['name'] = empty($_COOKIE['name_value']) ? '' : strip_tags($_COOKIE['name_value']);
  $values['email'] = empty($_COOKIE['email_value']) ? '' : strip_tags($_COOKIE['email_value']);
  $values['birth'] = empty($_COOKIE['birth_value']) ? '' : strip_tags($_COOKIE['birth_value']);
  $values['gender'] = empty($_COOKIE['gender_value']) ? '' : strip_tags($_COOKIE['gender_value']);
  $values['limbs'] = empty($_COOKIE['limbs_value']) ? '' : strip_tags($_COOKIE['limbs_value']);
  $values['select'] = empty($_COOKIE['select_value']) ? '' : strip_tags($_COOKIE['select_value']);
  $values['bio'] = empty($_COOKIE['bio_value']) ? '' : strip_tags($_COOKIE['bio_value']);
  $values['policy'] = empty($_COOKIE['policy_value']) ? '' : strip_tags($_COOKIE['policy_value']);
  // TODO: аналогично все поля.

  // Если нет предыдущих ошибок ввода, есть кука сессии, начали сессию и
  // ранее в сессию записан факт успешного логина.
  if (
    empty($errors) && !empty($_COOKIE[session_name()]) &&
    session_start() && !empty($_SESSION['login'])
  ) {
    $member = $_SESSION['login'];
    try {
      $db = new PDO('mysql:host=localhost;dbname=u47505', $user, $pass, array(PDO::ATTR_PERSISTENT => true));
      $stmt = $db->prepare("SELECT * FROM members WHERE login = ?");
      $stmt->execute(array($member));
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      $values['name'] = $result['name_value'];
      $values['email'] = $result['email_value'];
      $values['birth'] = $result['birth_value'];
      $values['gender'] = $result['gender_value'];
      $values['limbs'] = $result['limbs_value'];
      $values['bio'] = $result['bio_value'];
      $values['policy'] = $result['policy_value'];

      $powers = $db->prepare("SELECT * FROM powers2 WHERE user_login = ? ");
      $powers->execute(array($member));
      $result = $powers->fetch(PDO::FETCH_ASSOC);
      $values['select'] = $result['powers'];
    } catch (PDOException $e) {
      print('Error : ' . $e->getMessage());
      exit();
    }
    printf('Вход с логином %s, uid %d', $_SESSION['login'], $_SESSION['uid']);
  }

  include('form.php');
}
// Иначе, если запрос был методом POST, т.е. нужно проверить данные и сохранить их в XML-файл.
else {
  // Проверяем ошибки.
  $errors = FALSE;
  if (!filter_var($_COOKIE['email_value'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = !empty($_COOKIE['email_error']);
  }

  $errors = FALSE;
  // проверка поля имени
  if (!preg_match('/^[a-z0-9_\s]+$/i', $_POST['name'])) {
    setcookie('name_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('name_value', $_POST['name'], time() + 12 * 30 * 24 * 60 * 60);
  }

  // проверка поля email
  if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    setcookie('email_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('email_value', $_POST['email'], time() + 12 * 30 * 24 * 60 * 60);
  }

  // проверка поля даты рождения
  $birth = explode('-', $_POST['birth']);
  $age = (int)date('Y') - (int)$birth[0];
  if ($age > 100 || $age < 0) {
    setcookie('birth_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('birth_value', $_POST['birth'], time() + 12 * 30 * 24 * 60 * 60);
  }

  // проверка поля пола
  if (empty($_POST['gender'])) {
    setcookie('gender_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('gender_value', $_POST['gender'], time() + 12 * 30 * 24 * 60 * 60);
  }

  // проверка поля количества конечностей
  if (empty($_POST['limbs'])) {
    setcookie('limbs_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('limbs_value', $_POST['limbs'], time() + 12 * 30 * 24 * 60 * 60);
  }

  // проверка поля суперспособностей
  if (empty($_POST['select'])) {
    setcookie('select_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('select_value', $_POST['select'], time() + 12 * 30 * 24 * 60 * 60);
  }

  // проверка поля биографии
  if (empty($_POST['bio'])) {
    setcookie('bio_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('bio_value', $_POST['bio'], time() + 12 * 30 * 24 * 60 * 60);
  }

  // проверка поля политики обработки данных 
  if (empty($_POST['policy'])) {
    setcookie('policy_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('policy_value', $_POST['policy'], time() + 12 * 30 * 24 * 60 * 60);
  }

  if ($errors) {
    // При наличии ошибок перезагружаем страницу и завершаем работу скрипта.
    header('Location: index.php');
    exit();
  } else {
    setcookie('name_error', '', 100000);
    setcookie('email_error', '', 100000);
    setcookie('birth_error', '', 100000);
    setcookie('gender_error', '', 100000);
    setcookie('limbs_error', '', 100000);
    setcookie('select_error', '', 100000);
    setcookie('bio_error', '', 100000);
    setcookie('policy_error', '', 100000);
  }
  $name = $_POST['name'];
  $email = $_POST['email'];
  $date = $_POST['birth'];
  $gender = $_POST['gender'];
  $limbs = $_POST['limbs'];
  $bio = $_POST['bio'];
  $policy = $_POST['policy'];
  $powers = implode(',', $_POST['select']);
  $member = $_SESSION['login'];

  $db = new PDO('mysql:host=localhost;dbname=u47644', $user, $pass, array(PDO::ATTR_PERSISTENT => true));
  // Проверяем меняются ли ранее сохраненные данные или отправляются новые.
  if (
    !empty($_COOKIE[session_name()]) &&
    session_start() && !empty($_SESSION['login'])
  ) {
    try {
      $stmt = $db->prepare("UPDATE members SET name = ?, email = ?, date = ?, gender = ?, limbs = ?, bio = ?, policy = ? WHERE login = ?");
      $stmt->execute(array($name, $email, $date, $gender, $limbs, $bio, $policy, $member));

      $superpowers = $db->prepare("UPDATE powers2 SET powers = ? WHERE user_login = ? ");
      $superpowers->execute(array($powers, $member));
    } catch (PDOException $e) {
      print('Error : ' . $e->getMessage());
      exit();
    }
  } else {

    $login = uniqid();
    $password = uniqid();
    $hash = md5($password);
    // Сохраняем в Cookies.
    setcookie('login', $login);
    setcookie('pass', $password);

    try {
      $stmt = $db->prepare("INSERT INTO members SET login = ?, pass = ?, name = ?, email = ?, date = ?, gender = ?, limbs = ?, bio = ?, policy = ?");
      $stmt->execute(array($login, $hash, $name, $email, $date, $gender, $limbs, $bio, $policy));

      $superpowers = $db->prepare("INSERT INTO powers2 SET powers = ?, user_login = ? ");
      $superpowers->execute(array($powers, $login));
    } catch (PDOException $e) {
      print('Error : ' . $e->getMessage());
      exit();
    }
  }

  // Сохраняем куку с признаком успешного сохранения.
  setcookie('save', '1');

  // Делаем перенаправление.
  header('Location: ./');
}
