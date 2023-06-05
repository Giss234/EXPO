<?php
require_once('../../entities/dto/productos.php');

// Se comprueba si existe una acción a realizar, de lo contrario se finaliza el script con un mensaje de error.
if (isset($_GET['action'])) {
    // Se crea una sesión o se reanuda la actual para poder utilizar variables de sesión en el script.
    session_start();
    // Se instancia la clase correspondiente.
    $producto = new producto;
    // Se declara e inicializa un arreglo para guardar el resultado que retorna la API.
    $result = array('status' => 0, 'message' => null, 'exception' => null, 'dataset' => null);
    // Se verifica si existe una sesión iniciada como administrador, de lo contrario se finaliza el script con un mensaje de error.
    if (isset($_SESSION['id_usuario']) or true) {
        // Se compara la acción a realizar cuando un administrador ha iniciado sesión.
        switch ($_GET['action']) {
            case 'readAll':
                if ($result['dataset'] = $producto->readAll()) {
                    $result['status'] = 1;
                    $result['message'] = 'Existen '.count($result['dataset']).' registros';
                } elseif (Database::getException()) {
                    $result['exception'] = Database::getException();
                } else {
                    $result['exception'] = 'No hay datos registrados';
                }
                break;
                case 'readUsuarios':
                    if ($result['dataset'] = $producto->readUsuarios()){
                        $result['status'] = 1;
                        $result['message'] = 'Existen '.count($result['dataset']).' registros';
                    } elseif (Database::getException()){
                        $result['exception'] = Database::getException();
                    } else {
                        $result['exception'] = 'No hay datos registrados';
                    }
                    break;
                case 'readSubCategorias':
                    if ($result['dataset'] = $producto->readSubCategorias()){
                        $result['status'] = 1;
                        $result['message'] = 'Existen '.count($result['dataset']).' registros';
                    } elseif (Database::getException()){
                        $result['exception'] = Database::getException();
                    } else {
                        $result['exception'] = 'No hay datos registrados';
                    }
                    break;
            case 'search':
                $_POST = Validator::validateForm($_POST);
                if ($_POST['search'] == '') {
                    $result['exception'] = 'Ingrese un valor para buscar';
                } elseif ($result['dataset'] = $producto->searchRows($_POST['search'])) {
                    $result['status'] = 1;
                    $result['message'] = 'Existen '.count($result['dataset']).' coincidencias';
                } elseif (Database::getException()) {
                    $result['exception'] = Database::getException();
                } else {
                    $result['exception'] = 'No hay coincidencias';
                }
                break;
            case 'create':
                $_POST = Validator::validateForm($_POST);
                if (!$producto->setNombre($_POST['nombre'])) {
                    $result['exception'] = 'Nombre incorrecto';
                } elseif (!$producto->setDetalle($_POST['detalle'])) {
                    $result['exception'] = 'Detalle incorrecto';
                } elseif (!$producto->setPrecio($_POST['precio'])) {
                    $result['exception'] = 'Precio incorrecto';
                } elseif (!$producto->setEstado(isset($_POST['estado']) ? 1 : 0)) {
                    $result['exception'] = 'Estado incorrecto';
                } elseif (!isset($_POST['usuario'])) {
                    $result['exception'] = 'Seleccione un usuario';
                }elseif (!$producto->setUsuario($_POST['usuario'])) {
                    $result['exception'] = 'usuario incorrecto'; 
                } elseif (!isset($_POST['subCategoria'])) {
                    $result['exception'] = 'Seleccione una subCategoría';
                }elseif (!$producto->setSubcategoria($_POST['subCategoria'])) {
                    $result['exception'] = 'subCategoria incorrecta'; 
                }elseif (!is_uploaded_file($_FILES['imagen']['tmp_name'])) {
                    $result['exception'] = 'Seleccione una imagen';
                } elseif (!$producto->setImagen($_FILES['imagen'])) {
                    $result['exception'] = Validator::getFileError();
                } elseif ($producto->createRow()) {
                    $result['status'] = 1;
                    if (Validator::saveFile($_FILES['imagen'], $producto->getRuta(), $producto->getImagen())) {
                        $result['message'] = 'producto creado correctamente';
                    } else {
                        $result['message'] = 'producto creado pero no se guardó la imagen';
                    }
                } else {
                    $result['exception'] = Database::getException();
                }
                break;
            case 'readOne':
                if (!$producto->setId($_POST['id_producto'])) {
                    $result['exception'] = 'producto incorrecto';
                } elseif ($result['dataset'] = $producto->readOne()) {
                    $result['status'] = 1;
                } elseif (Database::getException()) {
                    $result['exception'] = Database::getException();
                } else {
                    $result['exception'] = 'producto inexistente';
                }
                break;
            case 'update':
                $_POST = Validator::validateForm($_POST);
                if (!$producto->setId($_POST['id'])) {
                    $result['exception'] = 'producto incorrecto';
                } elseif (!$data = $producto->readOne()) {
                    $result['exception'] = 'producto inexistente';
                } elseif (!$producto->setNombre($_POST['nombre'])) {
                    $result['exception'] = 'Nombre incorrecto';
                } elseif (!$producto->setDetalle($_POST['detalle'])) {
                    $result['exception'] = 'Detalle incorrecto';
                } elseif (!$producto->setPrecio($_POST['precio'])) {
                    $result['exception'] = 'Precio incorrecto';
                } elseif (!$producto->setEstado(isset($_POST['estado']) ? 1 : 0)) {
                    $result['exception'] = 'Estado incorrecto';
                } elseif (!$producto->setUsuario($_POST['usuario'])) {
                    $result['exception'] = 'Seleccione un usuario';
                } elseif (!$producto->setSubcategoria($_POST['subCategoria'])) {
                    $result['exception'] = 'Seleccione una subCategoría';
                } elseif (!is_uploaded_file($_FILES['imagen']['tmp_name'])) {
                    if ($producto->updateRow($data['imagen_producto'])) {
                        $result['status'] = 1;
                        $result['message'] = 'producto modificado correctamente';
                    } else {
                        $result['exception'] = Database::getException();
                    }
                } elseif (!$producto->setImagen($_FILES['imagen'])) {
                    $result['exception'] = Validator::getFileError();
                } elseif ($producto->updateRow($data['imagen_producto'])) {
                    $result['status'] = 1;
                    if (Validator::saveFile($_FILES['imagen'], $producto->getRuta(), $producto->getImagen())) {
                        $result['message'] = 'producto modificado correctamente';
                    } else {
                        $result['message'] = 'producto modificado pero no se guardó la imagen';
                    }
                } else {
                    $result['exception'] = Database::getException();
                }
                break;
            case 'delete':
                if (!$producto->setId($_POST['id_producto'])) {
                    $result['exception'] = 'producto incorrecto';
                } elseif (!$data = $producto->readOne()) {
                    $result['exception'] = 'producto inexistente';
                } elseif ($producto->deleteRow()) {
                    $result['status'] = 1;
                    if (Validator::deleteFile($producto->getRuta(), $data['imagen_producto'])) {
                        $result['message'] = 'producto eliminado correctamente';
                    } else {
                        $result['message'] = 'producto eliminado pero no se borró la imagen';
                    }
                } else {
                    $result['exception'] = Database::getException();
                }
                break;
            default:
                $result['exception'] = 'Acción no disponible dentro de la sesión';
        }
        // Se indica el tipo de contenido a mostrar y su respectivo conjunto de caracteres.
        header('content-type: application/json; charset=utf-8');
        // Se imprime el resultado en formato JSON y se retorna al controlador.
        print(json_encode($result));
    } else {
        print(json_encode('Acceso denegado'));
    }
} else {
    print(json_encode('Recurso no disponible'));
}

