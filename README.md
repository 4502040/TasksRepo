Тестовый проект для Tasks REST
1. Клонируем проект
2. Каталогом проекта является www
3. docker-compose up && Устанавливаем зависимости, можно зайти внутрь контейнера или через docker-compose exec symfony_app composer update
4. Тестируем через постман (https://www.postman.com/galactic-shuttle-4373/workspace/teamit/collection/9242404-0c9ebb8d-3b58-44d2-88a0-ba09daeee13b?action=share&source=copy-link&creator=9242404)
5. В каталоге Postman находятся коллекции запросов постман

Сделано:
1. Аутентификация с JWT
2. Логин / регистрация пользователей
3. CRUD Задачи
4. Пагинация списка задач
5. Фильтрация по статусу в списке задач
6. Тесты методов контроллеров с фикстурой
7. Команды для настройки тестовой бд :


php bin/console --env=test doctrine:database:drop --force

php bin/console --env=test doctrine:database:create

php bin/console --env=test doctrine:migrations:migrate
