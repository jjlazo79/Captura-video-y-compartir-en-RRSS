<?php 
// Gugamos con los datos (si los hay)
if ( isset( $_POST ) ) {
	// Extraemos los valores de la global POST
	$nombre 		= $_POST['nombre'];
	$apellidos 	= $_POST['apellidos'];
	$email 			= $_POST['email'];
	$telefono 	= $_POST['telefono'];
	$hora 			= date('l jS \of F Y h:i:s A');

	// Valores de la DDBB
	$servername = "localhost";
	$username 	= "root";
	$password 	= "";
	$dbname 		= "leadsinesrosales";

	// Creamos la conexión
	$conn = new mysqli( $servername, $username, $password, $dbname );
	// Comprobamos la conexión
	if ( $conn->connect_error ) {
	    die("Falló la conexión por: " . $conn->connect_error );
	} 

	// Realizamos la consulta
	$sql = "INSERT INTO leadsinesrosales (hora, nombre, apellidos, email, telefono)
	VALUES ( '$hora', '$nombre', '$apellidos', '$email', '$telefono')";

	if ( $conn->query($sql) === TRUE ) {
		echo "<h1>Datos guardados correctamente</h1>";
	  echo "<br>";
	  echo "<p>En breve será redirigido y podrá gravar su vídeo</p>";
	} else {
	    echo "Error: " . $sql . "<br>" . $conn->error;
	}

	$conn->close();
}

// Redirigimos a la página del vídeo video.html
header( "refresh:2; url=video.php?nombre=$nombre" ); 
die();
 ?>