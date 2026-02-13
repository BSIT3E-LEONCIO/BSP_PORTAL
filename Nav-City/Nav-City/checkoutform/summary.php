<!DOCTYPE html>
<html>
  <head>
    <title>Order Summary</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
      }

      .container {
        margin-top: 0;
        max-width: 600px;
        width: 100%;
        background-color: #fff;
        padding: 30px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        margin: 0 auto;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        font-family: Arial, sans-serif;
        /* Add the desired font-family */
      }

      
      table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
      }

      table th,
      table td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ccc;
      }

      table th {
        background-color: #f2f2f2;
        font-weight: bold;
      }

      table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
      }

      h2 {
        margin-bottom: 20px;
        text-align: center;
        color: #333;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-family: Arial, sans-serif;
        /* Add the desired font-family */
      }

      .form-group {
        margin-bottom: 20px;
      }

      .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
      }

      .form-group input[type="text"],
      .form-group input[type="email"],
      .form-group input[type="number"],
      .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 14px;
      }

      .form-group input[type="submit"] {
        width: 100%;
        padding: 10px;
        background-color: #4CAF50;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
      }

      @media screen and (max-width: 480px) {
        .container {
          max-width: 100%;
        }
      }

      .success-message {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 10px;
        border-radius: 4px;
        margin-top: 20px;
        text-align: center;
      }

      .form-group .edit-button {
        display: inline-block;
        padding: 10px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        text-decoration: none;
        margin-bottom: 10px;
        width: 100%;
        box-sizing: border-box;
        text-align: center;
      }

      .form-group .confirm-button {
        display: inline-block;
        padding: 10px;
        background-color: #4CAF50;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
        box-sizing: border-box;
        transition: background-color 0.3s ease;
        text-align: center;
      }

      .form-group .edit-button:hover,
      .form-group .confirm-button:hover {
        background-color: #45a049;
      }

    </style>
  </head>
  <body>
    <div class="container">
      <h2>Order Summary</h2> 
      <?php
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["enter"])) {
          // Retrieve form data
          $name = $_POST["name"];
          $email = $_POST["email"];
          $address = $_POST["address"];
          $city = $_POST["city"];
          $postalCode = $_POST["postal_code"];
          $contactNumber = $_POST["contact_number"];
          $total = $_POST["total"];

          $checkout_items_name = $_POST["checkout_items_name"];
          $checkout_items_id = $_POST["checkout_items_id"];
          $checkout_items_quantity = $_POST["checkout_items_quantity"];
          $checkout_items_subtotal = $_POST["checkout_items_subtotal"];

          $checkout_items_length = count($checkout_items_id);
      ?> 
        <div class="form-group">
          <label>Name:</label>
          <p> <?php echo $name; ?> </p>
        </div>
        <div class="form-group">
          <label>Email:</label>
          <p> <?php echo $email; ?> </p>
        </div>
        <div class="form-group">
          <label>Street Address:</label>
          <p> <?php echo $address; ?> </p>
        </div>
        <div class="form-group">
          <label>City:</label>
          <p> <?php echo $city; ?> </p>
        </div>
        <div class="form-group">
          <label>Postal Code:</label>
          <p> <?php echo $postalCode; ?> </p>
        </div>
        <div class="form-group">
          <label>Contact Number:</label>
          <p> <?php echo $contactNumber; ?> </p>
        </div>
         <div class="form-group">
          <label>Items:</label>
          <table>
            <thead>
              <tr>
                <th>Product Name</th>
                <th>ID</th>
                <th>Quantity</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php for($i=0; $i<$checkout_items_length; $i++ ): ?>
                <tr>
                  <td> <?php echo $checkout_items_name[$i] ?> </td>
                  <td> <?php echo $checkout_items_id[$i] ?> </td>
                  <td> <?php echo $checkout_items_quantity[$i] ?> </td>
                  <td> <?php echo $checkout_items_subtotal[$i] ?> </td>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
        <div class="form-group">
          <label for="total">Total:</label>
          <p> <?php echo $total ; ?> </p>
        </div>

        <div class="form-group">
          <form method="POST" action="store_order.php">
            
            <input type="hidden" name="name" value="<?php echo $name; ?>">
            <input type="hidden" name="email" value="<?php echo $email; ?>">
            <input type="hidden" name="address" value="<?php echo $address; ?>">
            <input type="hidden" name="city" value="<?php echo $city; ?>">
            <input type="hidden" name="postal_code" value="<?php echo $postalCode; ?>">
            <input type="hidden" name="contact_number" value="<?php echo $contactNumber; ?>">

            <?php for ( $i = 0; $i<$checkout_items_length; $i++): ?>
            <input type="hidden" name="item_name[]" value= "<?php echo $checkout_items_name[$i] ?>">
            <input type="hidden" name="item_id[]" value= "<?php echo $checkout_items_id[$i] ?>">
            <input type="hidden" name="item_quantity[]" value= "<?php echo $checkout_items_quantity[$i] ?>">
            <input type="hidden" name="item_subtotal[]" value= "<?php echo $checkout_items_subtotal[$i] ?>">
            <?php endfor; ?>

            <input type="hidden" name="total" value="<?php echo $total; ?>">
            <input type="submit" value="Confirm" name="confirm">
          </form>
        </div> <?php
      } 
      ?>
      </div>
      <script>
        function confirmOrder() {
          var successMessage = document.createElement("p");
          successMessage.classList.add("success-message");
          successMessage.textContent = "Order placed successfully.";
          document.querySelector(".container").appendChild(successMessage);
        }
      </script>
  </body>
</html>