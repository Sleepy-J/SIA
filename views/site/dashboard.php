<?php

use yii\helpers\Html;

$this->title = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>
    <link rel="stylesheet" href="<?= Yii::getAlias('@web') ?>/css/dash.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <nav class="navbar">
            <ul>
                <li><a href="#transaction-entry" onclick="showPanel('transaction-entry')">Transaction Entry</a></li>
                <li><a href="#summary-history" onclick="showPanel('summary-history')">Summary & History</a></li>
                <li><a href="<?= Yii::$app->urlManager->createUrl(['site/logout']) ?>" class="logout-btn">Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-wrapper">
            <!-- Transaction Entry Panel -->
            <div id="transaction-entry" class="panel active">
                <h2>Transaction Entry</h2>
                <?= Html::beginForm(['site/add-transaction'], 'post') ?>
                    <label for="transaction-name">Transaction Name:</label>
                    <?= Html::input('text', 'transaction-name', '', [
                        'class' => 'input-box',
                        'id' => 'transaction-name',
                        'placeholder' => 'Enter transaction name',
                        'required' => true,
                    ]) ?>

                    <label for="category">Transaction Category:</label>
                    <?= Html::dropDownList('category', null, [
                        'income' => 'Income',
                        'expense' => 'Expense',
                        'savings' => 'Savings',
                    ], ['class' => 'input-box', 'id' => 'category', 'required' => true]) ?>

                    <label for="amount">Amount:</label>
                    <?= Html::input('text', 'amount', '', [
                        'class' => 'input-box',
                        'id' => 'amount',
                        'placeholder' => 'Enter amount',
                        'required' => true,
                    ]) ?>

                    <label for="description">Description:</label>
                    <?= Html::textarea('description', '', [
                        'class' => 'input-box',
                        'id' => 'description',
                        'placeholder' => 'Enter details (optional)',
                    ]) ?>

                    <?= Html::submitButton('Submit', ['class' => 'btn-submit']) ?>
                <?= Html::endForm() ?>
            </div>

            <!-- Summary and History Panel -->
            <div id="summary-history" class="panel">
                <div class="summary-history-panel">
                    <!-- Financial Summary -->
                    <div class="left-container">
                        <h2>Financial Summary</h2>
                        <div class="summary-cards">
                            <div class="card income">
                                <h3>Total Income</h3>
                                <p>
                                    <?= Html::encode(array_sum(array_map(function ($t) {
                                        return $t[1] === 'income' ? $t[2] : 0;
                                    }, $transactions))) ?>
                                </p>
                            </div>
                            <div class="card expenses">
                                <h3>Total Expenses</h3>
                                <p>
                                    <?= Html::encode(array_sum(array_map(function ($t) {
                                        return $t[1] === 'expense' ? $t[2] : 0;
                                    }, $transactions))) ?>
                                </p>
                            </div>
                            <div class="card balance">
                                <h3>Total Balance</h3>
                                <p>
                                    <?= Html::encode(array_sum(array_map(function ($t) {
                                        return $t[1] === 'income' ? $t[2] : 0;
                                    }, $transactions)) - 
                                        array_sum(array_map(function ($t) {
                                            return $t[1] === 'expense' ? $t[2] : 0;
                                        }, $transactions))) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction History -->
                    <div class="right-container">
                        <h2>Transaction History</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Transaction Name</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($transactions)): ?>
                                    <?php foreach ($transactions as $index => $transaction): ?>
                                        <tr>
                                            <td><?= Html::encode($transaction[4]) ?></td> <!-- Date -->
                                            <td><?= Html::encode($transaction[0]) ?></td> <!-- Transaction Name -->
                                            <td><?= Html::encode($transaction[1]) ?></td> <!-- Category -->
                                            <td><?= Html::encode($transaction[2]) ?></td> <!-- Amount -->
                                            <td>
                                                <button class="delete-btn" data-id="<?= $index ?>">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">No transactions found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showPanel(panelId) {
        // Hide all panels
        document.querySelectorAll('.panel').forEach(panel => {
            panel.classList.remove('active');
        });
        // Show the selected panel
        document.getElementById(panelId).classList.add('active');

        // Store the current active panel in localStorage
        localStorage.setItem('activePanel', panelId);
    }

    // Restore the active panel when the page loads
    document.addEventListener("DOMContentLoaded", function () {
        const activePanel = localStorage.getItem('activePanel') || 'transaction-entry';
        showPanel(activePanel);

        // Re-attach delete button functionality
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function () {
                const transactionId = this.getAttribute('data-id');

                if (!transactionId || isNaN(transactionId)) {
                    alert("Invalid transaction identifier.");
                    return;
                }

                if (confirm("Are you sure you want to delete this transaction?")) {
                    fetch('<?= Yii::$app->urlManager->createUrl(["site/delete-transaction"]) ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
                        },
                        body: JSON.stringify({ transactionIndex: parseInt(transactionId) })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                localStorage.setItem('activePanel', 'summary-history'); // Ensure it stays on history panel
                                location.reload();
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the transaction.');
                        });
                }
            });
        });
    });
</script>

</body>
</html>