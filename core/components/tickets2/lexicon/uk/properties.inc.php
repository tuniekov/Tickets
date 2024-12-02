<?php
/*
 * Properties Russian Lexicon Entries
 *
 * */

$_lang['tickets2_prop_limit'] = 'Ліміт вибірки результатів';
$_lang['tickets2_prop_offset'] = 'Пропуск результатів з початку вибірки';
$_lang['tickets2_prop_depth'] = 'Глибина пошуку ресурсів від кожного батька.';
$_lang['tickets2_prop_sortby'] = 'Сортування вибірки.';
$_lang['tickets2_prop_sortdir'] = 'Напрям сортування';
$_lang['tickets2_prop_parents'] = 'Список категорій, через кому, для пошуку результатів. За замовчуванням вибірка обмежена поточним батьком. Якщо поставити 0 - вибірка не обмежується.';
$_lang['tickets2_prop_resources'] = 'Список ресурсів, через кому, для виведення в результатах. Якщо id ресурсу починається з мінуса, цей ресурс виключається з вибірки.';
$_lang['tickets2_prop_where'] = 'Рядок, закодована в JSON, з додатковими умовами вибірки.';
$_lang['tickets2_prop_tvPrefix'] = 'Префікс для ТБ плейсхолдеров, наприклад "tv.". За замовчуванням порожній.';
$_lang['tickets2_prop_includeContent'] = 'Вибирати поле "content" ресурсів.';
$_lang['tickets2_prop_includeTVs'] = 'Список ТБ параметрів для вибірки, через кому. Наприклад: "action,time" дадуть плейсхолдеры [[+action]] і [[+time]].';
$_lang['tickets2_prop_toPlaceholder'] = 'Якщо не порожньо, фрагмент зберігатиме всі дані у плейсхолдер з цим ім`ям, замість виведення не екран.';
$_lang['tickets2_prop_outputSeparator'] = 'Необов`язкова рядок для поділу результатів роботи.';

$_lang['tickets2_prop_showLog'] = 'Показувати додаткову інформацію про роботу фрагменту. Тільки для авторизованих в контекте "mgr".';
$_lang['tickets2_prop_showUnpublished'] = 'Показувати неопубліковані ресурси.';
$_lang['tickets2_prop_showDeleted'] = 'Показувати видалені ресурси.';
$_lang['tickets2_prop_showHidden'] = 'Показувати ресурси, приховані в меню.';
$_lang['tickets2_prop_fastMode'] = 'Якщо увімкнуто - чанк результату будуть підставлені тільки значення з БД. Всі необроблені теги MODX, такі як фільтри, виклик фрагментів та інші - будуть вирізані.';

$_lang['tickets2_prop_action'] = 'Режим роботи фрагменту';
$_lang['tickets2_prop_cacheKey'] = 'Ім`я кеша фрагменту. Якщо порожнє - кешування результатів буде відключено.';
$_lang['tickets2_prop_cacheTime'] = 'Час кешування.';
$_lang['tickets2_prop_thread'] = 'Ім`я гілки коментарів. За замовчуванням, "resource-[[*id]]".';
$_lang['tickets2_prop_user'] = 'Вибрати тільки елементи, створені цим користувачем.';

