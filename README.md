# Лас Кукарачас — сервис лобби

Веб-сервис для настольной игры «Лас Кукарачас»: ведущий создаёт лобби на
экране (проектор), игроки подключаются со смартфонов по QR-коду, выбирают
имя и аватар, а затем в каждом раунде угадывают эмоции друг друга. Экран
ведущего обновляется в реальном времени через WebSocket (Laravel Reverb).

Стек: Laravel 13, Blade + Alpine.js, Tailwind CSS 4, Laravel Reverb
(broadcasting), Spatie Laravel Data, SQLite.

## Локальная разработка

Требования: PHP ^8.3, Composer, Node.js/npm.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
```

Приложению нужны три параллельных процесса — HTTP-сервер, WebSocket-сервер
(Reverb) и сборщик фронтенда:

```bash
composer run dev
```

Эта команда (см. `composer.json`) поднимает `php artisan serve`,
`php artisan reverb:start`, `php artisan queue:listen`, `php artisan pail`
и `npm run dev` одновременно. Приложение будет доступно на
`http://localhost:8000`.

Запустить тесты: `php artisan test`. Отформатировать код: `vendor/bin/pint`.

## Деплой на сервер с Apache2

Ниже — пошаговая инструкция и примеры конфигов для боевого сервера на
Ubuntu/Debian с Apache2. В качестве домена используется `kukarachas.example.com`
— замените на свой везде, где он встречается.

### 1. Требования к серверу

- PHP 8.3+ с расширениями: `mbstring`, `xml`, `curl`, `pdo`, `sqlite3` (или
  `mysql`/`pgsql`, если используете другую БД), `bcmath`, `fileinfo`, `ctype`,
  `tokenize`.
- Composer.
- Node.js + npm (нужен только на этапе сборки фронтенда — на сервере он
  не обязателен постоянно, но упрощает деплой).
- Apache2 с модулями `rewrite`, `proxy`, `proxy_http`, `proxy_wstunnel`,
  `ssl`, `headers`.
- `systemd` для управления процессом Reverb как службой.

Включите нужные модули Apache:

```bash
sudo a2enmod rewrite proxy proxy_http proxy_wstunnel ssl headers
```

### 2. Код и зависимости

```bash
sudo mkdir -p /var/www/kukarachas
sudo chown $USER:$USER /var/www/kukarachas
git clone <ваш-репозиторий> /var/www/kukarachas
cd /var/www/kukarachas

composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

`npm run build` кладёт собранные ассеты в `public/build` — после сборки
Node.js на сервере больше не нужен для обслуживания запросов, только сам
Apache и PHP.

### 3. Файл `.env`

```bash
cp .env.example .env
php artisan key:generate --force
```

Отредактируйте `.env` под боевые настройки. Ключевые отличия от локальной
разработки:

```dotenv
APP_NAME="Лас Кукарачас"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kukarachas.example.com

DB_CONNECTION=sqlite
# путь ниже должен существовать и быть доступен для записи пользователю
# Apache (см. шаг про права доступа)
# DB_DATABASE=/var/www/kukarachas/database/database.sqlite

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database

# Свои случайные значения, не совпадающие с локальными!
REVERB_APP_ID=<сгенерируйте-случайное-число>
REVERB_APP_KEY=<сгенерируйте-случайную-строку>
REVERB_APP_SECRET=<сгенерируйте-случайную-строку>

# Backend (Laravel) обращается к Reverb напрямую, в обход Apache —
# сервер, порт и схема внутренние:
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

# Браузер игрока подключается к тому же домену по wss:// через 443 —
# Apache проксирует этот путь на внутренний Reverb (см. конфиг ниже):
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST=kukarachas.example.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

Случайные значения для `REVERB_APP_ID/KEY/SECRET` можно сгенерировать так:

```bash
php -r 'echo bin2hex(random_bytes(4)), PHP_EOL, bin2hex(random_bytes(10)), PHP_EOL, bin2hex(random_bytes(10)), PHP_EOL;'
```

Если меняли `VITE_REVERB_*`, пересоберите фронтенд (`npm run build`) —
эти значения зашиваются в JS-бандл при сборке.

### 4. Права доступа

