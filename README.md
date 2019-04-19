# RESTful APIs для modx revolution
Готовые конгтроллеры для работы с товарами, категориями minishop2 а так же с обычными ресурсами

Для начала работы необходимо скопировать папку **rest** в корневую директорию и прописать следующие:


#### для NGINX
```
server {
    ...........
    location /rest/ {
            try_files $uri @modx_rest;
    }
    location @modx_rest {
            rewrite ^/rest/(.*)$ /rest/index.php?_rest=$1&$args last;
    }
    ...........
}

```

После чего перезапустить веб-сервер
```
service nginx restart
```


#### Для Apache
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-s
RewriteRule ^(.*)$ rest/index.php?_rest=$1 [QSA,NC,L]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^(.*)$ rest/index.php [QSA,NC,L]
```

## Опубликованные методы

Запросы для получения данных 

Список товаров
```http://restfulapis.bustep.ru/rest/products```

Список категорий
```http://restfulapis.bustep.ru/rest/categories```

Список ресурсов
```http://restfulapis.bustep.ru/rest/resources```

Дополнительно добавлена функция для удобного просмотра результатов в json формате. Для этого вам необходимо в конце url дописать ```print=1```
```http://restfulapis.bustep.ru/rest/resources?print=1```

Получите вот такой результат
```json
{
    "results": [
        {
            "id": 1,
            "pagetitle": "Главная",
            "longtitle": "",
            "description": "",
            "parent": 0,
            "introtext": null,
            "content": "{include 'file:resources/main.tpl'}",
            "menuindex": 0,
            "page_url": "http://restfulapis.bustep.ru/"
        },
        {
            "id": 7,
            "pagetitle": "Страница не найдена",
            "longtitle": "",
            "description": "",
            "parent": 5,
            "introtext": null,
            "content": "{include 'file:resources/service/error404.tpl'}",
            "menuindex": 1,
            "page_url": "http://restfulapis.bustep.ru/error404"
        }
    ],
    "total": 2
}
```

Чтобы получить полную информацию о ресурсе, можно указать ID ресурса

```http://restfulapis.bustep.ru/rest/products/1```