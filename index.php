<?php
session_start();

// Simulate login for demo (replace with real login logic)
if (!isset($_SESSION['logged_in'])) {
    $_SESSION['logged_in'] = true;
}

/// Load menu from menu.txt
$menu = [];
$menuFile = __DIR__ . '/menu.txt';
if (file_exists($menuFile)) {
    $lines = file($menuFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        list($id, $name, $price) = explode('|', $line);
        $menu[] = [
            'id' => (int)$id,
            'name' => $name,
            'price' => (float)$price
        ];
    }
} else {
    die("Menu file not found: $menuFile");
}

// -------------------
// Merge Sort Function (Sort menu by price ascending)
function mergeSortMenu($array) {
    if (count($array) <= 1) return $array;
    $middle = floor(count($array) / 2);
    $left = mergeSortMenu(array_slice($array, 0, $middle));
    $right = mergeSortMenu(array_slice($array, $middle));
    return merge($left, $right);
}
function merge($left, $right) {
    $result = [];
    while (!empty($left) && !empty($right)) {
        if ($left[0]['price'] <= $right[0]['price']) {
            $result[] = array_shift($left);
        } else {
            $result[] = array_shift($right);
        }
    }
    return array_merge($result, $left, $right);
}
$sortedMenu = mergeSortMenu($menu);

// -------------------
// Dynamic Programming Knapsack (Whole items)
// Returns array of items chosen
function dpKnapsack($items, $budget) {
    $n = count($items);
    $W = (int)round($budget * 100); // Convert to integer cents to avoid float issues

    // Values and weights as integer cents
    $weights = array_map(function($it) {
        return (int)round($it['price'] * 100);
    }, $items);
    $values = $weights; // Here value = price

    // DP table initialization
    $dp = array_fill(0, $n + 1, array_fill(0, $W + 1, 0));

    for ($i = 1; $i <= $n; $i++) {
        for ($w = 0; $w <= $W; $w++) {
            if ($weights[$i-1] <= $w) {
                $dp[$i][$w] = max(
                    $dp[$i-1][$w],
                    $dp[$i-1][$w - $weights[$i-1]] + $values[$i-1]
                );
            } else {
                $dp[$i][$w] = $dp[$i-1][$w];
            }
        }
    }

    // Backtracking to find chosen items
    $res = [];
    $w = $W;
    for ($i = $n; $i > 0 && $w >= 0; $i--) {
        if ($dp[$i][$w] != $dp[$i-1][$w]) {
            $res[] = $items[$i-1];
            $w -= $weights[$i-1];
        }
    }
    return array_reverse($res);
}

// -------------------
// Greedy Fractional Knapsack
// Returns array of ['name', 'price', 'portion']
function greedyFractionalKnapsack($items, $budget) {
    $sorted = $items;
    // Sort descending by price (value per weight = price/price = 1, but sort by price for fractional)
    usort($sorted, function($a, $b) {
        return $b['price'] <=> $a['price'];
    });
    $remBudget = $budget;
    $result = [];

    foreach ($sorted as $item) {
        if ($item['price'] <= $remBudget) {
            $result[] = ['name'=>$item['name'], 'price'=>$item['price'], 'portion'=>1.0];
            $remBudget -= $item['price'];
        } elseif ($remBudget > 0) {
            // Fractional portion
            $portion = $remBudget / $item['price'];
            $result[] = ['name'=>$item['name'], 'price'=>$remBudget, 'portion'=>$portion];
            $remBudget = 0;
            break;
        } else {
            break;
        }
    }
    return $result;
}

// -------------------
// Save orders to file (orders.txt)
function saveOrdersToFile($orders) {
    $lines = [];
    foreach ($orders as $order) {
        $lines[] = "{$order['name']}|{$order['price']}|{$order['quantity']}";
    }
    @file_put_contents(__DIR__ . '/orders.txt', implode(PHP_EOL, $lines));
}

