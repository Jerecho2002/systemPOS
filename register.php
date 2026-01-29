<?php
  include "database/database.php";
  $database->register();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory System - Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 flex justify-center items-center min-h-screen">

  <div class="w-full max-w-md bg-white shadow-lg rounded-2xl p-8">
    <!-- Logo + Title -->
    <div class="text-center mb-6">
      <div class="flex justify-center space-x-2 mb-3">
        <div class="w-12 h-12 flex items-center justify-center bg-black rounded-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9h14m-9-9v9m4-9v9" />
          </svg>
        </div>
        <div class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 7l9-4 9 4-9 4-9-4zm0 6l9 4 9-4M3 19l9 4 9-4" />
          </svg>
        </div>
      </div>
      <h1 class="text-2xl font-bold text-gray-800">POS & Inventory System</h1>
      <p class="text-gray-500 text-sm">Register to access your dashboard</p>
    </div>

    <!-- Register Card -->
    <div class="bg-white border rounded-xl p-6">
      <h2 class="text-lg font-semibold text-gray-800">Create Account</h2>
      <p class="text-sm text-gray-500 mb-4">Enter your credentials to access the system</p>
      <?php
      if(isset($_SESSION['register-error'])){
        $error = htmlspecialchars($_SESSION['register-error']);
        echo "<p class='text-red-500 text-sm mt-3'>{$error}</p>";
        unset($_SESSION['register-error']);
      }
      if(isset($_SESSION['register-success'])){
        $success = htmlspecialchars($_SESSION['register-success']);
        echo "<p class='text-green-500 text-sm mt-3'>{$success}</p>";
        unset($_SESSION['register-success']);
      }
      ?>

      <form class="space-y-4" method="POST">
        <input type="hidden" name="role" value="staff">
        <div>
          <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
          <input type="text" id="username" name="username" placeholder="Enter your username" required
            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required
            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <button type="submit" name="register" class="w-full bg-black text-white py-2 rounded-lg hover:bg-gray-900 transition">
          Register
        </button>
        <p class="text-sm text-gray-600 text-center mt-4">Account already exists? <a href="index.php" class="text-indigo-600 hover:underline font-medium">Login</a></p>
      </form>
    </div>
  </div>

</body>

</html>