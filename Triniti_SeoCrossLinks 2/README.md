# Triniti SEO Cross Links - Інструкція з встановлення

## 📦 Про модуль

**Triniti_SeoCrossLinks** — модуль для Magento 2, який автоматично додає перехресні SEO-посилання на сторінки вашого інтернет-магазину.

**Версія:** 1.1.4 (всі помилки виправлені + підтримка мультимагазинів)

### Основні можливості:
- ✅ Автоматичне додавання перехресних посилань на сторінки
- ✅ Підтримка різних типів сторінок (категорії, товари, CMS)
- ✅ Імпорт/експорт посилань через CSV
- ✅ Підтримка мультимагазинів
- ✅ Керування сортуванням та активністю посилань

---

## 🔧 Вимоги

- **Magento 2.3.x, 2.4.x**
- **PHP 7.4, 8.0, 8.1, 8.2, 8.3**
- Доступ до командного рядка сервера
- Права адміністратора в Magento

---

## 📥 Встановлення

### Крок 1: Завантаження модуля

Розпакуйте архів у директорію:
```bash
app/code/Triniti/SeoCrossLinks/
```

Структура повинна виглядати так:
```
app/code/Triniti/SeoCrossLinks/
├── Block/
├── Controller/
├── Model/
├── etc/
├── view/
├── registration.php
└── composer.json
```

### Крок 2: Встановлення через командний рядок

Перейдіть до кореневої директорії Magento та виконайте:

```bash
# Очистити кеш
php bin/magento cache:clean

# Увімкнути модуль
php bin/magento module:enable Triniti_SeoCrossLinks

# Оновити базу даних (створить таблицю triniti_seo_crosslink)
php bin/magento setup:upgrade

# Компіляція DI (для production mode)
php bin/magento setup:di:compile

# Деплой статичного контенту (для production mode)
php bin/magento setup:static-content:deploy uk_UA en_US -f

# Очистити кеш повторно
php bin/magento cache:flush
```

### Крок 3: Перевірка встановлення

Перевірте статус модуля:
```bash
php bin/magento module:status Triniti_SeoCrossLinks
```

Повинно вивести:
```
List of enabled modules:
Triniti_SeoCrossLinks
```

Перевірте, чи створилась таблиця в базі даних:
```sql
SHOW TABLES LIKE 'triniti_seo_crosslink';
DESCRIBE triniti_seo_crosslink;
```

---

## 📝 Використання

### Доступ до адмін-панелі

1. Увійдіть в адмін-панель Magento
2. Перейдіть: **Content → SEO Cross Links → Import / Export**

### Імпорт посилань через CSV

#### Формат CSV файлу:

```csv
store_id,donor_url,acceptor_url,anchor,sort_order,is_active
0,/,/category/laptops,Ноутбуки,1,1
0,/,/category/smartphones,Смартфони,2,1
0,/category/laptops,/product/macbook-pro,MacBook Pro 16,1,1
1,/category/laptops,/product/dell-xps,Dell XPS 15,2,1
```

#### Пояснення полів:

| Поле | Опис | Приклад |
|------|------|---------|
| `store_id` | ID магазину (0 = всі магазини) | `0` або `1` |
| `donor_url` | Сторінка, де з'явиться посилання | `/zariadni-stantsii` |
| `acceptor_url` | URL посилання | `/product/charger-x` |
| `anchor` | Текст посилання (анкор) | `Зарядна станція X` |
| `sort_order` | Порядок сортування | `1`, `2`, `3` |
| `is_active` | Активність (1 = активно, 0 = вімкнено) | `1` |

#### Приклади URL:

- **Головна сторінка:** `/`
- **Категорія:** `/zariadni-stantsii` (без домену!)
- **Товар:** `/product/charger-model-1`
- **CMS сторінка:** `/about-us`

**⚠️ ВАЖЛИВО для мультимагазинів:**

Якщо у вас різні store views з префіксами в URL (наприклад `/ru/`, `/ua/`):
- ✅ **Правильно:** `/zariadni-stantsii` (БЕЗ префіксу store code)
- ❌ **Неправильно:** `/ru/zariadni-stantsii` (модуль автоматично видалить `/ru/`)

**Приклад:**
```
URL у браузері:       https://dev.triniti-sb.com.ua/ru/zariadni-stantsii/
URL у CSV:            /zariadni-stantsii
```

Модуль автоматично видалить префікс store code (`/ru/`, `/ua/` тощо) перед пошуком в базі.

**ВАЖЛИВО:** Використовуйте відносні URL (без `https://domain.com`)!

#### Кроки імпорту:

1. Відкрийте **Content → SEO Cross Links → Import / Export**
2. Натисніть **"Choose File"** та оберіть ваш CSV файл
3. Натисніть **"Upload and Import"**
4. Дочекайтесь повідомлення про успіх

