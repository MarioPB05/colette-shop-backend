# Colette's Shop - Backend
Este es el backend de **Colette's Shop**, una tienda online donde los usuarios pueden comprar cajas de brawlers aleatorios, gestionar su inventario y dejar reseñas.

El backend está desarrollado con **Symfony**, utilizando **PostgreSQL** como base de datos y un sistema de validación robusto.

## 🌐 Enlaces
- [Frontend](https://colette-shop.onrender.com/)
- [Backend](https://colette-shop-backend.onrender.com)

## 🚀 Tecnologías utilizadas
- **Symfony** - Framework PHP para backend
- **PostgreSQL** - Base de datos relacional
- **Mailer** - Envío de correos electrónicos
- **Twig** - Motor de plantillas
- **Validator** - Validación de datos en formularios
- **UID** - Generación de identificadores únicos

## 📂 Estructura del proyecto
```plaintext
src/
├── Controller/            # Controladores de la API
├── DTO/                   # Objetos de transferencia de datos
├── Entity/                # Entidades de la base de datos
├── Enum/                  # Enumeraciones
├── EventListener/         # Listeners de eventos
├── Repository/            # Repositorios de las entidades
```

## ⚙️ Instalación y configuración
### 1️⃣ Requisitos previos
Asegúrate de tener instalados:
* PHP 8.1 o superior
* Composer
* PostgreSQL
* Symfony CLI (Opcional, pero recomendado)

### 2️⃣ Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/colette-shop-backend.git
cd colette-shop-backend
```

### 3️⃣ Instalar dependencias
```bash
composer install
```

### 4️⃣ Configurar variables de entorno
Crea un archivo .env y configura los valores:
```ini
APP_SECRET=
DATABASE_URL=
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=
COMPOSE_PROJECT_NAME="symfony"
MAILER_DSN=
CORS_ALLOW_ORIGIN='*'
```

### 5️⃣ Iniciar el servidor de desarrollo
```bash
symfony server:start
```

El backend estará disponible en http://localhost:8000.

ahora la seccion de como crear la base de datos
## 🗄️ Base de datos
```postgresql
create table if not exists rarity
(
    id    serial
        primary key,
    name  varchar(200) not null,
    color varchar(100) not null
);

create table if not exists brawler
(
    id             serial
        primary key,
    name           varchar(200)  not null,
    image          varchar(1000) not null,
    pin_image      varchar(1000) not null,
    model_image    varchar(1000) not null,
    portrait_image varchar(1000) not null,
    rarity_id      integer       not null
        constraint fk_brawler_rarity
            references rarity
);

create table if not exists client
(
    id        serial
        primary key,
    name      varchar(200) not null,
    surname   varchar(400) not null,
    birthdate date         not null,
    dni       char(9)      not null
);

create table if not exists "user"
(
    id             serial
        primary key,
    username       varchar(200)      not null,
    password       varchar(500)      not null,
    email          varchar(200)      not null,
    gems           integer default 0 not null,
    brawl_tag      char(9)           not null,
    enabled        boolean           not null,
    client_id      integer           not null
        constraint uniq_8d93d64919eb6921
            unique
        constraint fk_user_client
            references client,
    role           integer           not null,
    brawler_avatar integer
        constraint fk_user_brawler
            references brawler
);

create table if not exists user_favorite_brawlers
(
    user_id    integer not null
        constraint fk_user_client
            references "user",
    brawler_id integer not null
        constraint fk_user_brawler
            references brawler,
    primary key (user_id, brawler_id)
);

create table if not exists box
(
    id               serial
        primary key,
    name             varchar(200)          not null,
    price            double precision      not null,
    quantity         integer               not null,
    brawler_quantity integer default 5     not null,
    deleted          boolean default false not null,
    type             integer               not null,
    pinned           boolean default false not null
);

create table if not exists box_brawler
(
    id          serial
        primary key,
    box_id      integer          not null
        constraint fk_box_brawler_box
            references box,
    brawler_id  integer          not null
        constraint fk_box_brawler_brawler
            references brawler,
    probability double precision not null
);

create table if not exists box_daily
(
    id                 serial
        primary key,
    repeat_every_hours integer not null,
    box_id             integer not null
        constraint uniq_f7b77782d8177b3f
            unique
        constraint fk_box_daily_box
            references box
);

create table if not exists box_review
(
    id        serial
        primary key,
    rating    integer        not null,
    comment   varchar(10000) not null,
    user_id   integer        not null
        constraint fk_box_review_user
            references "user",
    box_id    integer        not null
        constraint fk_box_review_box
            references box,
    post_date timestamp default now()
);

create table if not exists "order"
(
    id             serial
        primary key,
    invoice_number varchar(20)                         not null
        constraint uniq_f52993982da68207
            unique,
    purchase_date  timestamp default CURRENT_TIMESTAMP not null,
    state          integer   default 0                 not null,
    cancelled      boolean   default true              not null,
    user_id        integer                             not null
        constraint fk_inventory_user
            references "user"
);

create table if not exists gem_transaction
(
    id      serial
        primary key,
    gems    integer                             not null,
    date    timestamp default CURRENT_TIMESTAMP not null,
    user_id integer                             not null
        constraint fk_gem_transaction_user
            references "user"
);

create table if not exists order_discount
(
    id             serial
        primary key,
    order_id       integer          not null
        constraint fk_order_discount_order
            references "order",
    transaction_id integer          not null
        constraint fk_order_discount_transaction
            references gem_transaction,
    discount       double precision not null
);

create unique index if not exists uniq_1856bf8d9f6d38
    on order_discount (order_id);

create unique index if not exists uniq_1856bf2fc0cb0f
    on order_discount (transaction_id);

create table if not exists inventory
(
    id           serial
        primary key,
    price        double precision                    not null,
    open         boolean   default false             not null,
    collect_date timestamp default CURRENT_TIMESTAMP not null,
    open_date    timestamp,
    box_id       integer                             not null
        constraint fk_inventory_box
            references box,
    user_id      integer                             not null
        constraint fk_inventory_user
            references "user",
    order_id     integer                             not null
        constraint fk_inventory_order
            references "order"
);

create table if not exists user_brawler
(
    id           serial
        primary key,
    quantity     integer not null,
    brawler_id   integer not null
        constraint fk_user_brawler_brawler
            references brawler,
    user_id      integer not null
        constraint fk_user_brawler_user
            references "user",
    inventory_id integer not null
        constraint fk_user_brawler_inventory
            references inventory
);
```

## 📦 Despliegue
Para generar una versión lista para producción, usa el docker compose incluido en el repositorio.
```bash
docker-compose up
```
_Solo se crea el contenedor de PHP, por lo que necesitarás crear una base de datos PostgreSQL y configurar las variables de entorno._

## 🧑‍💻 Autores
- [Mario Perdiguero Barrera](https://github.com/MarioPB05)
- [David Pérez Fernández](https://github.com/david-perez-2357)
- [David Zamora Martínez](https://github.com/TicoticoSAFA)
