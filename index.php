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
  <title>Landing Page</title>
  <link rel="icon" type="image/x-icon" href="./img/l1.png" />
  <script src="./css/3.4.16"></script>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          keyframes: {
            popUp: {
              '0%': { opacity: '0', transform: 'scale(0.8)' },
              '100%': { opacity: '1', transform: 'scale(1)' },
            },
          },
          animation: {
            popUp: 'popUp 0.5s ease forwards',
          },
        },
      },
    }
  </script>

  <style>
    /* Button hover animation to make them bigger */
    button, a.button {
        transition: transform 0.3s ease, background-color 0.3s ease;
    }

    button:hover, a.button:hover {
        transform: scale(1.1); /* Makes the button bigger */
        background-color: #3A0000; /* Darkens the background on hover */
    }
  </style>
</head>

<body class="relative font-sans">

  <div class="absolute inset-0 -z-20 bg-cover bg-center"
    style="background-image: url('./img/Mariano-Marcos-State-University-MMSU.jpg')">
  </div>

  <div class="absolute inset-0 -z-15 bg-black opacity-70"></div>

  <!-- Navbar -->
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
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
    </button>
  </nav>

  <!-- Mobile Menu -->
  <ul id="mobile-menu" class="hidden absolute top-14 left-0 w-full bg-gray-900 text-white flex flex-col space-y-4 px-6 py-4 md:hidden z-50">
    <li><a href="#" class="hover:text-green-400">Home</a></li>
    <li><a href="#" class="hover:text-green-400">Documentation</a></li>
    <li><a href="#" class="hover:text-green-400">About</a></li>
  </ul>

  <!-- Content -->
  <div class="relative h-screen flex items-center justify-center px-6 text-center">
    <div class="absolute inset-0 bg-black opacity-50 z-5"></div> <!-- dark overlay -->
    <div class="relative z-10 flex flex-col items-center justify-center max-w-4xl">
      <!-- Logos with pop-up animation -->
      <div class="flex space-x-6 mb-6 animate-popUp">
        <img src="./img/mmsuLogo.png" alt="Logo 1" class="w-20 h-auto" />
        <img src="./img/COELOGO.png" alt="Logo 2" class="w-20 h-auto" />
      </div>

      <!-- Text initially hidden and pops in after delay -->
      <div class="opacity-0 scale-75" style="animation: popUp 0.5s ease forwards; animation-delay: 0.5s;">
        <h1 class="text-3xl md:text-5xl font-bold text-white mb-4">Enhancing Classroom Security with Automated Door Access</h1>
        <p class="text-white text-lg md:text-xl mb-6">
          A Thesis Research Study on Implementing RFID-based Automated Door Access for Computer Engineering Classrooms:
          Adopting Innovative Technology for Improved Security and Efficiency
        </p>
      </div>

      <div class="flex space-x-4">
        <!-- Add button class to the 'Get Started' link -->
        <a href="login.php" class="px-6 py-2 bg-[#4B0000] text-white rounded hover:bg-[#3A0000] transition a.button">Get Started</a>
        <a href="img/thesis.docx" download="MMSU_Automated_Door_Thesis.docx">
          <button class="px-6 py-2 bg-[#4B0000] text-white rounded hover:bg-[#3A0000] transition a.button">Documentation</button>
        </a>
      </div>
    </div>
  </div>

  <script src="./js/particles.min.js"></script>
  <script src="script.js"></script>

  <script>
    // Mobile menu toggle
    document.getElementById('menu-toggle').addEventListener('click', () => {
      const menu = document.getElementById('mobile-menu')
      menu.classList.toggle('hidden')
    })
  </script>

</body>

</html>
