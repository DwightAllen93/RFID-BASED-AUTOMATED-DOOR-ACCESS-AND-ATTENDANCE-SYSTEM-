<?php
// Start session
session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    // If logged in, redirect to dashboard.php
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <link rel="icon" type="image/x-icon" href="./img/l.png" />
  
  <style>
    /* Button hover animation to make them bigger */
    button, a.button {
        transition: transform 0.3s ease, background-color 0.3s ease;
    }

    button:hover, a.button:hover {
        transform: scale(1.1); /* Makes the button bigger */
        background-color: #3A0000; /* Darkens the background on hover */
    }

    /* Fade-in effect for page content */
    @keyframes fadeInSimple {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    body {
      animation: fadeInSimple 1s ease forwards;
    }
  </style>
  
  <script src="./css/3.4.16"></script>
</head>

<body class="bg-gray-100 font-sans relative">

  <nav class="fixed top-0 w-full bg-[#3A0000] text-white py-3 px-6 flex justify-between items-center z-50">
    <div class="flex items-center">
      <img src="./img/l1.png" alt="Logo" class="h-10 mr-3" />
      <span class="text-lg text-[#FFD700]">CPE Automated Door</span>
    </div>

    <ul class="hidden md:flex space-x-6 text-[#FFD700]">
      <li><a href="index.php" class="hover:text-[#FFD700]">Home</a></li>
      <li><a href="img/thesis.docx" download class="hover:text-[#FFD700]">Documentation</a></li>
      <li><a href="aboutus.php" class="hover:text-[#FFD700]">About</a></li>
    </ul>

    <button id="menu-toggle" class="md:hidden text-white focus:outline-none" aria-label="Toggle menu">
     
    </button>
  </nav>

  <!-- Mobile Menu -->
  <ul id="mobile-menu" class="hidden absolute top-14 left-0 w-full bg-gray-900 text-white flex flex-col space-y-4 px-6 py-4 md:hidden z-50">
    <li><a href="index.php" class="hover:text-green-400">Home</a></li>
    <li><a href="img/thesis.docx" download class="hover:text-green-400">Documentation</a></li>
    <li><a href="aboutus.php" class="hover:text-green-400">About</a></li>
  </ul>

  <!-- Background Animation (Particles.js) -->
  <div class="absolute inset-0 -z-20 bg-cover bg-center" style="background-image: url('./img/Mariano-Marcos-State-University-MMSU.jpg');"></div>
  <div class="absolute inset-0 -z-15 bg-black opacity-70"></div>

  <!-- Login Form -->
  <div class="relative z-10 flex flex-col items-center justify-center h-screen text-center px-6">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md mt-10">
      <div class="text-center mb-6">
        <img src="./img/l1.png" alt="Logo" class="h-16 mx-auto mb-2" />
        <h1 class="text-2xl font-bold text-gray-800">Login</h1>
      </div>

      <!-- Display error message if any -->
      <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-200 text-red-800 p-3 rounded mb-4 text-center">
          <?php echo $_SESSION['error_message']; ?>
          <?php unset($_SESSION['error_message']); ?>
        </div>
      <?php endif; ?>

      <form action="authenticate.php" method="POST" class="space-y-4">
        <!-- Email Address -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
          <input
            type="email"
            id="email"
            name="email"
            required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
          />
        </div>

        <!-- Password with show/hide toggle -->
        <div class="relative">
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 pr-10"
          />
          <button
            type="button"
            id="toggle-password"
            class="absolute top-[33px] bottom-auto right-0 px-3 flex items-center text-gray-500 hover:text-gray-700 focus:outline-none"
            aria-label="Toggle password visibility"
          >
            Show
          </button>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          class="w-full px-4 py-2 text-white bg-[#4B0000] rounded-md hover:bg-[#3A0000] transition a.button"
        >
          Login
        </button>
      </form>
    </div>
  </div>

  <script>
    // Mobile menu toggle
    document.getElementById('menu-toggle').addEventListener('click', () => {
      const menu = document.getElementById('mobile-menu');
      menu.classList.toggle('hidden');
    });

    // Password toggle show/hide
    const togglePasswordBtn = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');

    togglePasswordBtn.addEventListener('click', () => {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      togglePasswordBtn.textContent = type === 'password' ? 'Show' : 'Hide';
    });
  </script>

  <div class="absolute inset-0 -z-20 bg-cover bg-center"
    style="background-image: url('./img/Mariano-Marcos-State-University-MMSU.jpg')">
  </div>

</body>

</html>
