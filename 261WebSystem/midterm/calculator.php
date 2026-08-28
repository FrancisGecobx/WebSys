<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Math Calculator</title>
</head>
<body>

    <form action="" method="POST">
        <table border="1">
            <tr>
                <td>Enter Number 1</td>
                <td><input type="number" step="any" name="number1" placeholder="Enter number 1" required></td>
            </tr>
            <tr>
                <td>Enter Number 2</td>
                <td><input type="number" step="any" name="number2" placeholder="Enter number 2" required></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" name="submit" value="Calculate"></td>     
            </tr>
        </table>
    </form>

    <br>

<?php
    if (isset($_POST['submit'])) {
        $number1 = $_POST['number1'];	// variable for the 1st input
        $number2 = $_POST['number2']; 	// variable for the 2nd input
        $border = 1;

        $sum = $number1 + $number2;
        $difference = $number1 - $number2;
        $product = $number1 * $number2;


        if ($number2 != 0) {			// Prevent division by 0
            $quotient = $number1 / $number2;
            $modulo = $number1 % $number2;
        } else {
            $quotient = "Undefined (Cannot divide by 0)";
            $modulo = "Undefined (Cannot divide by 0)";
        }

        echo "<table border='$border'>
            <tr>
                <th>Operation</th>
                <th>Expression</th>
                <th>Result</th>
            </tr>
            <tr>
                <td>SUM</td>
                <td>$number1 + $number2</td>
                <td>$sum</td> 
            </tr>
            <tr>
                <td>DIFFERENCE</td>
                <td>$number1 - $number2</td>
                <td>$difference</td> 
            </tr>
            <tr>
                <td>PRODUCT</td>
                <td>$number1 * $number2</td>
                <td>$product</td> 
            </tr>
            <tr>
                <td>QUOTIENT</td>
                <td>$number1 / $number2</td>
                <td>$quotient</td> 
            </tr>
            <tr>
                <td>MODULO</td>
                <td>$number1 % $number2</td>
                <td>$modulo</td> 
            </tr>
        </table>";
    }
?>

</body>
</html>