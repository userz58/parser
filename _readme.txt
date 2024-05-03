docker-compose exec php sh

composer req webapp
composer req symfony/panther symfony/dom-crawler symfony/css-selector
composer require --dev dbrekelmans/bdi
vendor/bin/bdi detect drivers

composer req liip/imagine-bundle oneup/flysystem-bundle mk-j/php_xlsxwriter



???
composer req guzzlehttp/guzzle

 logger symfony/dom-crawler symfony/css-selector symfony/translation-contracts mk-j/php_xlsxwriter liip/imagine-bundle guzzlehttp/guzzle


docker-compose exec php composer require symfony/panther oneup/flysystem-bundle logger symfony/dom-crawler symfony/css-selector symfony/translation-contracts mk-j/php_xlsxwriter liip/imagine-bundle guzzlehttp/guzzle

------------------
Dockerfile recipes
------------------

###> liip/imagine-bundle
###> GD
RUN apk add --update --no-cache freetype libjpeg-turbo libpng freetype-dev libjpeg-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd \
    && apk del --no-cache freetype-dev libjpeg-turbo-dev libpng-dev \
    && rm -rf /tmp/*
###< GD
###> ImageMagick
RUN apk add --update --no-cache autoconf g++ imagemagick imagemagick-dev libtool make pcre-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apk del autoconf g++ libtool make pcre-dev
###< ImageMagick
###< liip/imagine-bundle

# Install mbstring extension - расширение для работы с киррилицей
RUN apk add --update --no-cache oniguruma-dev \
    && docker-php-ext-install mbstring \
    && docker-php-ext-enable mbstring \
    && rm -rf /tmp/*
------------------
Установка
------------------

docker-compose up -d --build

docker-compose exec php sh

docker-compose exec php composer require symfony/panther oneup/flysystem-bundle logger symfony/dom-crawler symfony/css-selector symfony/translation-contracts mk-j/php_xlsxwriter liip/imagine-bundle guzzlehttp/guzzle
composer require deeplcom/deepl-php


composer require symfony/orm:*
composer require stof/doctrine-extensions-bundle, symfony/serializer


docker-compose exec php composer require --dev dbrekelmans/bdi
------------------
Запуск
------------------

docker-compose exec php php -d memory_limit=-1 bin/console parser:gedore
docker-compose exec php php -d memory_limit=-1 bin/console parser:cat


*/30 * * * * cd /Users/vladimir/Sites/symfony/parser; docker-compose restart; docker-compose exec php php -d memory_limit=-1 bin/console parser:cat


////////////////////

Удалить служебные файлы

find . -name "._*" -type f -delete
find . -name ".DS_Store" -depth -exec rm {} \;



////////////////////////////////////////////////////////////////////

GEDORE

docker-compose exec php bin/console parser:gedore

загружить спарсенный xsl файл
- страницу категорий
- страницу товары
- страницу товары без категорий
- скопировать лист товаров; на новом листе удалить колонки кроме категории и изображения; удалить дубликаты по колонке раздел; загрузить как категории чтобы появились картинки на категориях
- переименовать категории (сделать уникальные названия используя сначения в скобках)
- разобрать товары без категорий
- переименовывать и редактировать характеристики и описания товаров




FEIN

https://fein.com/robots.txt

https://fein.com/ru_ru/?type=841132


// категории
https://fein.com/ru_ru/?sitemapType=sitemapPgr&type=841132&cHash=533ae95f1920c83cecf116d3c7899a74
// товары
https://fein.com/ru_ru/?sitemapType=sitemapProMachine&type=841132&cHash=dd3a9f5def6520e6565c446d4ab749ca
// принадлежности
https://fein.com/ru_ru/?sitemapType=sitemapProAccessory&type=841132&cHash=6bba2764a5aaf7e51662fca002405132


// Новости и страницы
https://fein.com/ru_ru/?sitemapType=sitemapPages&type=841132&cHash=3f70209dd238b6596d57feba2768343c
https://fein.com/ru_ru/?sitemapType=sitemapNews&type=841132&cHash=651b377065adfddfd288d802d25c2802


//////////////
php proxy


списки прокси с php
https://hackware.ru/?p=8920


$url = 'http://dynupdate.no-ip.com/ip.php';
$proxy = '127.0.0.1:8888';
//$proxyauth = 'user:password';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_PROXY, $proxy);
//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
$curl_scraped_page = curl_exec($ch);
curl_close($ch);

echo $curl_scraped_page;



https://www.geeksforgeeks.org/how-to-use-curl-via-a-proxy/

-----------------------------

создать сжатый архив


tar -zcvf archive.tar.gz .

tar -zcvf archive.tar.gz /path/to/files

Распаковать

tar -zxvf archive.tar.gz


не перезаписывать существующие
tar -zxvfk cat-parts.tar.gz -C ~/Sites/symfony/common-filestorage/


Эта команда извлечет из архива archive.tar файлы myfile1 и dir2/myfile2
tar -tf archive.tar
tar -xf archive.tar myfile1 dir2/myfile2


***********************************************

Удалить служебные файлы

find . -name "._*" -type f -delete
find . -name ".DS_Store" -depth -exec rm {} \;



удалить пустые файлы
find . -type f -empty -delete

удалить пустые json файлы - содержимое []
find ./json -type f -size -4c -delete

find ./json -type f -empty -delete
find . -type f -empty -delete


tar -zcvf /Volumes/samsung/common-filestorage/cat-parts.tar.gz parts-cat-com/


Чтобы сразу удалить все неиспользуемые контейнеры, тома, сети и образы:
docker system prune -a -f --volumes


git remote add origin https://github.com/userz58/parser.git
git branch -M main
git push -u origin main