```bash
sudo chown -R www-data:www-data /var/www/kukarachas
sudo find /var/www/kukarachas -type d -exec chmod 755 {} \;
sudo find /var/www/kukarachas -type f -exec chmod 644 {} \;
sudo chmod -R 775 /var/www/kukarachas/storage /var/www/kukarachas/bootstrap/cache
```

### 5. Миграции и кеш конфигурации

```bash
cd /var/www/kukarachas
php artisan migrate --force
php artisan db:seed --force --class=Database\\Seeders\\EmotionSeeder

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

При каждом следующем деплое (обновлении кода) повторяйте: `composer install
--no-dev`, `npm run build`, `migrate --force`, и обязательно
`config:cache`/`route:cache`/`view:cache` заново — иначе Laravel продолжит
использовать закешированные старые значения.

### 6. Служба Reverb (systemd)

Reverb должен работать постоянно как отдельный процесс, слушая только
`127.0.0.1` — наружу его порт светить не нужно, снаружи все запросы идут
через Apache на 443.

Создайте `/etc/systemd/system/kukarachas-reverb.service`:

```ini
[Unit]
Description=Лас Кукарачас — Reverb WebSocket server
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/kukarachas
ExecStart=/usr/bin/php artisan reverb:start --host=127.0.0.1 --port=8080
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Включите и запустите:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now kukarachas-reverb
sudo systemctl status kukarachas-reverb
```

Очередь (`QUEUE_CONNECTION`) отдельного воркера не требует: все broadcast-
события в этом приложении используют `ShouldBroadcastNow` и отправляются
синхронно, без очереди.

### 7. Конфиг Apache для домена

Создайте `/etc/apache2/sites-available/kukarachas.conf`:

```apache
# Редирект с HTTP на HTTPS
<VirtualHost *:80>
    ServerName kukarachas.example.com
    Redirect permanent / https://kukarachas.example.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName kukarachas.example.com
    DocumentRoot /var/www/kukarachas/public

    <Directory /var/www/kukarachas/public>
        AllowOverride All
        Require all granted
    </Directory>

    # WebSocket (Laravel Echo / Reverb, Pusher-совместимый протокол).
    # Браузер стучится на wss://kukarachas.example.com/app/{key} —
    # проксируем именно этот путь на внутренний Reverb.
    ProxyRequests Off
    ProxyPass /app/ ws://127.0.0.1:8080/app/
    ProxyPassReverse /app/ ws://127.0.0.1:8080/app/

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/kukarachas.example.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/kukarachas.example.com/privkey.pem

    ErrorLog ${APACHE_LOG_DIR}/kukarachas-error.log
    CustomLog ${APACHE_LOG_DIR}/kukarachas-access.log combined
</VirtualHost>
```

`mod_proxy_wstunnel` сам определяет заголовок `Upgrade: websocket` в
запросах на `/app/` и переключает соединение в WebSocket-туннель — отдельно
настраивать заголовки `Upgrade`/`Connection` для Apache (в отличие от
nginx) не нужно.

Путь `/apps/` (HTTP API Reverb, которым пользуется сам Laravel-бэкенд для
публикации событий) сознательно НЕ проксируется наружу — бэкенд обращается
к Reverb напрямую по `127.0.0.1:8080`, и секрет приложения (`REVERB_APP_SECRET`)
никогда не покидает сервер.

Включите сайт и перезапустите Apache:

```bash
sudo a2ensite kukarachas.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
```

Если сертификата ещё нет — получите его через certbot (плагин apache сам
допишет `SSLEngine`/пути к сертификату в конфиг, если не хотите прописывать
вручную):

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d kukarachas.example.com
```

### 8. Проверка после деплоя

- `https://kukarachas.example.com/` открывает страницу создания лобби.
- В консоли браузера на экране ведущего не должно быть ошибок
  `WebSocket connection failed` — если есть, проверьте:
  - что служба `kukarachas-reverb` запущена (`systemctl status`);
  - что модули `proxy`/`proxy_wstunnel` включены и Apache перезапущен;
  - что `VITE_REVERB_*` в `.env` указывают на публичный домен/443/https,
    а фронтенд пересобран после их изменения (`npm run build`);
  - что фаервол не блокирует локальные соединения Apache → 127.0.0.1:8080.
- Создайте тестовое лобби и присоединитесь со второго устройства — при
  входе игрока экран ведущего должен обновиться без перезагрузки страницы.
