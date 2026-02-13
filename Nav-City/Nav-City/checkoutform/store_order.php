<!DOCTYPE html>
<html>
<head>
  <style>

    body {
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
      background-color: #fff;
      border: 1px solid #ddd;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    h1 {
      text-align: center;
    }

    p {
      margin: 5px 0;
    }


    .receipt-container {
      text-align: center;
      background-color: #fff;
      padding: 20px;
      border: 1px solid #ddd;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .receipt-heading {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 20px;
    }

    .receipt-details {
      text-align: left;
      margin-bottom: 20px;
    }

    .receipt-details p {
      margin: 5px 0;
    }

    .receipt-thankyou {
      text-align: center;
      font-weight: bold;
    }

    .receipt-footer {
      margin-top: 20px;
      text-align: center;
    }

    .print-button {
      display: block;
    }

    @media (max-width: 768px) {
      .container {
        padding: 10px;
      }

      .receipt-heading {
        font-size: 20px;
      }
    }

    @media (max-width: 480px) {
      .receipt-container {
        padding: 10px;
      }
    }

    @media print {
      .print-button {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="receipt-container">
      <h1 class="receipt-heading">Order Receipt</h1>
      <?php
      if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirm"])) {
          $name = trim($_POST["name"]); 
          $email = trim($_POST["email"]); 
          $address = trim($_POST["address"]); 
          $city = trim($_POST["city"]); 
          $postalCode = trim($_POST["postal_code"]); 
          $contactNumber = trim($_POST["contact_number"]); 
          $total = $_POST["total"];
         

          $item_id = $_POST["item_id"];
          $item_quantity = $_POST["item_quantity"];
          $item_subtotal = $_POST["item_subtotal"];

          $item_id_imploded = implode(", ", $item_id);
          $item_quantity_imploded = implode(", ", $item_quantity);
          $item_subtotal_imploded = implode(" + ", $item_subtotal);

          $item_length = count($item_id);

          $servername = "localhost";
          $username = "root";
          $password = "";
          $dbname = "order";

          $conn = new mysqli($servername, $username, $password, $dbname);

          if ($conn->connect_error) {
              die("Connection failed: " . $conn->connect_error);
          }

          $ticket = rand(1000000000, 9999999999);

          $sql = "INSERT INTO orders 
                  (name, email, address, city, postal_code, contact_number, total, ticket, created_at, item_id, item_quantity, item_subtotal)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)";

          $stmt = $conn->prepare($sql);

          $stmt->bind_param("ssssiisisss", 
              $name, 
              $email, 
              $address,
              $city,
              $postalCode,
              $contactNumber,
              $total, 
              $ticket,
              $item_id_imploded,
              $item_quantity_imploded,
              $item_subtotal_imploded
          );

          $stmt->execute();

          if ($stmt->affected_rows > 0) {
              echo "<div class=\"receipt-details\">";
              echo "<hr>";
              echo "<p>Order #: $ticket</p>";
              echo "<hr>";
              echo "<p>Name: $name</p>";
              echo "<p>Email: $email</p>";
              echo "<p>Address: $address</p>";
              echo "<p>City: $city</p>";
              echo "<p>Postal Code: $postalCode</p>";
              echo "<p>Contact Number: $contactNumber</p>";
              echo "<hr>";
              echo "<p>Items</p>";
              echo "<hr>";
              for($i=0; $i < $item_length; $i++ ){
                  echo "<p>Item ID: " . $item_id[$i] . "</p>";
                  echo "<p>Quantity: " . $item_quantity[$i] . "</p>";
                  echo "<p>Subtotal: " . $item_subtotal[$i] . "</p>";
                  echo "<hr>";
              }
              echo "<p>Total: $total</p>";
              echo "<hr>";
              echo "</div>";
          } else {
              echo "<div class=\"error-message\">Error placing order.</div>";
          }

          $stmt->close();
          $conn->close();
      }
      ?>
      <p class="receipt-thankyou">Thank you for shopping with us!</p>
    </div>
    <div class="receipt-footer">
      <p>Website: www.Navotas-City-Council`.com</p>
      <p>Email: Bspnavotascouncil@gmail.com</p>
      <p>Phone: (02) 8426-4987</p>
      <p class="print-button"><a href="javascript:window.print()">Print Receipt</a></p>
    </div>
  </div>
</body>
</html>