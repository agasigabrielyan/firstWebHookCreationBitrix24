<?php
if( isset($_POST) && $_POST['registration'] == "true" ) {
    // формируем URL в переменной $queryUrl
    $queryUrl = 'Этуссылкумыполучаемвбитрикс24послесозданиянашеговходящеговебхука';

    // соберем массив файлов (имен и временных папок) в отдельный массив $createdFilesArray
    $createdFilesArray = [];
    foreach ($_FILES['sertificates'] as $fileKey => $fileValue) {
        if($fileKey === 'name') {
            foreach ( $fileValue as $fileValueKey => $fileValueName) {
                $createdFilesArray[$fileValueKey]['name'] = $fileValueName;
            }
        }
        if($fileKey === 'tmp_name') {
            foreach ( $fileValue as $fileValueKey => $fileValueName) {
                $createdFilesArray[$fileValueKey]['tmp_name'] = $fileValueName;
            }
        }
    }

    // создадим данные файлов для отправки с помощью вебхука
    $fileData = [];
    foreach( $createdFilesArray as $fileArrayKey => $fileArrayValue) {
        $target = date('Y_m_d_H_i_s_');
        $target .= $fileArrayValue['name'];
        $path = WP_CONTENT_DIR . '/uploads/registered_lids_documents/'.$target;
        move_uploaded_file($fileArrayValue['tmp_name'], $path);

        $data = file_get_contents($path);
        $base64 = base64_encode($data);
        $fileName = urldecode(basename($path));

        $fileData[]['fileData'] = [$fileName, $base64];
    }

    $queryData = http_build_query(array(
        'fields' => array(
            'TITLE' => 'Регистрация на сайте: ' . $_POST['name'],
            "NAME" => $_POST['name'],
            "ADDRESS" => $_POST['city'],
            'EMAIL' => Array(
                "n0" => Array(
                    "VALUE" => $_POST['email'],
                    "VALUE_TYPE" => "WORK",
                ),
            ),
            'PHONE' => Array(
                "n0" => Array(
                    "VALUE" => $_POST['phone'],
                    "VALUE_TYPE" => "WORK",
                ),
            ),
            'UF_CRM_1668953179965' => $fileData
        ),
        'params' => array("REGISTER_SONET_EVENT" => "Y")
    ));

    // обращаемся к Битрикс24 при помощи функции curl_exec
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
    ));
    $result = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($result, 1);
    if (array_key_exists('error', $result)) echo "Ошибка при сохранении лида: ".$result['error_description']."<br/>";

}
?>
<div class="main_container">
    <div class="container">
        <h1 class='main_title'><?php the_title(); ?></h1>
        <div class="page_content page__my_account">
            <div class="page__my_account_row_00">
                <div class="mobile_quick_links js_mobile_quick_links__registration">
                    <a href=".js_block_registration">Регистрация</a>
                    <a href=".js_block_login">Войти</a>
                </div>
                <div class="page__my_account_item_0 js_block_registration">
                    <div class="page__my_account_item_title">Регистрация</div>
                    <div class="page__my_account_row_text__1">
                        Если у вас еще нет аккаунт, пройдите процедуру регистрации и вы получите доступ к полному каталогу продукции
                    </div>
                    <?php get_template_part('include/myaccount/registration_do'); ?>
                    <form action="https://phcos.loc/myaccount/" method="POST" enctype="multipart/form-data">
                        <div class="my_field_row my_field_row--2">
                            <div class="my_field">
                                <label>
                                    <div class="my_field__name">Имя:</div>
                                    <input type="text" name="uname" required placeholder="Алексей">
                                </label>
                            </div>
                            <div class="my_field">
                                <label>
                                    <div class="my_field__name">Email:</div>
                                    <input type="text" name="email" required placeholder="info@mail.ru">
                                </label>
                            </div>
                        </div>
                        <div class="my_field_row my_field_row--2">
                            <div class="my_field">
                                <label>
                                    <div class="my_field__name">Телефон:</div>
                                    <input type="tel" name="phone" required placeholder="+7 (800) 000-00-00">
                                </label>
                            </div>
                            <div class="my_field">
                                <label>
                                    <div class="my_field__name">Пароль:</div>
                                    <input type="password" name="password" required placeholder="********">
                                </label>
                            </div>
                        </div>
                        <div class="my_field_row my_field_row--2">
                            <div class="my_field">
                                <label>
                                    <div class="my_field__name">Город:</div>
                                    <input type="text" name="city" required placeholder="Москва">
                                </label>
                            </div>
                        </div>
                        <!-- <div class="my_field__text_0">Загрузив сертификаты, мы сможем подтвердить ваш статус и предоставить вам доступ к полному каталогу продукци:</div> -->
                        <div class="my_field__text_0">Загрузите документы подтверждающие Вашу квалификацию, чтобы открылся доступ к полному каталогу продукции и дополнительным функциям.</div>
                        <div class="my_field my_field--files">
                            <label class="input__files_wrap">
                                <div class="input__files_btn btn_default">Загрузить документы, подтверждающие квалификацию специалиста</div>

                                <input type="file" name="sertificates[]" id="sertificates" required multiple="true" class='js_load_files__registration'>
                                <?php wp_nonce_field( 'sertificates', 'sertificates_nonce' ); ?>
                            </label>
                            <div class="my_field__files__view js_my_field__show__registration">
                            </div>
                        </div>

                        <div class="my_field my_field--agree">
                            <label>
                                <input type="checkbox" name="agree" required>
                                <span class="my_field--agree_view"></span>
                                <span class="my_field--agree_text">Согласен на обработку <a data-fancybox data-src="#modal__agree" href="javascript:;">персональных данных</a></span>
                            </label>
                        </div>
                        <div class="my_field my_field--submit">
                            <input type="hidden" name="registration" value="true">
                            <input
                                type="submit"
                                value="Регистрация"
                                class='btn_default btn_x'>
                        </div>
                    </form>
                </div>
                <div class="page__my_account_item_00 js_block_login">
                    <?php if (isset($_GET['login']) && $_GET['login']=='failed'):?>
                        <div class="message_x message_x--error">Неправильный логин или пароль</div>
                    <?php endif;?>
                    <div class="page__my_account_item_title">Войти</div>
                    <div class="page__my_account_row_text">Для входа в личный кабинет используйте логин (email) и пароль:</div>
                    <div class="page__my_account_login_block">
                        <form action="<?= get_site_url();?>/wp-login.php" method="POST">
                            <div class="my_field">
                                <label>
                                    <div class="my_field__name">Email:</div>
                                    <input type="text" name="log" required placeholder="info@yandex.ru">
                                </label>
                            </div>
                            <div class="my_field">
                                <label>
                                    <div class="my_field__name">Пароль:</div>
                                    <input type="password" name="pwd" required placeholder="********">
                                </label>
                            </div>
                            <div class="my_field my_field--submit">
                                <input type="submit" value="Войти" class='btn_default'>
                                <input type="hidden" name="redirect_to" value="<?= get_page_link(225);?>" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>