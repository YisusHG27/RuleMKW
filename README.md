Hola jesu

Si vas a crear dinámicamente la cabecera con **generar_navbar.php** entonces bórrala del **index.php** bro
Y no sé por qué tienes en *db* un sql de la tabla de datos, pero bueno, he creado la base con ese sql y un usuario con los datos que slen en la conexión si no pilla la variable de entorno.

Ahora el registro lo pilla, pero no registra porque te redirige al index.html cuando tu index es .php xddddd
Igualmente creo que funciona porque lo he metido otra vez y me dice que ya estoy registrado.
Vale sí, funciona god.

En registro tienes que al primer usuario que se crea lo haces admin, yo lo quitaría ya por lo que sea, no vaya a ser.
***En el login tienes DOS index.html bro dile al copilot que te los cambie a php todos los index.html del proyecto...***
Y me ha iniciado sesión automáticamente. No sé si es lo que querías pero ahí lo tienes xdd

Ya unas cositas, el copyright lo tienes del 2024, pone TFG - Universidad (no estás en la universidad creo eh) y tienes los circuitos en una tabla diferente a las copas (lo cual está bien, pero tienes que hacer otra tabla para hacerle una relación 1-N). Hablando de los circuitos, no carga la imagen del Cañón Ferroviario. Tampoco se selecciona ninguno, no sé por qué.

Las cosas que te he dicho que están mal las he cambiado menos lo de borrar la cabecera del index.php y cambiar todos los index.html por index.php, en plan, no sé si quedan, he cambiado los que me jodían lo que intentaba hacer. Lo del copy y tfg universidad y la tabla circuitos-copa tampoco lo he hecho (no tengo tu BD así que no puedo tocarla obviamente), pero hazlo colegui venga saluditos chao.


*PD: cuando cambies conexion.php o arregles lo del getenv() mira la base de datos y ponle al usuario y a la BD lo que le pongas a esas variables.*
*PPD: lo que te he cambiado está en los commits, que he tenido que moverlos de la raíz a cada una de sus carpetas y para hacer eso tenía que hacer un commit para cada archivo.*