function saveDPResultToFile($dpCombo) {
    $lines = [];
    foreach ($dpCombo as $item) {
        $lines[] = "{$item['name']}|{$item['price']}";
    }
    $path = __DIR__ . '/dp_result.txt';
    $result = file_put_contents($path, implode(PHP_EOL, $lines));
    if ($result === false) {
        echo "Failed to write dp_result.txt at $path";
    }
}

function saveGreedyResultToFile($greedyCombo) {
    $lines = [];
    foreach ($greedyCombo as $item) {
        $portionPercent = round($item['portion'] * 100, 2);
        $lines[] = "{$item['name']}|{$item['price']}|{$portionPercent}";
    }
    $path = __DIR__ . '/greedy_result.txt';
    $result = file_put_contents($path, implode(PHP_EOL, $lines));
    if ($result === false) {
        echo "Failed to write greedy_result.txt at $path";
    }
}


// -------------------
// Initialize orders session array
if (!isset($_SESSION['orders'])) {
    $_SESSION['orders'] = [];
}

// Handle Add Order
if (isset($_POST['add_order'])) {
    $id = (int)($_POST['food_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 1);
    if ($qty < 1) $qty = 1;

    foreach ($menu as $item) {
        if ($item['id'] === $id) {
            if (isset($_SESSION['orders'][$id])) {
                $_SESSION['orders'][$id]['quantity'] += $qty;
            } else {
                $_SESSION['orders'][$id] = [
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $qty
                ];
            }
            break;
        }
    }
    saveOrdersToFile($_SESSION['orders']);
    header('Location: index.php#yourOrders');
    exit;
}

// Clear orders
if (isset($_POST['clear_orders'])) {
    $_SESSION['orders'] = [];
    saveOrdersToFile([]);
    header('Location: index.php');
    exit;
}

// Initialize budget combos and inputs
$bestDynamicCombo = [];
$bestGreedyCombo = [];
$budgetDynamic = '';
$budgetGreedy = '';

// Handle DP budget check
if (isset($_POST['check_budget_dynamic'])) {
    $budgetDynamic = trim($_POST['budget_dynamic']);
    if (is_numeric($budgetDynamic) && $budgetDynamic > 0) {
        $bestDynamicCombo = dpKnapsack($menu, (float)$budgetDynamic);
        saveDPResultToFile($bestDynamicCombo);
    }
}

// Handle Greedy budget check
if (isset($_POST['check_budget_greedy'])) {
    $budgetGreedy = trim($_POST['budget_greedy']);
    if (is_numeric($budgetGreedy) && $budgetGreedy > 0) {
        $bestGreedyCombo = greedyFractionalKnapsack($menu, (float)$budgetGreedy);
        saveGreedyResultToFile($bestGreedyCombo);
    }
}

// Handle Finish & Exit
if (isset($_POST['finish_exit'])) {
    $lines = ["--- Order Summary ---", "Orders:"];
    $total = 0;
    foreach ($_SESSION['orders'] as $order) {
        $lines[] = "{$order['name']} x{$order['quantity']} = " . number_format($order['price'] * $order['quantity'], 2);
        $total += $order['price'] * $order['quantity'];
    }
    $lines[] = "Order Total: " . number_format($total, 2);

    if (!empty($bestDynamicCombo)) {
        $lines[] = "\nBest Combo (DP):";
        $sumDP = 0;
        foreach ($bestDynamicCombo as $item) {
            $lines[] = "{$item['name']} - " . number_format($item['price'], 2);
            $sumDP += $item['price'];
        }
        $lines[] = "Total = " . number_format($sumDP, 2);
    }

    if (!empty($bestGreedyCombo)) {
        $lines[] = "\nBest Combo (Greedy):";
        $sumGreedy = 0;
        foreach ($bestGreedyCombo as $item) {
            $lines[] = "{$item['name']} (" . round($item['portion'] * 100, 1) . "%) - " . number_format($item['price'], 2);
            $sumGreedy += $item['price'];
        }
        $lines[] = "Total = " . number_format($sumGreedy, 2);
    }

    $lines[] = "\nThank you for ordering!";

    $file = __DIR__ . "/summary.txt";
    $result = @file_put_contents($file, implode(PHP_EOL, $lines));
    if ($result === false) {
        die("Could not write to summary.txt");
    }

    // Clear session orders and files
    $_SESSION['orders'] = [];
    saveOrdersToFile([]);

    // Show thank you page & exit
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Thank You</title>
<style>
    body {font-family: Arial, sans-serif; background: #d4edda; display: flex; justify-content:center; align-items:center; height: 100vh; margin:0;}
    .box {background: white; padding: 30px 40px; border-radius: 12px; box-shadow: 0 0 20px rgba(0,0,0,0.1); text-align:center;}
    h2 {color: #155724;}
    a {text-decoration:none; font-weight:bold; color:#28a745;}
    a:hover {text-decoration:underline;}
</style>
</head>
<body>
<div class="box">
    <h2>Jazakallahu Khayran!</h2>
    <pre style="background:#eee; padding:15px; border-radius:8px; max-width: 800px; text-align: left;">
HTML;

$summaryFile = __DIR__ . '/summary.txt';
if (file_exists($summaryFile)) {
    echo htmlspecialchars(file_get_contents($summaryFile));
} else {
    echo "Summary file not found.";
}

echo <<<HTML
    </pre>
    <p>May Allah bless your efforts.</p>
    <p><a href="index.php">Back to Menu</a></p>
</div>
</body>
</html>
HTML;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Restaurant Management System</title>
<style>
    *{
        padding: 0;
        margin: 0;
    }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: url('images/bg-main.jpeg') no-repeat center center fixed;
        background-size: cover;
        margin: 0; padding: 0;
        color: #2E8B57;
    }
    .container {
        display: flex;
        justify-content: center;
        gap: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .sub-container {
        display: flex;
        flex-direction: column;
        width: 600px;
        height: 800px;
        overflow: auto;
        /* max-width: 800px; */
        background: rgba(255,255,255,0.95);
        /* margin: 20px auto; */
        padding: 25px 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    h1, h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #2E8B57;
        font-weight: 700;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    th, td {
        padding: 10px 12px;
        border-bottom: 1px solid #ccc;
        text-align: left;
    }
    th {
        background-color: #2E8B57;
        color: white;
        font-weight: 600;
    }
    input[type=number], select {
        width: 100%;
        padding: 8px 10px;
        margin: 8px 0 15px 0;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 16px;
        box-sizing: border-box;
    }
    input[type=submit], button {
        background-color: #2E8B57;
        color: white;
        border: none;
        padding: 12px 22px;
        margin-bottom: 10px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-weight: 600;
    }
    input[type=submit]:hover, button:hover {
        background-color: #246b45;
    }
    .section {
        margin-bottom: 40px;
    }
    .btn-danger {
        background-color: #b83232 !important;
    }
    .btn-center {
        text-align: center;
    }
    .title {
        align: center;
    }
    .menu-div {
        height: 800px;
        width: 800px;
        background: rgba(255,255,255,0.95);
        /* margin: 20px auto; */
        padding: 25px 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .menu-items {
        display:block;
        height: 700px;
        overflow:auto;
    }
    .menu-table thead, .menu-table tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    .nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: rgba(255,255,255,0.95);
        padding: 5px 30px;
        border-radius: 0px 0px 12px 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin-bottom: 25px
    }
    .logout-btn {
        border-radius: 12px;
        padding: 10px;
        font-weight: 600;
        color: white;
        text-decoration: none;
        background-color: #b83232 !important;
    }
    .add_order {
        font-size: 14px;
    }    
</style>
</head>
<body>
<div class="nav">
    <h1 class="title">Restaurant Management System</h1>
    <a class="logout-btn" href="welcome.php">Log Out</a>
</div>
<div class="container">

    <!-- Menu Table -->
    <div class="section menu-div">
        <h2>Menu</h2>
        <table class="menu-table">
            <thead>
                <tr><th>Food ID</th><th>Name</th><th>Price (৳)</th><th>Quantity</th><th>Action</th></tr>
            </thead>
            <tbody class="menu-items">
                <?php foreach ($menu as $item): ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= number_format($item['price'], 2) ?></td>
                    <form method="post">
                        <td><input type="number" name="quantity" min="1" value="1" required /></td>
                        <td>
                            <input type="hidden" name="food_id" value="<?= $item['id'] ?>" />
                            <button class="add_order" type="submit" name="add_order">Add to Order</button>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="sub-container">
    <!-- Display Orders -->
    <div class="section" id="yourOrders">
        <h2>Your Orders</h2>
        <?php if (empty($_SESSION['orders'])): ?>
            <p>No orders placed yet.</p>
        <?php else: ?>
            <form method="post" onsubmit="return confirm('Are you sure you want to clear all orders?');">
                <table>
                    <thead>
                        <tr><th>Name & Price (TK)</th><th>Quantity</th><th>Total (TK)</th></tr>
                    </thead>
                    <tbody>
                    <?php
                    $total = 0;
                    foreach ($_SESSION['orders'] as $order) {
                        $lineTotal = $order['price'] * $order['quantity'];
                        $total += $lineTotal;
                        echo "<tr><td>" . htmlspecialchars($order['name']) . " (TK " . number_format($order['price'], 2) . ")</td><td>{$order['quantity']}</td><td>" . number_format($lineTotal, 2) . "</td></tr>";
                    }
                    ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="2" style="text-align:right; font-weight:bold;">Grand Total:</td><td><strong><?= number_format($total, 2) ?></strong></td></tr>
                    </tfoot>
                </table>
                <input type="submit" name="clear_orders" value="Clear All Orders" class="btn-danger" />
            </form>
        <?php endif; ?>
    </div>

    <!-- DP Knapsack Combo -->
    <div class="section">
        <h2>Best Combo (Whole Items)</h2>
        <form method="post">
            <input type="number" step="0.01" name="budget_dynamic" placeholder="Enter your budget (TK)" required value="<?= htmlspecialchars($budgetDynamic) ?>" />
            <input type="submit" name="check_budget_dynamic" value="Show Combo" />
        </form>
        <?php if ($bestDynamicCombo): ?>
            <table>
                <thead><tr><th>Name</th><th>Price (TK)</th></tr></thead>
                <tbody>
                <?php
                $sum = 0;
                foreach ($bestDynamicCombo as $item) {
                    $sum += $item['price'];
                    echo "<tr><td>" . htmlspecialchars($item['name']) . "</td><td>" . number_format($item['price'], 2) . "</td></tr>";
                }
                ?>
                </tbody>
                <tfoot>
                    <tr><td><strong>Total</strong></td><td><strong><?= number_format($sum, 2) ?></strong></td></tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>

    <!-- Greedy Fractional Knapsack Combo -->
    <div class="section">
        <h2>Best Combo (Fractional Items)</h2>
        <form method="post">
            <input type="number" step="0.01" name="budget_greedy" placeholder="Enter your budget (TK)" required value="<?= htmlspecialchars($budgetGreedy) ?>" />
            <input type="submit" name="check_budget_greedy" value="Show Combo" />
        </form>
        <?php if ($bestGreedyCombo): ?>
            <table>
                <thead><tr><th>Name</th><th>Portion (%)</th><th>Price (TK)</th></tr></thead>
                <tbody>
                <?php
                $sumGreedy = 0;
                foreach ($bestGreedyCombo as $item) {
                    $portionPercent = round($item['portion'] * 100, 1);
                    $sumGreedy += $item['price'];
                    echo "<tr><td>" . htmlspecialchars($item['name']) . "</td><td>{$portionPercent}%</td><td>" . number_format($item['price'], 2) . "</td></tr>";
                }
                ?>
                </tbody>
                <tfoot>
                    <tr><td><strong>Total</strong></td><td></td><td><strong><?= number_format($sumGreedy, 2) ?></strong></td></tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>

    <!-- Finish & Exit -->
    <div class="section btn-center">
        <form method="post" onsubmit="return confirm('Are you sure you want to finish this order?');">
            <input type="submit" name="finish_exit" value="Finish Order" />
        </form>
    </div>
    </div>
</div>
</body>
</html>
