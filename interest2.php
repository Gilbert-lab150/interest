<?php
function calculateMonthlyPayment($loanAmount, $annualInterestRate, $loanTermMonths) {
    $monthlyInterestRate = $annualInterestRate / 100 / 12;
    $monthlyPayment = ($loanAmount * $monthlyInterestRate) / (1 - pow((1 + $monthlyInterestRate), -$loanTermMonths));
    return $monthlyPayment;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loanAmount = floatval($_POST['loan_amount']);
    $annualInterestRate = floatval($_POST['interest_rate']);
    $loanTerm = intval($_POST['loan_term']);
    $termType = $_POST['term_type'];

    // Validate loan amount and term
    if ($loanAmount < 0 || $loanAmount > 1000000000000000000) {
        $error_message = "Invalid loan amount!";
    } elseif ($loanTerm <= 0) {
        $error_message = "Invalid loan term!";
    } else {
        // Convert loan term to months
        $loanTermMonths = ($termType === 'years') ? $loanTerm * 12 : $loanTerm;

        // Calculate monthly payment
        $monthlyPayment = calculateMonthlyPayment($loanAmount, $annualInterestRate, $loanTermMonths);

        // Calculate total payment and total interest
        $totalCost = $monthlyPayment * $loanTermMonths;
        $totalInterest = $totalCost - $loanAmount;

        // Prepare results for display
        $results = [
            'monthly_payment' => number_format($monthlyPayment, 2),
            'total_interest' => number_format($totalInterest, 2),
            'total_cost' => number_format($totalCost, 2),
        ];

        // Display amortization schedule
        if (isset($_POST['show_schedule']) && $_POST['show_schedule'] == 'yes') {
            $schedule = [];
            $remainingBalance = $loanAmount;

            for ($paymentNumber = 1; $paymentNumber <= $loanTermMonths; $paymentNumber++) {
                $interestPaid = $remainingBalance * ($annualInterestRate / 100 / 12);
                $principalPaid = $monthlyPayment - $interestPaid;
                $remainingBalance -= $principalPaid;

                $schedule[] = [
                    'payment_number' => $paymentNumber,
                    'principal_paid' => number_format($principalPaid, 2),
                    'interest_paid' => number_format($interestPaid, 2),
                    'remaining_balance' => number_format($remainingBalance, 2),
                ];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Calculator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #333;
            text-align: center;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 0 auto;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input[type="number"], select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #28a745; /* Green color */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #218838; /* Darker green on hover */
        }
        .error {
            color: red;
            text-align: center;
        }
        .results {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center; /* Center the text */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Interest Loan Calculator</h1>

    <?php if (!empty($error_message)): ?>
        <div class="error"><?= $error_message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="loan_amount">Loan Amount (₱):</label>
        <input type="number" name="loan_amount" step="0.01" required>

        <label for="interest_rate">Annual Interest Rate (%):</label>
        <input type="number" name="interest_rate" step="0.01" required>

        <label for="loan_term">Loan Term:</label>
        <input type="number" name="loan_term" required>
        <select name="term_type" required>
            <option value="months">Months</option>
            <option value="years">Years</option>
        </select>

        <label for="show_schedule">Show Amortization Schedule?</label>
        <select name="show_schedule">
            <option value="no">No</option>
            <option value="yes">Yes</option>
        </select>

        <input type="submit" value="Calculate">
    </form>

    <?php if (isset($results)): ?>
        <div class="results">
            <h2>Loan Calculation Results</h2>
            <p>Monthly Payment: ₱<?= $results['monthly_payment']; ?></p>
            <p>Total Interest Paid: ₱<?= $results['total_interest']; ?></p>
            <p>Total Loan Cost: ₱<?= $results['total_cost']; ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($schedule)): ?>
        <h3>Amortization Schedule:</h3>
        <table>
            <tr>
                <th>Payment #</th>
                <th>Principal Paid</th>
                <th>Interest Paid</th>
                <th>Remaining Balance</th>
            </tr>
            <?php foreach ($schedule as $payment): ?>
                <tr>
                    <td><?= $payment['payment_number']; ?></td>
                    <td>₱<?= $payment['principal_paid']; ?></td>
                    <td>₱<?= $payment['interest_paid']; ?></td>
                    <td>₱<?= $payment['remaining_balance']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
