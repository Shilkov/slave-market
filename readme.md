# Тестовое задание "Биржа рабов" (Древнее HR агентство)

## Описание биржи

Биржа рабов позволяет покупать или брать в аренду рабов для различных задач:

+ земледелие;
+ скотоводство;
+ работа по дому (уборка, приготовление пищи);
+ работа в каменоломне
+ и др.

Оплата производится золотом.

Для удобства клиентов весь ассортимент разбит по категориям, например:

> Популярное -> Для кухни -> Мытьё посуды

Раб может находиться в нескольких категориях одновременно.

Для описания рабов используются характеристики:

+ Кличка;
+ Пол;
+ Возраст;
+ Вес;
+ Цвет кожи;
+ Где пойман/выращен;
+ Описание и повадки (например, любит играть с собакой);
+ Ставка почасовой аренды;
+ Стоимость.


## Задание №1. Реализовать операцию аренды раба

**Цель задания** - выяснить уровень владения ООП, навыки построения абстракций и знание принципов и инструментов разработки.

**Всё, что не описано - на совести и фантазии разработчика!**

### Описание системы аренды

Для успешного бизнеса необходимо спроектировать систему расчёта аренды рабов.

#### Процесс аренды (сильно упрощён):

1. Пользователь находит подходящего раба в каталоге и переходит на страницу аренды раба.
2. Пользователь выбирает желаемое время аренды (например, с 01 июня 2016 14.00 по 05 июня 2016 20.00).
    + Если аренда на выбранное время возможна, система оформляет аренду и выводит договор аренды с итоговой стоимостью.
    + Если аренда невозможна, выводится информация о причинах.

#### Требования и ограничения:

+ У покупателей (хозяев) бесконечное количество золота.
+ Рабы не могут работать больше 16 часов в сутки.
+ Рабочий день начинается с 00:00.
+ Время аренды округляется до часов в большую сторону, например:
    + раба арендуют с 11.30 до 13.00, то это 3 часа: 11, 12, 13
    + раба арендуют с 12.00 до 12.30, то это 1 час - 12
+ При аренде на несколько дней:
    + в полных днях:
        + правило 16 часов не проверяется (всё на совести клиента)
        + считается, что заняты все 24 часа
        + стоимость дня = стоимости 16 часов
    + в неполных днях:
        + часы первого дня считаются с момента аренды и до конца дня
        + часы последнего дня считаются с 00:00 до конца аренды
        + правило 16 часов проверяется
+ Нельзя арендовать раба на выбранный период, если хотя бы один час в периоде уже занят.
+ VIP клиенты имеют приоритет перед обычными и могут игнорировать занятые не VIP-ами часы.
+ Если аренда невозможна, в причине должна быть подробная информация о перекрытии аренды по времени.

### Ожидаемый результат

#### Обязательно:

+ Классы, содержащие данные и реализующие логику расчётов, проверок и т.д. из описания.

#### Желательно:

+ Тесты к написанному коду (в [проекте-болванке](https://github.com/pvbogdanov/slave-market) уже используются [Prophecy](https://github.com/phpspec/prophecy) и [PHPUnit](https://phpunit.de/)).
+ Комментарии, диаграммы, схемы и т.п. для объяснения принятых решений.

#### Что не нужно реализовывать:

+ Работу с БД и сохранение - можно использовать заглушки (stub-ы и mock-и).
+ Пользовательский интерфейс (UI)

#### Что можно использовать:

+ Любые инструменты/библиотеки/фреймворки/велосипеды!
+ Наш [проект-болванку](https://github.com/pvbogdanov/slave-market) (вы сейчас в нём =), как стартовую точку:
    + Форкнуть репозиторий.
    + Посмотреть уже написанные классы и тесты.
    + Развернуть окружение (нужен только PHP и пара модулей из composer.json)
    + Можете воспользоваться образом Docker (см. [docker_usage](./docker_usage.md))
    + Установить composer-зависимости
    + Попробовать написать код, чтобы та пара интеграционных тестов прошла.
    + Добавить ещё тестов к коду (своему и нашему)
    + **Любой код в этой болванке можно менять!** - это всего лишь пример/направление мысли.

## Задание №2. Спроектировать схему БД и написать запросы

**Цель задания** - выяснить уровень владения SQL и знания по устройству БД.

**Нужны только SQL запросы, никакой визуализации!**

Спроектировать структуру базы данных и оптимизировать для работы следующих выборок:

+ Получить минимальную, максимальную и среднюю стоимость всех рабов весом более 60 кг.
<pre><code>
select min(price_buy), max(price_buy), avg(price_buy)
from slave
where weight > 60;
</code></pre>
 
+ Выбрать категории, в которых больше 10 рабов.
<pre><code>
select *
from category
where
    id in (
        select category_id
        from slave_category
        group by category_id HAVING COUNT(*) > 10
    );
</code></pre>
    
+ Выбрать категорию с наибольшей суммарной стоимостью рабов.
<pre><code>
select *
from category
where id = (
    select category_id
    from (
             select category_id, sum(price_buy) as summ_price
             from slave
                      right join slave_category sc on slave.id = sc.slave_id
             group by category_id
             order by summ_price DESC
             limit 1
         ) sum_price
)
</code></pre>
+ Выбрать категории, в которых мужчин больше чем женщин.
<pre><code>
from category
where id in (
    select category_id
    from (
             select count(male) male, count(female) female, category_id
             from (
                      SELECT CASE WHEN sex = 1 THEN 1 END as female,
                             CASE WHEN sex = 0 THEN 1 END as male,
                             sc.category_id
                      FROM slave
                               right join slave_category sc on slave.id = sc.slave_id
                  ) gender
             group by category_id
         ) count_gender_in_catigory
    where count_gender_in_catigory.female > count_gender_in_catigory.male
)
</code></pre>
+ Количество рабов в категории "Для кухни" (включая все вложенные категории).
<pre><code>
select count(*)
from (
         select distinct slave_id
         from slave_category
         where category_id in (
             select id
             from category
             where 
                nested_left >= 1
                and nested_right <= 6
         )
     ) slave_in_category
</code></pre>
`примечание 1 и 6 это узды в нестедсете для катигории в которой мы ищем.`
<pre><code>
select count(*)
from (
         select distinct slave_id
         from slave_category
         where category_id in (
             select category.id
             from category
                      inner join category as parent on parent.title = 'cat 1'
             where category.nested_left >= parent.nested_left
               and category.nested_right <= parent.nested_right
         )
     ) slave_in_category;
</code></pre>
Можно использовать любую реляционную СУБД, например, MySQL или PostgreSQL.