$_lang['tickets2_prop_tpl'] = 'Чанк оформлення для кожного результату';
$_lang['tickets2_prop_tplFormCreate'] = 'Чанк для створення нового тікета';
$_lang['tickets2_prop_tplFormUpdate'] = 'Чанк для оновлення існуючого тікета';
$_lang['tickets2_prop_tplSectionRow'] = 'Чанк для оформлення розділу питань у формі';
$_lang['tickets2_prop_tplPreview'] = 'Чанк для попереднього перегляду тікета перед публікацією';
$_lang['tickets2_prop_tplCommentForm'] = 'Чанк для форми додавання коментаря';
$_lang['tickets2_prop_tplCommentAuth'] = 'Чанк коментаря для показу авторизованому користувачеві.';
$_lang['tickets2_prop_tplCommentGuest'] = 'Чанк коментаря для показу гостям.';
$_lang['tickets2_prop_tplCommentDeleted'] = 'Чанк віддаленого коментаря.';
$_lang['tickets2_prop_tplComments'] = 'Обгортка для всіх коментарів сторінки.';
$_lang['tickets2_prop_tplLoginToComment'] = 'Чанк з вимогою авторизуватись.';
$_lang['tickets2_prop_tplCommentEmailOwner'] = 'Чанк для попередження власника тікета про новий коментарі.';
$_lang['tickets2_prop_tplCommentEmailReply'] = 'Чанк для повідомлення користувача про те, що на його коментар з`явився відповідь.';
$_lang['tickets2_prop_tplCommentEmailSubscription'] = 'Чанк для попередження підписаного користувача, що в темі з`явився новий коментар.';
$_lang['tickets2_prop_tplCommentEmailBcc'] = 'Чанк для попередження адмінів сайту про новий коментарі.';
$_lang['tickets2_prop_tplTicketEmailBcc'] = 'Чанк для попередження адмінів сайту про новий тикеті.';

$_lang['tickets2_prop_commentsDepth'] = 'Ціле число, для визначення максимальної глибини гілки коментарів.';
$_lang['tickets2_prop_autoPublish'] = 'Автоматично публікувати всі нові коментарі без премодерації.';
$_lang['tickets2_prop_formBefore'] = 'Розташувати форму коментування перед коментарями. За замовчуванням - ні.';
//$_lang['tickets2_prop_dateFormat'] = 'Формат дати коментаря, для функції date()';
$_lang['tickets2_prop_gravatarIcon'] = 'Якщо аватарка користувача не знайдено, вантажити цю картинку на заміну.';
$_lang['tickets2_prop_gravatarSize'] = 'Розмір аватара';
$_lang['tickets2_prop_gravatarUrl'] = 'Адреса для завантаження аватарів';

$_lang['tickets2_prop_allowedFields'] = 'Поля тікета, які можна заповнювати користувачеві. Можна вказувати імена ТБ параметрів.';
$_lang['tickets2_prop_requiredFields'] = 'Обов`язкові поля, які користувач повинен заповнити для відправки форми.';
$_lang['tickets2_prop_redirectUnpublished'] = 'Ви можете вказати, на який документ відправляти користувача при створенні неопублікованого тікета.';
$_lang['tickets2_prop_sections_parents'] = 'За замовчуванням виводяться всі доступні розділи тікетів, але ви можете обмежити їх, вказавши конкретних батьків через кому.';
$_lang['tickets2_prop_sections_sortby'] = 'Поле для сортування списку розділів.';
$_lang['tickets2_prop_sections_sortdir'] = 'Напрям сортування списку розділів.';
$_lang['tickets2_prop_sections_context'] = 'Список контекстів для пошуку секцій, через кому.';

$_lang['tickets2_prop_meta_tpl'] = 'Чанк оформлення інформації про тикеті.';
$_lang['tickets2_prop_getSection'] = 'Зробити додатковий запит до БД для отримання батьківського секції?';
$_lang['tickets2_prop_getUser'] = 'Зробити додатковий запит до БД для отримання профілю автора?';

$_lang['tickets2_prop_allowGuest'] = 'Включити можливість коментування неавторизованих користувачів?';
$_lang['tickets2_prop_allowGuestEdit'] = 'Дозволяти неавторизованим користувачам редагувати свої коментарі?';
$_lang['tickets2_prop_allowGuestEmails'] = 'Відправляти гостям поштові повідомлення про відповіді?';
$_lang['tickets2_prop_enableCaptcha'] = 'Включити захист від спаму для неавторизованих користувачів?';
$_lang['tickets2_prop_minCaptcha'] = 'Мінімальна кількість для генерації коду захисту від спаму.';
$_lang['tickets2_prop_maxCaptcha'] = 'Максимальна кількість для генерації коду захисту від спаму.';