**УВАГА:** Імпорт **повністю замінює** всі існуючі посилання!

### Експорт існуючих посилань

1. Відкрийте **Content → SEO Cross Links → Import / Export**
2. Натисніть кнопку **"Export to CSV"**
3. Завантажиться файл `seo_crosslinks_export.csv`

---

## 🎨 Налаштування відображення

### Де відображаються посилання?

За замовчуванням посилання відображаються внизу сторінки на:
- ✅ Категоріях товарів
- ✅ Сторінках товарів
- ✅ CMS сторінках

### Зміна розташування

Файли layout:
```
view/frontend/layout/catalog_category_view.xml
view/frontend/layout/catalog_product_view.xml
view/frontend/layout/cms_page_view.xml
```

Щоб змінити розташування, відредагуйте атрибут `name`:
```xml
<!-- Поточне: внизу контенту -->
<referenceContainer name="content.bottom">

<!-- Альтернативи: -->
<referenceContainer name="content">          <!-- Всередині контенту -->
<referenceContainer name="sidebar.main">     <!-- В сайдбарі -->
<referenceContainer name="content.top">      <!-- Вгорі контенту -->
```

### Налаштування шаблону

Шаблон посилань: `view/frontend/templates/seo_links.phtml`

Приклад кастомізації:
```php
<?php if ($links = $block->getLinks()): ?>
    <div class="seo-crosslinks my-custom-class">
        <h3><?= __('Рекомендовані посилання') ?></h3>
        <ul>
            <?php foreach ($links as $link): ?>
                <li>
                    <a href="<?= $escaper->escapeUrl($link['url']) ?>">
                        <?= $escaper->escapeHtml($link['anchor']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
```

---

## 🔍 Діагностика та тестування

### Тестовий блок (зелена мітка)

В файлі `view/frontend/layout/default.xml` є тестовий блок для діагностики:

```xml
<block class="Magento\Framework\View\Element\Template" 
       name="seo.crosslinks.test" 
       template="Triniti_SeoCrossLinks::test.phtml" />
```

Він виводить:
- ✅ Поточний URL сторінки
- ✅ Нормалізований URL
- ✅ Поточний Store ID

**Після успішного тестування** можна закоментувати цей блок.

### Перевірка роботи

1. Додайте тестове посилання:
```csv
store_id,donor_url,acceptor_url,anchor,sort_order,is_active
0,/,/contact,Зв'яжіться з нами,1,1
```

2. Імпортуйте CSV
3. Очистіть кеш:
```bash
php bin/magento cache:flush
```

4. Відкрийте головну сторінку сайту
5. Внизу сторінки повинно з'явитись посилання "Зв'яжіться з нами"

---

## ❓ Вирішення проблем

### Посилання не відображаються

**Можливі причини:**

1. **Невірний URL для мультимагазинів**
   - Якщо використовуєте store code в URL (`/ru/`, `/ua/`)
   - В CSV треба вказувати URL **БЕЗ** store code префіксу
   - Приклад: для `/ru/zariadni-stantsii/` в CSV вказуйте `/zariadni-stantsii`
   - Перевірте тестовий блок (зелена мітка) — він покаже правильний URL

2. **Немає даних для поточної сторінки**
   - Перевірте, чи `donor_url` співпадає з поточним URL
   - URL повинен бути в lowercase та без `/` в кінці

3. **Невірний Store ID**
   - Використовуйте `0` для всіх магазинів
   - Або конкретний ID магазину (перевірте в базі таблицю `store`)

4. **Посилання вимкнені**
   - Перевірте, чи `is_active = 1`

5. **Кеш не очищено**
   ```bash
   php bin/magento cache:flush
   ```

6. **Layout не оновився**
   ```bash
   php bin/magento setup:upgrade
   php bin/magento cache:clean layout
   ```

### Помилка "Invalid Document"

**Причина:** Була синтаксична помилка в `db_schema.xml`  
**Вирішення:** В цій версії вже виправлено ✅

### Помилка "Table doesn't exist"

**Причина:** Не виконано `setup:upgrade`  
**Вирішення:**
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### 404 Not Found в адмін-панелі

**Причина:** Модуль не увімкнено або не оновлено  
**Вирішення:**
```bash
php bin/magento module:enable Triniti_SeoCrossLinks
php bin/magento setup:upgrade
php bin/magento cache:flush
```

---

## 📊 Структура бази даних

Таблиця `triniti_seo_crosslink`:

