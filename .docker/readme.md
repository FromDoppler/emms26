_Pasos a seguir para correr la nueva version de Docker por primera vez._

- Apagar cualquier otro servicio de docker y xampp que haya prendido.
- Copiar `.env.example` a `.env` y configurar las variables necesarias.
- Borrar la carpeta node_modules y el archivo package-lock.json de la raiz del theme.
- Hacer un docker up.
- Enjoy.

_Pasos a seguir para generar los archivos de deploy del Theme por primera vez._

- Entrar por consola a la maquina virtual del theme.
- Correr el comando npm cache clear --force.
- Correr el comando npm run serve:prod.
- Enjoy.

---

## 🚀 Servicios Disponibles

### **Web & Database**

- **Web**: http://localhost:80
- **phpMyAdmin**: http://localhost:8080
- **MariaDB**: localhost:3406

### **Redis & Monitoring**

- **Redis**: localhost:6379
- **RedisInsight**: http://localhost:8001

### **Workers (Redis Streams)**

- `email-worker` - Procesamiento asíncrono de emails
- `list-worker` - Integración con Doppler
- `spreadsheet-worker` - Integración con Google Sheets

---

## 🔧 RedisInsight

RedisInsight viene **pre-configurado** con 3 conexiones:

- **local**: Contenedor Redis local
- **qa**: Servidor Redis QA (configurado en `.env`)
- **production**: Servidor Redis Production (configurado en `.env`)

Solo abre http://localhost:8001 y las 3 conexiones ya estarán disponibles.

---

## 💾 Persistencia de Datos

| Servicio         | Almacenamiento                             |
| ---------------- | ------------------------------------------ |
| **MariaDB**      | Directorio local: `.docker/db/data/`       |
| **Redis**        | Volumen Docker: `emms26_redis_data`        |
| **RedisInsight** | Volumen Docker: `emms26_redisinsight_data` |

**MariaDB** usa directorio local para facilitar respaldos y migraciones.
**Redis/RedisInsight** usan volúmenes Docker (gestionados automáticamente, sin problemas de permisos).
