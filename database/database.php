<?php
session_start();
class Database{
    private $serverName = ("mysql:host=localhost;dbname=computer_store");
        private $userName = ("root");
        private $userPass = ("");
        private $fetchDefault = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
        protected $conn;

        public function conn(){
            try{
                $this->conn = new PDO($this->serverName, $this->userName, $this->userPass, $this->fetchDefault);
                return $this->conn;
            }catch(PDOException $e){
                echo "Error : " . $e->getMessage();
                exit;
            }
        }

        public function login_session(){
            if(!isset($_SESSION['login-success'])){
                header("Location: login.php");
            }
        }

        public function register(){
            $errors = [];
            if(isset($_POST['register'])){
                $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
                $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
                $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);        

                $query = $this->conn()->prepare("SELECT username FROM users WHERE username = ?");
                $query->execute([$username]);
                $check_username = $query->fetch();

                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                if(empty($username) || empty($password)){
                    $errors[] = "Do not leave the field empty";
                }else if(strlen($username) > 15){
                    $errors[] = "Username is too long, cannot be exceed to 15 characters";
                }else if(strlen($password) > 10){
                    $errors[] = "Password is too long, cannot be exceed to 10 characters";
                }else if(!preg_match("/^[a-zA-Z\s]+$/", $username)){
                    $errors[] = "Username cannot contain numbers";
                }else if(!preg_match("/^[a-zA-Z0-9\s]+$/", $password)){
                    $errors[] = "Password is invalid, contains numbers & letters only";
                }

                if($check_username){
                    $errors[] = "Username is already taken.";
                }

                if(!empty($errors)){
                        $_SESSION['register-error'] = implode("<br><br>",$errors);
                }else{
                    $sql = $this->conn()->prepare("INSERT INTO users (`username`, `password`, `role`) VALUES (?,?,?)");
                    $sql->execute([$username, $hashedPassword, $role]);
                    $_SESSION['register-success'] = "Successfully register " . $username . " you can now login";
                }
                }
            }

            public function login(){
            $errors = [];
            if(isset($_POST['login'])){
                $username = filter_input(INPUT_POST, 'username' , FILTER_SANITIZE_STRING);
                $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

                $sql = $this->conn()->prepare("SELECT password, role FROM users WHERE username = ?");
                $sql->execute([$username]);
                $user = $sql->fetch();
                    
                if($user){
                    if(password_verify($password, $user['password'])){
                        $_SESSION['login-success'] = $username;
                        $_SESSION['user-role'] = $user['role'];
                        header("Location: dashboard.php");
                    }else{
                        $errors[] = "Wrong password";
                    }
                }else{
                    $errors[] = "Wrong username";
                }

                if(empty($user) && empty($password)){
                    $errors[] = "Do not leave the field empty";
                }

                if (!empty($errors)) {
                    $_SESSION['login-error'] = implode("<br>",$errors);
                }
            }
        }
}
$database = new Database();