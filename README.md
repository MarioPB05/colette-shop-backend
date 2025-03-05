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