| Поле | Тип | Опис |
|------|-----|------|
| entity_id | INT | Первинний ключ (AUTO_INCREMENT) |
| store_id | SMALLINT | ID магазину (0 = всі) |
| donor_url | VARCHAR(512) | Сторінка-донор (де з'являється посилання) |
| acceptor_url | VARCHAR(2048) | URL посилання |
| anchor | VARCHAR(512) | Текст посилання |
| sort_order | INT | Порядок сортування |
| is_active | SMALLINT | Активність (1/0) |
| created_at | TIMESTAMP | Дата створення |
| updated_at | TIMESTAMP | Дата оновлення |

---

## 🔐 Безпека

### ACL (Access Control List)

Доступ до модуля керується через ACL ресурс:
```
Triniti_SeoCrossLinks::import
```

Щоб надати доступ користувачу:
1. **System → Permissions → User Roles**
2. Оберіть роль
3. В **Role Resources** знайдіть **SEO Cross Links → Import / Export**
4. Встановіть чекбокс
5. Збережіть

### XSS Protection

Шаблон використовує escape-функції:
- `$escaper->escapeUrl()` для URL
- `$escaper->escapeHtml()` для тексту

---

## 🚀 Поради з використання

### 1. Оптимізація SEO

**Правильне використання anchor-текстів:**
- ✅ `Купити ноутбук MacBook Pro 16`
- ✅ `Дивіться всі смартфони Samsung`
- ❌ `Клікніть тут`
- ❌ `Перейти`

### 2. Сортування посилань

Використовуйте `sort_order` для контролю порядку відображення:
```csv
store_id,donor_url,acceptor_url,anchor,sort_order,is_active
0,/,/category/new,Новинки,1,1
0,/,/category/sale,Розпродаж,2,1
0,/,/category/bestsellers,Хіти продажів,3,1
```

### 3. Мультимагазини

Створюйте різні посилання для різних магазинів:
```csv
store_id,donor_url,acceptor_url,anchor,sort_order,is_active
1,/,/zariadni-stantsii,Зарядні станції,1,1
2,/,/zariadni-stantsii,Charging Stations,1,1
```

**Як працює з різними store views:**

Якщо у вас налаштовано store views з різними URL префіксами:
- Store "Ua" (ID: 1, код: `ua`) → URL: `https://site.com/zariadni-stantsii`
- Store "Ru" (ID: 2, код: `ru`) → URL: `https://site.com/ru/zariadni-stantsii`

**В CSV використовуйте URL БЕЗ store code:**
```csv
store_id,donor_url,acceptor_url,anchor,sort_order,is_active
1,/zariadni-stantsii,/product/charger-1,Зарядна станція 1,1,1
2,/zariadni-stantsii,/product/charger-1,Зарядная станция 1,1,1
```

Модуль автоматично:
1. Визначить поточний store code (`ru`, `ua` тощо)
2. Видалить його з URL (`/ru/zariadni-stantsii` → `/zariadni-stantsii`)
3. Знайде відповідні посилання в базі за `store_id`

**Діагностика:** Увімкніть тестовий блок (він показує зелену мітку внизу екрану) — він покаже який URL потрібно використовувати в CSV.

### 4. Регулярне оновлення

Експортуйте поточні посилання раз на тиждень для резервного копіювання:
```bash
# Можна автоматизувати через cron
0 0 * * 0 cd /var/www/magento && php bin/magento triniti:seo:export
```

---

## 📋 Що виправлено в версії 1.1.3

✅ **Виправлено синтаксичну помилку** в `etc/db_schema.xml` (рядок 12)  
✅ **Додано обов'язковий файл** `etc/db_schema_whitelist.json`  
✅ **Покращено ACL** в `etc/adminhtml/acl.xml`  
✅ **Видалено застарілий** `setup_version` з `etc/module.xml`  
✅ **Додано** `composer.json`  
✅ **Видалено службові файли** `.DS_Store`

---

## 📞 Підтримка

Якщо виникли питання або проблеми:

1. Перевірте розділ **"Вирішення проблем"** вище
2. Перегляньте логи Magento:
   ```bash
   tail -f var/log/system.log
   tail -f var/log/exception.log
   ```

3. Перевірте права доступу до файлів:
   ```bash
   find app/code/Triniti/SeoCrossLinks -type d -exec chmod 755 {} \;
   find app/code/Triniti/SeoCrossLinks -type f -exec chmod 644 {} \;
   ```

---

## ✅ Чеклист встановлення

- [ ] Модуль розпаковано в `app/code/Triniti/SeoCrossLinks/`
- [ ] Виконано `php bin/magento module:enable Triniti_SeoCrossLinks`
- [ ] Виконано `php bin/magento setup:upgrade`
- [ ] Виконано `php bin/magento cache:flush`
- [ ] Перевірено статус модуля (`module:status`)
- [ ] Перевірено створення таблиці в БД
- [ ] Імпортовано тестові дані
- [ ] Перевірено відображення на фронтенді
- [ ] Налаштовано права доступу в ACL

---

**Успішного використання модуля Triniti SEO Cross Links! 🎉**
