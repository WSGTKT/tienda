<?php
// Configuración de la base de datos (simulada con un archivo JSON)
$db_file = 'db.json';

function get_data() {
    global $db_file;
    if (!file_exists($db_file)) {
        return ['users' => [], 'games' => [], 'developers' => []];
    }
    return json_decode(file_get_contents($db_file), true);
}

function save_data($data) {
    global $db_file;
    file_put_contents($db_file, json_encode($data, JSON_PRETTY_PRINT));
}

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

$response = ['success' => false, 'message' => 'Acción no válida.'];

switch ($action) {
    case 'register':
        $username = $input['username'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        $data = get_data();
        $user_exists = false;
        foreach ($data['users'] as $user) {
            if ($user['username'] === $username || $user['email'] === $email) {
                $user_exists = true;
                break;
            }
        }

        if ($user_exists) {
            $response = ['success' => false, 'message' => 'Usuario o correo ya registrado.'];
        } else {
            $data['users'][] = ['username' => $username, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT), 'role' => 'client'];
            save_data($data);
            $response = ['success' => true, 'message' => 'Registro exitoso. Ahora puedes iniciar sesión.'];
        }
        break;

    case 'login':
        $userInput = $input['userInput'] ?? '';
        $password = $input['password'] ?? '';

        $data = get_data();
        $user_found = false;
        foreach ($data['users'] as $user) {
            if (($user['username'] === $userInput || $user['email'] === $userInput) && password_verify($password, $user['password'])) {
                $user_found = true;
                $response = ['success' => true, 'message' => 'Login exitoso.', 'username' => $user['username'], 'role' => $user['role']];
                break;
            }
        }

        if (!$user_found) {
            $response = ['success' => false, 'message' => 'Usuario o contraseña incorrectos.'];
        }
        break;
    
    // --- CRUD DE USUARIOS (ADMIN) ---
    case 'get_users':
        $data = get_data();
        $response = ['success' => true, 'users' => $data['users']];
        break;

    case 'update_user':
        $oldUsername = $input['oldUsername'];
        $newUsername = $input['newUsername'];
        $newEmail = $input['newEmail'];
        
        $data = get_data();
        foreach ($data['users'] as &$user) {
            if ($user['username'] === $oldUsername) {
                $user['username'] = $newUsername;
                $user['email'] = $newEmail;
                break;
            }
        }
        save_data($data);
        $response = ['success' => true, 'message' => 'Usuario actualizado.'];
        break;
        
    case 'delete_user':
        $usernameToDelete = $input['username'];
        $data = get_data();
        $data['users'] = array_values(array_filter($data['users'], function($user) use ($usernameToDelete) {
            return $user['username'] !== $usernameToDelete;
        }));
        save_data($data);
        $response = ['success' => true, 'message' => 'Usuario eliminado.'];
        break;
        
    // --- CRUD DE JUEGOS (CURSOS) ---
    case 'get_games':
        $data = get_data();
        $response = ['success' => true, 'games' => $data['games']];
        break;

    case 'add_game':
        $game = $input['game'];
        $data = get_data();
        $data['games'][] = $game;
        save_data($data);
        $response = ['success' => true, 'message' => 'Juego agregado.'];
        break;
        
    case 'update_game':
        $oldTitle = $input['oldTitle'];
        $newGame = $input['newGame'];
        $data = get_data();
        foreach ($data['games'] as &$game) {
            if ($game['titulo'] === $oldTitle) {
                $game = $newGame;
                break;
            }
        }
        save_data($data);
        $response = ['success' => true, 'message' => 'Juego actualizado.'];
        break;
        
    case 'delete_game':
        $titleToDelete = $input['title'];
        $data = get_data();
        $data['games'] = array_values(array_filter($data['games'], function($game) use ($titleToDelete) {
            return $game['titulo'] !== $titleToDelete;
        }));
        save_data($data);
        $response = ['success' => true, 'message' => 'Juego eliminado.'];
        break;
    
    // --- CRUD DE DESARROLLADORES (MAESTROS) ---
    case 'get_developers':
        $data = get_data();
        $response = ['success' => true, 'developers' => $data['developers']];
        break;
    
    case 'add_developer':
        $developer = $input['developer'];
        $data = get_data();
        $data['developers'][] = $developer;
        save_data($data);
        $response = ['success' => true, 'message' => 'Desarrollador agregado.'];
        break;
        
    case 'update_developer':
        $oldName = $input['oldName'];
        $newDeveloper = $input['newDeveloper'];
        $data = get_data();
        foreach ($data['developers'] as &$dev) {
            if ($dev['nombre'] === $oldName) {
                $dev = $newDeveloper;
                break;
            }
        }
        save_data($data);
        $response = ['success' => true, 'message' => 'Desarrollador actualizado.'];
        break;
        
    case 'delete_developer':
        $nameToDelete = $input['name'];
        $data = get_data();
        $data['developers'] = array_values(array_filter($data['developers'], function($dev) use ($nameToDelete) {
            return $dev['nombre'] !== $nameToDelete;
        }));
        save_data($data);
        $response = ['success' => true, 'message' => 'Desarrollador eliminado.'];
        break;
}

{
    "users": [
        {
            "username": "admin",
            "email": "admin@tienda.com",
            "password": "$2y$10$T8VlQdM.N5b.i8T1qJ6uO.g.f/P.R4.V.b6B.1dG.W.9uW.Q/u.w/O",
            "role": "admin"
        }
    ],
    "games": [
        {
            "titulo": "Halo Infinite",
            "precio": 59.99,
            "imagen": "Imagenes/juego.jpg"
        },
        {
            "titulo": "The Witcher 3",
            "precio": 39.99,
            "imagen": "Imagenes/juego2.jpg"
        },
        {
            "titulo": "Minecraft",
            "precio": 29.99,
            "imagen": "Imagenes/juego3.jpg"
        }
    ],
    "developers": [
        {
            "nombre": "343 Industries",
            "juegos_asignados": ["Halo Infinite"]
        }
    ]
}

echo json_encode($response);