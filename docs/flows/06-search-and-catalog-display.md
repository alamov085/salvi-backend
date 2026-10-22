# Механизм: Каталог, поиск и витрины товаров (SearchController & Frontend)

## 1. Назначение механизма
Контроллер [`api/controllers/v1/SearchController.php`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/api/controllers/v1/SearchController.php) является центральным ядром интернет-магазина/B2B-портала Salvi. Он отвечает за:
* Полнотекстовый поиск с опечатками и ранжированием через **Elasticsearch**.
* Подсказки и автокомплит в поисковой строке.
* Выдачу товаров для специализированных промо-блоков главной страницы («Популярное», «Must See / Сезонные товары», «4 категории»).
* Навигацию по дереву категорий, хлебные крошки и фильтрацию каталога.
* Многоуровневое тегированное кэширование в Redis.

---

## 2. Связка методов API и экранов фронтенда (Vue.js / `salvi_front`)

| Метод API | Маршрут API | Компонент / Экран во фронтенде (`salvi_front`) | Назначение блока на сайте |
| :--- | :--- | :--- | :--- |
| **`actionGetPopular`** | `POST /v1/search/get-popular` | [`category-main.vue:320`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/category-main.vue#L320), [`catalog__tile.vue:846`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/catalog/catalog__tile.vue#L846) | **Блок «Популярные товары»:** карусель/плитка на главной и отдельная страница `/popular`. |
| **`actionGetSeasonGoods`** | `POST /v1/search/get-season-goods` | [`category-main.vue:301`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/category-main.vue#L301), [`catalog__tile.vue:846`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/catalog/catalog__tile.vue#L846) | **Блок «Сезонные товары» (Must See):** сезонная подборка на главной и страница `/must_see`. |
| **`actionGet4BlockCategories`** | `POST /v1/search/get-4-block-categories` | [`category-main.vue:340`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/category-main.vue#L340) | **Блок «Категории на главной»:** 4 или 8 ключевых категорий 3-го уровня с превью товаров внутри. |
| **`actionGetCategoryPage`** | `POST /v1/search/get-category-page` | [`catalog__tile.vue:1836`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/catalog/catalog__tile.vue#L1836), [`catalog_slider.vue:685`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/catalog/catalog_slider.vue#L685) | **Страница категории верхнего уровня:** хлебные крошки и товары, сгруппированные по дочерним категориям. |
| **`actionGetProductByCategoryOnCategoryPage`** | `POST /v1/search/get-product-by-category-on-category-page` | Подгрузка товаров категории | **Конечная категория:** выдача товаров для конкретной выбранной листовой категории. |
| **`actionSearch`** | `POST /v1/search/search` | [`catalog__tile.vue:1835`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/catalog/catalog__tile.vue#L1835), [`catalog__table.vue:725`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/catalog/catalog__table.vue#L725) | **Глобальный поиск и каталог:** основной поиск по названию/артикулу, фильтры, сортировка и пагинация. |
| **`actionGetCategorySearch`** | `GET /v1/search/get-category-search` | [`search-LK.vue:298`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/components/search-LK.vue#L298), [`search.vue:347`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/views/catalog/search.vue#L347) | **Живой поиск (Категории):** выпадающие подсказки категорий в строке поиска с первыми 4 товарами. |
| **`actionGetHistorySearch`** | `GET /v1/search/get-history-search` | [`search-LK.vue:280`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/components/search-LK.vue#L280) | **История поиска:** выпадающий список популярных/недавних запросов пользователя. |
| **`actionSetSearchClue`** | `GET /v1/search/set-search-clue` | [`search-LK.vue:292`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/salvi_front/src/components/search-LK.vue#L292) | **Метрика клика:** логирование выбора поисковой подсказки. |

---

## 3. Особенности отбора и фильтрации товаров

Все методы контроллера перед выборкой накладывают обязательные системные ограничения:
1. **Проверка прав RBAC:** `orderShowBtn`, `catalogShowBtn` или `viewCatalog`.
2. **Исключение скрытых товаров и категорий:**
   * Категория: `category.delete = 0`
   * Товар: `product.deleted != Product::STATUS_HIDDEN`
3. **Фильтрация по спецификациям компании:**
   Метод `Specification::getListByParams($location_id, $user->company_id)` определяет персональные контрактные цены для филиала клиента. Если включен флаг `specification_only = 1`, отображаются **только товары из согласованной спецификации**.

### Критерии отбора по отдельным блокам:

* **Популярные товары (`actionGetPopular`):**
  * Строгий SQL-фильтр: **`product.is_popular = 1`**.
  * Поддерживает привязку к категории (если выбран фильтр), сортировку и пагинацию.
* **Сезонные товары (`actionGetSeasonGoods`):**
  * Строгий SQL-фильтр: **`product.is_season_goods = 1`**.
  * Используется для баннеров, сезонных распродаж и промо-вкладок.
* **Блок 4/8 категорий (`actionGet4BlockCategories`):**
  * Отбирает категории строго **3-го уровня** (`c.level = 3`), у которых установлен порядок сортировки (`c.sort > 0`).
  * Для каждой из первых 8 категорий выбирает до **23 товаров** (`perPage = 23`).
* **Полнотекстовый поиск (`actionSearch`):**
  * Формирует Elastic-запрос через `Elastic::buildBodyProductSearch($query)`.
  * Ищет в индексе `{env}.salvi.ua.search` по полям: наименование, синонимы, SKU, бренд, УКТВЭД.
  * Если передана категория (`category_id`), добавляет фильтр `term`.
  * Полученные `product_id` из Elasticsearch передаются в MySQL для обогащения свежими ценами, остатками и спецификациями.

---

## 4. Архитектура кэширования

В `SearchController` используется высокопроизводительное кэширование на базе **Redis + TagDependency** с базовым временем жизни:
```php
const CACHE_TIME = 260000; // ~72 часа (3 дня)
```

### Сводная таблица кэширования:

| Метод | Ключ кэша (Cache Key) | Тег инвалидации (Tag) | Что именно кэшируется |
| :--- | :--- | :--- | :--- |
| **`actionGetPopular`** | `CategoryPopular_{md5(RawSql)}` | `CategoryPopular` | Массив подготовленных товаров (`productsList`), отформатированные цены со спецификациями (`result`) и общее количество (`total`). |
| **`actionGetSeasonGoods`** | `GetSeasonGoods_{md5(RawSql)}` | `GetSeasonGoods` | Подготовленные сезонные товары с ценами спецификаций и `total`. |
| **`actionGet4BlockCategories`** | `Get4BlockCategoriesFull_{md5(params)}` и `Get4BlockCategories_{md5(RawSql)}` | `Get4BlockCategories` | Двухуровневый кэш: полный JSON-ответ структуры категорий и по-категорийный кэш товаров. |
| **`actionGetCategoryPage`** | `GetCategoryPage_{md5(RawSql)}` и `$noFiltersCacheKey` | `GetCategoryPage` | Структура подкатегорий, хлебные крошки и списки товаров дочерних категорий. |
| **`actionGetProductByCategoryOnCategoryPage`** | `GetProductByCategoryOnCategoryPage_{md5(RawSql)}` | `GetProductByCategoryOnCategoryPage` | Готовый массив товаров для конкретной листовой категории. |

---

## 5. Как и когда сбрасывается кэш?

Сброс тегированного кэша вызывается централизованно через `TagDependency::invalidate()`:

1. **При загрузке/обновлении фотографий товаров ([`api/job/UploadPhoto.php`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/api/job/UploadPhoto.php)):**
   Сбрасывает сразу все витрины:
   ```php
   $listCacheToClear = [
       'GetSeasonGoods',
       'CategoryPopular',
       'Get4BlockCategories',
       'GetCategoryPage',
       'GetProductByCategoryOnCategoryPage',
   ];
   foreach ($listCacheToClear as $item) {
       TagDependency::invalidate(Yii::$app->cache, $item);
   }
   ```
2. **При обновлении дерева и иконок категорий ([`api/job/UploadPhotoCategory.php`](file:///C:/Users/Admin/PhpstormProjects/salvi_back/api/job/UploadPhotoCategory.php)):**
   Сбрасывает тег `CategoryTreeAllCategories`.
3. **Обход кэша при отладке:**
   Любой запрос можно выполнить в обход кэша с помощью специального флага в методе `$this->withoutCache()`.
