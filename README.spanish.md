# Papyrus: Almacenando datos en el sistema de archivos con PHP

## Generalidades


El paquete Papyrus puede almacenar información en archivos, realizar consultas, actualizaciones y eliminar datos. Todo esto, basado en una llave primaria o identificador. La información es almacenada en formato JSON. 

## Instalación

Para instalar vía [Composer](http://getcomposer.org/), escribe lo siguiente en tu archivo composer.json:

    {
        "require": {
            "barbosa/papyrus": "dev-master"
        }
    }

O descarga el paquete de [Github](https://github.com/barbosa89/papyrus):

    http://github.com/barbosa89/papyrus

## Configuración

Papyrus necesita un arreglo de configuraciones y una ruta a la carpeta que contenga los archivos de almacenamiento. 

#### Arreglo de configuraciones

Crea un arreglo de configuraciones en un archivo o en el archivo en el cual instancies a Papyrus. El arreglo debe contener dos claves primarias, extensión y archivos.

	<?php
    
    /**
     * Ejemplo en un archivo de configuraciones.
     */

    return [

            'extension'	=>	'.project',

            'files' =>  [
                        'users' => ['dni(int#)', 'name', 'lastName']
                        ]
	       ];

Otra forma, en el archivo en el cual instancias a Papyrus:

    <?php 

    require 'vendor/autoload.php';

    use \Barbosa\Papyrus\Papyrus;

    $config = [

            'extension' =>  '.project',

            'files' =>  [
                        'users' => ['dni(int#)', 'name', 'lastName']
                        ]
            ];

    $papyrus = new Papyrus($config);


Esta extensión es para todos los archivos:

    'extension' => '.project'


La estructura de los archivos representa los campos en una tabla SQL, lo que está entre corchetes, son los campos de cada archivo:

    'files' => [

        'fileName' => ['primaryKey(int#)', 'field', 'otherField'],
        'otherFileName' => ['primaryKey(str#)', 'field', 'otherField']
		
        ]

Las opciones para llaves primarias son las siguientes:

	Auto increment integer (int++)
	Unique integer (int#)
	Unique string (str#)

#### Ruta a la carpeta de almacenamiento

Los archivos de almacenamiento que sean configurados en el arreglo, deben ser creados en una carpeta seleccionada por el usuario, la ruta de la carpeta debe ser pasada a Papyrus.

    <?php 

    require 'vendor/autoload.php';

    use \Barbosa\Papyrus\Papyrus;

    /**
     * Ejemplo completo. 
     */

    $config = [

            'extension' =>  '.project',

            'files' =>  [
                        'users' => ['dni(int#)', 'name', 'lastName']
                        ]
            ];

    $path = realpath(__DIR__ . '/storageFolder/');

    $papyrus = new Papyrus($config, $path);

Puedes emplear cualquier forma de configuración:

    <?php 

    require 'vendor/autoload.php';

    use \Barbosa\Papyrus\Papyrus;

    /**
     * Ejemplo completo con métodos setters. 
     */

    $papyrus = new Papyrus();

    $papyrus->setStoragePath(realpath(__DIR__ . '/storageFolder/'));

    $config = [

            'extension' =>  '.project',

            'files' =>  [
                        'users' => ['dni(int#)', 'name', 'lastName']
                        ]
            ];

    $papyrus->loadConfigurations($config);

## Creación de archivos de almacenamiento

Como ejemplo, será creado un archivo con el nombre **registers** y la extensión .data:

    $ touch ruta/a/carpetaDeAlmacenamiento/registers.data

Listamos en la consola:

	$ ls -l
    -rwx---rw- 1 user user 494 jul  8 21:10 registers.data

Puedes agregar los archivos que consideres necesarios.

## Permisos (Sólo para Linux)

A los archivos que fueron creados, se les debe otorgar permisos (ruta/a/carpetaDeAlmacenamiento/): 

    chmod 706 fileName.extension

Ejemplo:
    
    chmod 706 registers.data

## Práctica con ejemplos

Para propósitos de explicación con ejemplos, se asume que hay un archivo previamente creado llamado **users**, con la siguiente estructura:

    'files' => [
		'users' => ['dni(int#)', 'name', 'lastName']
		]

El campo dni(int#), indica que es llave primaria y de tipo entero único.

Incluye el archivo de autocarga  de Composer y usa el espacio de nombres de Papyrus:

    require 'vendor/autoload.php';

    use \Barbosa\Papyrus\Papyrus;

### Inserciones


    $values = ['dni' => 123456, 'name' => 'Tony', 'lastName' => 'Stark'];

    $papyrus = new Papyrus();
    $papyrus->insertInto('users')->values($values)->runQuery();


### Selecciones

El método where puede únicamente recibir un valor de tipo arreglo, el cual debería ser la llave primaria o identificador.

##### Seleccionar todo el contenido de un archivo con todos los campos.


    $papyrus->select()->from('users')->runQuery();


##### Seleccionar todo el contenido de un archivo con algunos campos.


    $papyrus->select('dni, name')->from('users')->runQuery();


##### Seleccionar un registro con todos los campos.


    $papyrus->select()->from('users')->where(['dni' => 123456])->runQuery();


##### Seleccionar un registro con algunos los campos.


    $papyrus->select('dni, name')->from('users')->where(['dni' => 123456])->runQuery();

 
##### Seleccionar registros sin la clausula where, ordenado por un campo con valor ASC (Ascendente) ó DESC (Descendente).


    $papyrus->select()->from('users')->orderBy(['name' => 'ASC'])->runQuery();

    $papyrus->select('dni, lastName')->from('users')->orderBy(['lastName' => 'ASC'])->runQuery();


##### Seleccionar registros sin la clausula where pero aplicando un límite.


    $papyrus->select()->from('users')->limit(3)->runQuery();

    $papyrus->select()->from('users')->limit(3)->orderBy(['name' => 'DESC'])->runQuery();


### Borrar

##### Borrar todos los registros


    $papyrus->deleteFrom('users')->runQuery();


##### Borrar un registro


    $papyrus->deleteFrom('users')->where(['dni' => 123456])->runQuery();


### Actualizar

La llave primaria de un registro no puede ser modificada. 

##### Actualizar todos los registros.


    $data = ['name' => 'Tony', 'lastname' => 'Stark'];

    $papyrus->update('users')->set($data)->runQuery();


##### Actualizar un registro.


    $data = ['name' => 'Tony', 'lastName' => 'The Iron Man'];

    $papyrus->update('users')->set($data)->where(['dni' => 123456])->runQuery();


### Obtener los datos de una consulta.


    $papyrus->getRecords();


### Obtener el estado de una consulta.


    $papyrus->getStatus();


## Métodos disponibles


    Papyrus::select($fields);
    Papyrus::deleteFrom($file = '');
    Papyrus::update($file = '');            
    Papyrus::from($file = '');
    Papyrus::insertInto($file = '');
    Papyrus::set(array $data = null);
    Papyrus::values(array $data = null);
    Papyrus::where(array $conditions = null);
    Papyrus::orderBy(array $order = null);
    Papyrus::limit($limit = 0);
    Papyrus::runQuery();
    Papyrus::getRecords();
    Papyrus::getStatus();
    $papyrus->loadConfigurations(array $config = null);
    $papyrus->setStoragePath($path = '');


## Contribuye
1. Verifica los problemas abiertos o abre un nuevo problema para iniciar una discusión en torno a un error de software o característica. 
2. Crea una bifurcación del repositorio en Github para realizar cambios.
3. Escribe una o más pruebas para una nueva característica o que muestre un error de software. 
4. Realiza cambios en el código para implementar la nueva característica o para reparar el error de software.
5. Envía una petición para que tus cambios sean aceptados, combinados y publicados.

Gracias...

### [Omar Andrés Barbosa](http://omarbarbosa.com)