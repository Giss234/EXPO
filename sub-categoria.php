<?php
require_once('../../entities/dto/subcategoria.php');

// Se comprueba si existe una acción a realizar, de lo contrario se finaliza el script con un mensaje de error.
if (isset($_GET['action'])) {
    // Se crea una sesión o se reanuda la actual para poder utilizar variables de sesión en el script.
    session_start();
    // Se instancia la clase correspondiente.
    $subcategoria = new subCategoria;
    // Se declara e inicializa un arreglo para guardar el resultado que retorna la API.
    $result = array('status' => 0, 'message' => null, 'exception' => null, 'dataset' => null);
    // Se verifica si existe una sesión iniciada como administrador, de lo contrario se finaliza el script con un mensaje de error.
    if (isset($_SESSION['id_usuario']) or true) {
        // Se compara la acción a realizar cuando un administrador ha iniciado sesión.
        switch ($_GET['action']) {
            case 'readAll':
                if ($result['dataset'] = $subcategoria->readAll()) {
                    $result['status'] = 1;
                    $result['message'] = 'Existen '.count($result['dataset']).' registros';
                } elseif (Database::getException()) {
                    $result['exception'] = Database::getException();
                } else {
                    $result['exception'] = 'No hay datos registrados';
                }
                break;
                case 'readCategorias':
                    if ($result['dataset'] = $subcategoria->readCategorias()){
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
                } elseif ($result['dataset'] = $subcategoria->searchRows($_POST['search'])) {
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
                if (!$subcategoria->setNombre($_POST['nombre'])) {
                    $result['exception'] = 'Nombre incorrecto';
                } elseif (!$subcategoria->setDescripcion($_POST['descripcion'])) {
                    $result['exception'] = 'Descripción incorrecta';
                }elseif (!isset($_POST['categoria'])) {
                    $result['exception'] = 'Seleccione una categoría';
                }elseif (!$subcategoria->setCategoria($_POST['categoria'])) {
                    $result['exception'] = 'Categoría incorrecta'; 
                }elseif (!is_uploaded_file($_FILES['imagen']['tmp_name'])) {
                    $result['exception'] = 'Seleccione una imagen';
                } elseif (!$subcategoria->setImagen($_FILES['imagen'])) {
                    $result['exception'] = Validator::getFileError();
                } elseif ($subcategoria->createRow()) {
                    $result['status'] = 1;
                    if (Validator::saveFile($_FILES['imagen'], $subcategoria->getRuta(), $subcategoria->getImagen())) {
                        $result['message'] = 'subCategoría creada correctamente';
                    } else {
                        $result['message'] = 'subCategoría creada pero no se guardó la imagen';
                    }
                } else {
                    $result['exception'] = Database::getException();
                }
                break;
            case 'readOne':
                if (!$subcategoria->setId($_POST['id_sub_categoria'])) {
                    $result['exception'] = 'subCategoría incorrecta';
                } elseif ($result['dataset'] = $subcategoria->readOne()) {
                    $result['status'] = 1;
                } elseif (Database::getException()) {
                    $result['exception'] = Database::getException();
                } else {
                    $result['exception'] = 'subCategoría inexistente';
                }
                break;
            case 'update':
                $_POST = Validator::validateForm($_POST);
                if (!$subcategoria->setId($_POST['id'])) {
                    $result['exception'] = 'subCategoría incorrecta';
                } elseif (!$data = $subcategoria->readOne()) {
                    $result['exception'] = 'subCategoría inexistente';
                } elseif (!$subcategoria->setNombre($_POST['nombre'])) {
                    $result['exception'] = 'Nombre incorrecto';
                } elseif (!$subcategoria->setDescripcion($_POST['descripcion'])) {
                    $result['exception'] = 'Descripción incorrecta';
                } elseif (!$subcategoria->setCategoria($_POST['categoria'])) {
                    $result['exception'] = 'Seleccione una categoría';
                } elseif (!is_uploaded_file($_FILES['imagen']['tmp_name'])) {
                    if ($subcategoria->updateRow($data['imagen_sub_categoria'])) {
                        $result['status'] = 1;
                        $result['message'] = 'subCategoría modificada correctamente';
                    } else {
                        $result['exception'] = Database::getException();
                    }
                } elseif (!$subcategoria->setImagen($_FILES['imagen'])) {
                    $result['exception'] = Validator::getFileError();
                } elseif ($subcategoria->updateRow($data['imagen_sub_categoria'])) {
                    $result['status'] = 1;
                    if (Validator::saveFile($_FILES['imagen'], $subcategoria->getRuta(), $subcategoria->getImagen())) {
                        $result['message'] = 'subCategoría modificada correctamente';
                    } else {
                        $result['message'] = 'subCategoría modificada pero no se guardó la imagen';
                    }
                } else {
                    $result['exception'] = Database::getException();
                }
                break;
            case 'delete':
                if (!$subcategoria->setId($_POST['id_sub_categoria'])) {
                    $result['exception'] = 'subCategoría incorrecta';
                } elseif (!$data = $subcategoria->readOne()) {
                    $result['exception'] = 'subCategoría inexistente';
                } elseif ($subcategoria->deleteRow()) {
                    $result['status'] = 1;
                    if (Validator::deleteFile($subcategoria->getRuta(), $data['imagen_sub_categoria'])) {
                        $result['message'] = 'subCategoría eliminada correctamente';
                    } else {
                        $result['message'] = 'subCategoría eliminada pero no se borró la imagen';
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

