<?php
include "database/database.php";
$database->createPcBuilder();
$cpu = $database->getItemsByCategoryName("CPU");
$gpu = $database->getItemsByCategoryName("GPU");
$ram = $database->getItemsByCategoryName("RAM");
$motherboard = $database->getItemsByCategoryName("Motherboard");
$storage = $database->getItemsByCategoryName("Storage");
$psu = $database->getItemsByCategoryName("PSU");
$case = $database->getItemsByCategoryName("Case");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Custom PC Builder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="assets/tailwind.min.css" rel="stylesheet" />
</head>

<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar Toggle Button -->
    <button id="sidebar-toggle" class="md:hidden p-3 fixed top-4 left-4 z-60 text-white rounded shadow-lg"
        style="background-color: rgba(170, 170, 170, 0.82);">
        ☰
    </button>

    <!-- Sidebar -->
    <?php include "sidebar.php"; ?>

    <main class="flex-1 ml-0 md:ml-64 p-6 flex gap-6">

        <!-- PC Builder Form: Left Column -->
        <section class="bg-white rounded-xl shadow p-6 w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6">🖥️ Custom PC Builder</h2>
            <?php
            if (isset($_SESSION['create-success'])) {
                $success = $_SESSION['create-success'];
                ?>
                <div id="successAlert"
                    class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 text-sm rounded-lg">
                    <?php echo $success ?>
                </div>
                <?php
            } ?>

            <?php
            if (isset($_SESSION['create-error'])) {
                $error = $_SESSION['create-error'];
                ?>
                <div id="errorAlert"
                    class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 text-sm rounded-lg">
                    <?php echo $error ?>
                </div>
                <?php
            } ?>
            <form id="pc-builder-form" method="POST" action="<?= $_SERVER['PHP_SELF']; ?>" class="space-y-4">

                <!-- PC Build Name -->
                <div>
                    <label for="pc_builder_name" class="block text-sm font-medium text-gray-700 mb-1">Build Name</label>
                    <input type="text" name="pc_builder_name" id="pc_builder_name" placeholder="Enter build name"
                        class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- CPU -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select CPU</label>
                    <select name="CPU"
                        class="component-select w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Choose a CPU --</option>
                        <?php foreach ($cpu as $item): ?>
                            <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- GPU -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select GPU</label>
                    <select name="GPU"
                        class="component-select w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Choose a GPU --</option>
                        <?php foreach ($gpu as $item): ?>
                            <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- RAM -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select RAM</label>
                    <select name="RAM"
                        class="component-select w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Choose RAM --</option>
                        <?php foreach ($ram as $item): ?>
                            <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Motherboard -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Motherboard</label>
                    <select name="Motherboard"
                        class="component-select w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Choose a Motherboard --</option>
                        <?php foreach ($motherboard as $item): ?>
                            <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Storage -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Storage</label>
                    <select name="Storage"
                        class="component-select w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Choose Storage --</option>
                        <?php foreach ($storage as $item): ?>
                            <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- PSU -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Power Supply</label>
                    <select name="PSU"
                        class="component-select w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Choose PSU --</option>
                        <?php foreach ($psu as $item): ?>
                            <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Case -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Case</label>
                    <select name="Case"
                        class="component-select w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Choose Case --</option>
                        <?php foreach ($case as $item): ?>
                            <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" name="pc-build-btn"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                        💾 Save Build
                    </button>
                </div>
            </form>
        </section>

        <!-- Build Preview: Right Column -->
        <section class="bg-white rounded-xl shadow p-6 flex-1 min-w-[300px] max-w-lg">
            <h3 class="text-xl font-semibold mb-4">🧩 Build Preview</h3>
            <ul id="build-preview" class="space-y-3 text-gray-700 text-sm">
                <li><strong>Build Name:</strong> <span data-part="pc_builder_name">--</span></li>
                <li><strong>CPU:</strong> <span data-part="CPU">--</span></li>
                <li><strong>GPU:</strong> <span data-part="GPU">--</span></li>
                <li><strong>RAM:</strong> <span data-part="RAM">--</span></li>
                <li><strong>Motherboard:</strong> <span data-part="Motherboard">--</span></li>
                <li><strong>Storage:</strong> <span data-part="Storage">--</span></li>
                <li><strong>PSU:</strong> <span data-part="PSU">--</span></li>
                <li><strong>Case:</strong> <span data-part="Case">--</span></li>
            </ul>
        </section>

    </main>

    <!-- BugerBar Toggle -->
    <script>
        const sidebar = document.getElementById('mobile-sidebar');
        const toggleBtn = document.getElementById('sidebar-toggle');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });
    </script>

    <!-- BugerBar Close -->
    <script>
        const closeBtn = document.getElementById('sidebar-close');

        closeBtn.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full'); // close sidebar
        });
    </script>

    <!-- Save scroll before reload/submit -->
    <script>
        window.addEventListener('beforeunload', () => {
            sessionStorage.setItem('scrollPos', window.scrollY);
        });

        // Restore scroll on load
        window.addEventListener('load', () => {
            const scrollPos = sessionStorage.getItem('scrollPos');
            if (scrollPos) {
                window.scrollTo(0, parseInt(scrollPos));
                sessionStorage.removeItem('scrollPos');
            }
        });
    </script>

    <!-- Unset Alert -->
    <script>
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.display = 'none';
                fetch('unset_alert.php');
            }, 3000);
        }

        const errorAlert = document.getElementById('errorAlert');
        if (errorAlert) {
            setTimeout(() => {
                errorAlert.style.display = 'none';
                fetch('unset_alert.php');
            }, 3000);
        }
    </script>
    <script>
        // Update preview on select changes
        const selects = document.querySelectorAll(".component-select");
        selects.forEach(select => {
            select.addEventListener("change", (e) => {
                const name = e.target.name;
                const selectedOption = e.target.options[e.target.selectedIndex];
                const value = selectedOption.textContent || "--";
                const previewElement = document.querySelector(`[data-part="${name}"]`);
                if (previewElement) previewElement.textContent = value;
            });
        });

        // Update preview on PC Build Name input
        const buildNameInput = document.getElementById("pc_builder_name");
        buildNameInput.addEventListener("input", (e) => {
            const previewElement = document.querySelector(`[data-part="pc_builder_name"]`);
            previewElement.textContent = e.target.value || "--";
        });
    </script>

</body>

</html>