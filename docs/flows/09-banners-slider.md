# Механизм: Промо-баннеры на главной странице (/catalog)

## 1. Назначение механизма
Обеспечивает управление и отображение адаптивного слайдера рекламных баннеров в верхней части главной страницы каталога ([`https://stage.salvi.ua/catalog`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/category-main.vue)). 

Баннеры информируют клиентов об акциях, сезонных поступлениях, новых поставщиках и спецпредложениях. Каждый баннер кликабелен и перенаправляет пользователя на целевую категорию, товар или лендинг.

---

## 2. Архитектура и точки входа

* **Админка управления баннерами:** [`salvi_front/src/views/management/banner/list.vue`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/management/banner/list.vue)
* **Модалка создания/редактирования:** [`salvi_front/src/views/management/banner/modalEmail.vue`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/management/banner/modalEmail.vue)
* **Отображение на витрине:** [`salvi_front/src/views/category-main.vue`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/category-main.vue#L3-L37)
* **REST API Контроллер:** [`api/controllers/v1/BannerController.php`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/api/controllers/v1/BannerController.php)
* **ActiveRecord Модель:** [`common\models\Banner.php`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/common/models/Banner.php) (таблица `banners`)
* **Форма обработки и сохранения:** [`api/models/forms/BannerForm.php`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/api/models/forms/BannerForm.php)

---

## 3. Таблица эндпоинтов API

| Метод | URL эндпоинта | Метод контроллера | Права RBAC | Описание |
| :--- | :--- | :--- | :--- | :--- |
| **`GET`** | `/v1/banner/get-banners` | `BannerController::actionGetBanners()` | `catalogShowBtn` или `orderShowBtn` | Публичная выдача активных баннеров для витрины каталога с кэшированием. |
| **`GET`** | `/v1/banner/get-banners-adm`| `BannerController::actionGetBannersAdm()` | `managementEmail` | Выдача полного реестра баннеров для административной панели (с пагинацией и статусами). |
| **`POST`**| `/v1/banner/create` | `BannerController::actionCreate()` | Авторизован | Создание нового баннера, загрузка изображения, сброс кэша. |
| **`POST`**| `/v1/banner/update` | `BannerController::actionUpdate()` | Авторизован | Редактирование параметров (ссылка, интервал, порядок сортировки, активность). |
| **`POST`**| `/v1/banner/update-photo` | `BannerController::actionUpdatePhoto()` | Авторизован | Замена графического файла баннера. |
| **`POST`**| `/v1/banner/delete` | `BannerController::actionDelete()` | Авторизован | Деактивация / удаление баннера и очистка файла с диска. |

---

## 4. Параметры баннера (Модель `Banner`)

| Поле в БД | Поле в форме | Тип | Назначение |
| :--- | :--- | :--- | :--- |
| **`active`** | `active` | Boolean | **Флаг показа (1 / 0):** только баннеры с `active = 1` попадают в выдачу витрины. |
| **`name`** | `name` | String | Внутреннее служебное название баннера (отображается в таблице админки). |
| **`url`** | `url` | String | **Целевая ссылка:** URL страницы, куда переходит клиент при клике на баннер (например, `/catalog/ofisnij-papir` или `/must-see`). |
| **`sort`** | `sort` | Integer | **Порядок следования слайдов:** сортировка `ORDER BY sort ASC` (чем меньше число, тем левее/раньше слайд в карусели). |
| **`time_swap`**| `time_swap` | Integer | **Индивидуальная длительность показа (в секундах):** определяет, сколько секунд отображается именно этот слайд перед автоматическим перелистыванием. |
| **`img_path`** | `img` | String | Имя файла картинки, сохраненного на диске в каталоге `@api/web/sliders/`. |
| **`text`** | `text` | String | Текст подписи/кнопки (в UI кликабельным является все полотно баннера). |

---

## 5. Особенности реализации на фронтенде (`category-main.vue`)

### 5.1. Индивидуальный таймер для каждого слайда (`currentInterval`)
В отличие от стандартных слайдеров с фиксированным интервалом, на Salvi карусель динамически считывает длительность из параметров активного слайда:
```javascript
currentInterval() {
  return this.banners[this.slide].time_swap 
    ? this.banners[this.slide].time_swap * 1000 
    : 7000; // по умолчанию 7 секунд
}
```
*Если маркетолог выставил на важную акцию `time_swap = 12`, этот слайд будет висеть 12 секунд, а следующий с `time_swap = 4` прокрутится через 4 секунды.*

### 5.2. Плавная предзагрузка (Preloading) без миганий
Чтобы при смене слайдов не было белых вспышек и дерганья верстки, метод `preloadBanner()` кеширует картинки в память браузера через нативный объект `new Image()`:
```javascript
preloadBanner(src) {
  if (this.loadedBanners[src] !== undefined) return;
  this.$set(this.loadedBanners, src, false);
  const img = new Image();
  img.onload = () => this.$set(this.loadedBanners, src, true);
  img.onerror = () => this.$set(this.loadedBanners, src, true);
  img.src = src;
}
```
Слайд отображается с `opacity: 1` только тогда, когда картинка полностью скачана браузером.

### 5.3. Навигация и управление
* **Карусель:** компонент `v-carousel` с директивами `cycle` (автопрокрутка) и `v-model="slide"`.
* **Стрелки Prev/Next:** кнопки с проверкой граничных состояний: на первом слайде блокируется кнопка `prev` (`disabled`), на последнем — `next`.
* **Если баннеров нет:** блок `.home__banners` скрывается целиком (`v-if="banners.length"`), и страница сразу начинается с блока сезонных товаров.

---

## 6. Кэширование и хранение файлов

* **Каталог хранения изображений:** физически файлы сохраняются в директорию бэкенда:
  `/var/www/salvi/www_api/api/web/sliders/` с уникальным префиксом `slider_UUID.ext`.
* **Кэширование API (`GetBanners`):**
  * Запрос к БД: `Banner::find()->where(['active' => 1])->orderBy(['sort' => SORT_ASC])`.
  * Ключ кэша: `'GetBanners_' . md5($rawSql)`.
  * Тег кэша: `GetBanners` (`self::CACHE_KEY`).
  * Время жизни (TTL): **260 000 секунд (~3 суток)**.
  * **Авто-инвалидация:** при любом создании (`actionCreate`), редактировании (`actionUpdate`), смене картинки (`actionUpdatePhoto`) или удалении (`actionDelete`) вызывается:
    ```php
    TagDependency::invalidate(Yii::$app->cache, self::CACHE_KEY);
    ```
    Кэш сбрасывается мгновенно, и клиенты сразу видят обновленный баннер.
