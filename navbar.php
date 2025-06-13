<!-- Sidebar -->
<div class="fixed inset-y-0 left-0 w-64 text-white flex flex-col items-center z-50 shadow-2xl"
     style="background-color: rgba(75, 0, 0, 0.7);">
    <!-- Center all content vertically -->
    <div class="flex-1 flex flex-col items-center justify-center space-y-10 w-full">
        <!-- Header / Branding -->
        <div class="flex flex-col items-center space-y-2">
            <img src="./img/l1.png" alt="Logo" class="h-20 w-20 object-contain rounded-full shadow-lg border-2 border-white">
            <h1 class="text-lg font-semibold tracking-wide" style="color: #FFD700;">CPE Automated Door</h1>
        </div>

        <!-- Navigation Links -->
        <nav class="flex flex-col space-y-4 w-full px-6">
            <a href="dashboard.php" class="block text-center py-2 rounded-lg hover:bg-green-600 hover:scale-105 transition transform duration-200 shadow" style="background-color: #330000;">
                Dashboard
            </a>
            <a href="report.php" class="block text-center py-2 rounded-lg hover:bg-green-600 hover:scale-105 transition transform duration-200 shadow" style="background-color: #330000;">
                Report
            </a>
            <a href="attendance.php" class="block text-center py-2 rounded-lg hover:bg-green-600 hover:scale-105 transition transform duration-200 shadow" style="background-color: #330000;">
                Attendance
            </a>

            <?php if (isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'mmsudev@gmail.com'): ?>
            <a href="adduser.php" class="block text-center py-2 rounded-lg hover:bg-green-600 hover:scale-105 transition transform duration-200 shadow" style="background-color: #330000;">
                Instructor
            </a>
            <?php endif; ?>

            <a href="addstudent.php" class="block text-center py-2 rounded-lg hover:bg-green-600 hover:scale-105 transition transform duration-200 shadow" style="background-color: #330000;">
                Student
            </a>
            <a href="logout.php" class="block text-center py-2 rounded-lg hover:bg-red-600 hover:scale-105 transition transform duration-200 shadow" style="background-color: #330000;">
                Logout
            </a>
        </nav>
    </div>
</div>
