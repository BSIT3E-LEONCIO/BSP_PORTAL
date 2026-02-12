<!DOCTYPE html>
<html>
  <head>
    <title>Checkout Form</title>
    <style>

      body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: #f7f7f7;
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
      }

      h2 {
        margin-bottom: 20px;
        text-align: center;
        color: #333;
        text-transform: uppercase;
        letter-spacing: 2px;
      }

      h3 {
        margin-top: 40px;
        margin-bottom: 20px;
        color: #333;
        text-transform: uppercase;
        letter-spacing: 2px;
      }

      .form-group {
        margin-bottom: 20px;
      }

      .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #555;
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
        text-transform: uppercase;
        letter-spacing: 1px;
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
        padding: 10px 20px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        text-decoration: none;
      }
      .row {
        display: flex;
        justify-content: space-between;
      }

      .row .form-group {
        width: 48%;
      }


    </style>
  </head>
  <body>
    <div class="container">
      <h2>Checkout</h2>
      <h3>Customer Contact</h3>
      <form method="POST" action="summary.php">
        <div class="form-group">
          <label for="name">Name:</label>
          <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
          <label for="contact_number">Contact Number:</label>
          <input type="text" id="contact_number" name="contact_number" maxlength="11" pattern="[0-9]{11}" required>
        </div>
        <h3>Billing Information</h3>
        <div class="form-group">
          <label for="address">Street Address:</label>
          <input type="text" id="address" name="address" required>
        </div>
        <div class="row">
          <div class="form-group">
            <label for="city">City:</label>
            <input type="text" id="city" name="city" required>
          </div>
          <div class="form-group">
            <label for="postal_code">Postal Code:</label>
            <input type="text" id="postal_code" name="postal_code" maxlength="4" pattern="[0-9]{4}" required>
          </div>
        </div>
        <h3>Cart Items</h3> 

        <?php
          $cartItems = array();
          $index = 0;

          while (isset($_GET["name" . $index]) && isset($_GET["quantity" . $index])) {
            $proname = $_GET["name" . $index];
            $id = $_GET["id" . $index];
            $quantity = $_GET["quantity" . $index];
            $subtotal = $_GET["subtotal" . $index];
            $cartItems[] = array("name" => $proname, "quantity" => $quantity, "subtotal" => $subtotal,"id" => $id,); 
            $index++;
          }
        
        
        ?>
          <table>
            <thead>
              <tr>
                <th>Product Name</th>
                <th>ID</th>
                <th>Quantity</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody id="cart-items"> 
              <?php foreach ($cartItems as $item): ?> 
                <tr>
                  <td> <?php echo $item['name']; ?></td>
                  <td> <?php echo $item['id']; ?> </td>
                  <td> <?php echo $item['quantity']; ?> </td>
                  <td> <?php echo $item['subtotal']; ?> </td>
                <input type="hidden" id="checkout_items" name="checkout_items_name[]" value= "<?php echo $item['name'] ?>">
                <input type="hidden" id="checkout_items" name="checkout_items_id[]" value= "<?php echo $item['id'] ?>">
                <input type="hidden" id="checkout_items" name="checkout_items_quantity[]" value= "<?php echo $item['quantity'] ?>">
                <input type="hidden" id="checkout_items" name="checkout_items_subtotal[]" value= "<?php echo $item['subtotal'] ?>">
                </tr> 
                <?php endforeach;?> 
            </tbody>
        </table>



        <div class="form-group">
          <label for="total">Total:</label>
          <input type="hidden" name="total" id="checkout-total">
          <span id="total"></span>
        </div>
        <div class="form-group">
          <input type="submit" value="Enter" name="enter">
        </div>
      </form>
    </div>
    



    <style>
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
    </style>






    <script>
    

  const cartItems = <?php echo json_encode($cartItems); ?>;
  const totalElement = document.getElementById('total');
  const checkoutTotalElement = document.getElementById('checkout-total');  

  function calculateTotal() {
    let total = 0;
    for (const item of cartItems) {
      total += parseFloat(item.subtotal);
    }
    totalElement.textContent = total.toFixed(2);
    checkoutTotalElement.value = total.toFixed(2);
  }

  calculateTotal();
    </script>






  </body>
</html>