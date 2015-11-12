<?php

require_once 'vendor/autoload.php';
require 'Model/NotORM.php';

use Slim\Slim;

// Register autoloader and instantiate Slim
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();


// Database Configuration
$dbhost = 'localhost';
$dbuser = 'homestead';
$dbpass = 'secret';
$dbname = 'naiemoji';
$dbmethod = 'mysql:dbname=';

$dsn = $dbmethod.$dbname;
$pdo = new PDO($dsn, $dbuser, $dbpass);
$db = new NotORM($pdo);

// Routes
$app->get('/', function () use ($app) {
    $app->response->setStatus(200);
    $app->render('../templates/homepage.html');
});

// Register a user
$app->post('/register', function () use ($app, $db) {
    $app->response()->header('Content-Type', 'application/json');
    $user = $app->request()->post();
    $result = $db->users->insert($user);
    echo json_encode(array('id' => $result['id']));
});

// Login a user
$app->post('/login', function () use ($app, $db) {
    $email = $app->request->post('email');
    // echo $email;
    $password = $app->request->post('password');
    if ($email === $db->users()->where('email', $email)->fetch('email')
        &&
        $password === $db->users->where('password', $password)->fetch('password')
        ) {
        $user = $db->users()->where('email', $email);
        if ($user->fetch()) {
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            $data = ['token' => $token];
            $result = $user->update($data);
            echo json_encode(array(
                'status' => true,
                'message' => 'You are now logged in',
            ));
        } else {
            echo json_encode(array(
                'status' => false,
                'message' => 'Please check your email and password combo',
            ));
        }
    }
});

// Login a user
$app->post('/logout', function () use ($app, $db) {
    $email = $app->request->post('email');
    $user = $db->users()->where('email', $email);
    if ($user->fetch()) {
        $token = '';
        $data = ['token' => $token];
        $result = $user->update($data);
        echo json_encode(array(
            'status' => true,
            'message' => 'You are now logged off',
        ));
    } else {
        echo json_encode(array(
            'status' => false,
            'message' => 'Please verify your email',
        ));
    }
});

// Get all emojis
$app->get('/emojis', function () use ($app, $db) {
    $emojis = array();
    foreach ($db->emojis() as $emoji) {
        $emojis[] = array(
            'id' => $emoji['id'],
            'name' => $emoji['name'],
            'keywords' => $emoji['keywords'],
            'emoji' => $emoji['emoji'],
            'category' => $emoji['category'],
            'created_at' => $emoji['created_at'],
            'updated_at' => $emoji['updated_at'],
            'user' => $emoji['user'],
        );
    }
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode($emojis, JSON_FORCE_OBJECT);
});

// Get a single emoji
$app->get('/emojis/:id', function ($id) use ($app, $db) {
    $app->response()->header('Content-Type', 'application/json');
    $emoji = $db->emojis()->where('id', $id);
    if ($data = $emoji->fetch()) {
        echo json_encode(array(
            'id' => $data['id'],
            'name' => $data['name'],
            'keywords' => $data['keywords'],
            'emoji' => $data['emoji'],
            'category' => $data['category'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
            'user' => $data['user'],
        ));
    } else {
        echo json_encode(array(
            'status' => false,
            'message' => "Emoji ID $id does not exist",
        ));
    }
});

// Add a new emoji
$app->post('/emoji/:name', function ($name) use ($app, $db) {
    $app->response()->header('Content-Type', 'application/json');
    $emoji = $app->request()->post();
    $user = $db->users()->where('name', $name);
    if ($user->fetch('token') === '') {
        echo json_encode(array(
            'status' => false,
            'message' => 'You have to be logged in to add an emoji',
        ));
    } else {
        $result = $db->emojis->insert($emoji);
        echo json_encode(array(
            'status' => true,
            'message' => 'You have created a new emoji',
        ));
        $status = $app->response->setStatus(201);
        return $status;
    }
});

// Update a emoji
$app->put('/emoji/:id/:name', function ($id, $name) use ($app, $db) {
    $app->response()->header('Content-Type', 'application/json');
    $emoji = $db->emojis()->where('id', $id);
    $user = $db->users()->where('name', $name);
    if ($user->fetch('token') === '') {
        echo json_encode(array(
            'status' => false,
            'message' => 'You have to be logged in to update the emoji',
        ));
    } else {
        if ($emoji->fetch()) {
            $post = $app->request()->put();
            $result = $emoji->update($post);
            echo json_encode(array(
                'status' => (bool) $result,
                'message' => 'Emoji updated successfully',
                ));
        } else {
            echo json_encode(array(
                'status' => false,
                'message' => "Emoji id $id does not exist",
            ));
        }
    }
});

// Update a emoji
$app->patch('/emoji/:id/:name', function ($id, $name) use ($app, $db) {
    $app->response()->header('Content-Type', 'application/json');
    $emoji = $db->emojis()->where('id', $id);
    $user = $db->users()->where('name', $name);
    if ($user->fetch('token') === '') {
        echo json_encode(array(
            'status' => false,
            'message' => 'You have to be logged in to update the emoji',
        ));
    } else {
        if ($emoji->fetch()) {
            $post = $app->request()->patch();
            $result = $emoji->update($post);
            echo json_encode(array(
                'status' => (bool) $result,
                'message' => 'Emoji updated successfully',
                ));
        } else {
            echo json_encode(array(
                'status' => false,
                'message' => "Emoji id $id does not exist",
            ));
        }
    }
});

// Remove a emoji
$app->delete('/emoji/:id/:name', function ($id, $name) use ($app, $db) {
    $app->response()->header('Content-Type', 'application/json');
    $emoji = $db->emojis()->where('id', $id);
    $user = $db->users()->where('name', $name);
    if ($user->fetch('token') === '') {
        echo json_encode(array(
            'status' => false,
            'message' => 'You have to be logged in to delete an emoji',
        ));
    } else {
        if ($emoji->fetch()) {
            $result = $emoji->delete();
            echo json_encode(array(
                'status' => true,
                'message' => 'Emoji deleted successfully',
            ));
        } else {
            echo json_encode(array(
                'status' => false,
                'message' => "Emoji id $id does not exist",
            ));
        }
    }
});

/* Run the application */
$app->run();
