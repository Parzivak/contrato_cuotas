## Decisiones de Arquitectura

El proyecto se desarrolla con la estructura estándar del framework Symfony, pero aplicando principios de **Arquitectura Limpia** para garantizar la separación de responsabilidades, la mantenibilidad y la testabilidad.

### 1. CQS (Command/Query Separation)

Aunque la estructura de directorios es la estándar (`Service/`, `Controller/`), se aplica el principio CQS mediante el patrón de **Servicios como Handlers**:

* **Entrada de Datos:** Se utilizan **DTOs** (`ContractRequest.php`, etc.) para desacoplar los datos de la petición HTTP.
* **Lógica Aislada:** El `ContractController.php` actúa como un **Dispatcher** de comandos y el `ContractService.php` actúa como un **Handler**, conteniendo toda la lógica de negocio y persistencia. Los controladores permanecen "delgados".

### 2. Inversión de Dependencias (Interfaces de Repositorio)

Para desacoplar el Dominio del ORM (Doctrine), se implementó el principio de Inversión de Dependencias:

* Se definió la **`ContractRepositoryInterface`** (el contrato de datos).
* El **`ContractService.php`** (el Handler) depende de la **Interfaz** (`ContractRepositoryInterface`), y no de la implementación concreta de Doctrine (`ContractRepository.php`).

Esto asegura que el corazón de la lógica de negocio (el Servicio) no tiene conocimiento directo de cómo se almacenan los datos, mejorando la testabilidad (como se demuestra en las pruebas unitarias).
