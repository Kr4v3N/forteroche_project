<?php

namespace Controller;

use Model\User;
use Model\UserManager;

/**
 * Class UserController
 *
 * @package \Controller
 */
class UserController extends AbstractController
{
    /**
     * UserController constructor.
     */
    public function __construct()
    {
        parent:: __construct();
        if ($_SERVER['REQUEST_URI'] != '/login' && ($_SERVER['REQUEST_URI'] != '/logout')) {
            $this->verifyUser();
        }
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function suscribeUser()
    {
        $errorRegister = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            //call the manager
            $userManager = new UserManager($this->getPdo());

            if (!preg_match("/^[a-zA-Zéèêëïöôùçâû]*$/",$_POST['lastname']))
            {
                $errorRegister['lastname'] = 'Seul les lettres et espaces sont autorisés.' ;
            }
            if (strlen($_POST['lastname']) < 2 || strlen($_POST['lastname']) > 15)
            {
                $errorRegister['lastname'] = 'Le nom doit comporter entre 2 et 15 caractères';
            }
            if (!preg_match("/^[a-zA-Zéèêëïöôùçâû]*$/",$_POST['firstname']))
            {
                $errorRegister['firstname'] = 'Le prénom doit comporter seulement des lettres et espaces.';
            }
            if (strlen($_POST['firstname']) < 2 || strlen($_POST['firstname']) > 15)
            {
                $errorRegister['firstname'] = 'Le prénom doit comporter entre 2 et 15 caractères';
            }
            if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $_POST['email']))
            {
                $errorRegister['email'] = 'Mauvais format de votre adresse email';
            }
            //verifies that the email we send is not in the database
            if ($userManager->existUser($_POST['email']))
            {
                $errorRegister['email'] = "L'adresse email est déja utilisé.";
            }
            if (strlen($_POST['password']) < 8 )
            {
                $errorRegister['password'] = 'Le mot de passe doit comporter au minimum 8 caractères';
            }
            if ($_POST['password'] !== ($_POST['password_control']))
            {
                $errorRegister['password'] = 'Les mots de passe saisis ne sont pas identiques.';
            }
            if (empty($errorRegister))
            {
                $newUser = new User;
                $newUser->setLastname($_POST['lastname']);
                $newUser->setFirstname($_POST['firstname']);
                $newUser->setEmail($_POST['email']);
                $newUser->setPass($_POST['password']);
                $id = $userManager->suscribe($newUser);
                header('Location: /login');
            }
        }
        return $this->twig->render('signUp.html.twig', [
            'errorRegister' => $errorRegister,
            'post' =>$_POST]);
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function logUser()
    {

        //if user is connected
        if (isset($_SESSION['user'])) {
            header('Location: /');
            exit();
        }

        $errorLoginUser = null;

        if (!empty($_POST)) {

            //call the manager
            $auth = new UserManager($this->getPdo());
            $user = $auth->loginUser($_POST['email']);

            if ($user) {
                if (password_verify($_POST['password'], $user->getPass())) {
                    //Si password ok, creation session user avec lastname, firstname, et email.
                    $_SESSION['user'] = [
                        'id' => $user->getId(),
                        'lastname' => $user->getlastname(),
                        'firstname' => $user->getFirstname(),
                        'email' => $user->getEmail(),
                        'message' => 'Vous êtes connecté'
                    ];

                    header('Location: /');

                } else {
                    $errorLoginUser = 'Identifiants incorrects ';

                }
            } else {
                $errorLoginUser = 'Identifiants incorrects';
            }
        }
        return $this->twig->render('loginUser.html.twig', [
            'errorLoginUser' => $errorLoginUser]);
    }

    /**
     * logout user
     */
    public function logoutUser()
    {
        session_destroy();
        header('Location: /');
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function error()
    {
        return $this->twig->render('Users/error.html.twig');
    }

}

