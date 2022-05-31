<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

## Reto Técnico Levo

Esté proyecto es un api que escucha en los endpoint `api/create/` y `api/update` para manejar transacciones usando la biblioteca 
[spatie/event-sourcing](https://spatie.be/docs/laravel-event-sourcing/v7/introduction)

### Setup

El prooyecto se levanto usando Sail, siguiendo la documentación [oficial de Laravel](https://laravel.com/docs/9.x/sail#main-content)

#### Para levantarlo el proyecto

se copia el `.env.example`
```
cp .env.example .env
```

Dentro del repositorio, se escribe el siguiente comando
```
./vendor/bin/sail up
```

####  pruebas  
Dentro del repositorrio se escribe el comando
```
./vendor/bin/sail artisan test
```


Para ejecutar las pruebas de Postman, ese necesario tener instalado postman  
`seleccionar importar colección -> seleccionar el archivo json que se encuentra en la carpeta postman de este repositorio-> ejecutar la colección`.



## Informacion adicional

- Introducción al Event Sourcing con Laravel: https://www.youtube.com/watch?v=9tbxl_I1EGE
