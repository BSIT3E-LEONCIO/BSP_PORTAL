var cartItems = []; // Array to store cart items

function addToCart(name, id, price) {
    var item = {
        name: name,
        id: id,
        price: price,
        quantity: 1
    };
    cartItems.push(item);
    updateCart();
    showDialog('Item added to cart!');
}

function showDialog(message) {
    var dialog = document.getElementById('item-added');
    var dialogMessage = document.getElementById('added-confirmation');
    dialogMessage.textContent = message;
    dialog.classList.add('show');
    setTimeout(function() {
        dialog.classList.remove('show');
    }, 1500);
}

function updateCart() {
    var cartTable = document.getElementById('cart-items');
    var cartTotalElement = document.getElementById('cart-total');
    cartTable.innerHTML = '';
    var total = 0;

    for (var i = 0; i < cartItems.length; i++) {
        var item = cartItems[i];

        var row = document.createElement('tr');

        var nameCell = document.createElement('td');
        nameCell.textContent = item.name;
        row.appendChild(nameCell);

        var idCell = document.createElement('td');
        idCell.textContent = item.id;
        row.appendChild(idCell);

        var quantityCell = document.createElement('td');
        var minusButton = document.createElement('button');
        minusButton.textContent = '-';
        minusButton.classList.add('btn', 'btn-secondary');
        minusButton.addEventListener('click', (function(index) {
            return function() {
                if (cartItems[index].quantity > 1) {
                    cartItems[index].quantity--;
                    updateCart();
                }
            };
        })(i));
        quantityCell.appendChild(minusButton);

        var quantityText = document.createElement('span');
        quantityText.textContent = item.quantity;
        quantityCell.appendChild(quantityText);

        var plusButton = document.createElement('button');
        plusButton.textContent = '+';
        plusButton.classList.add('btn', 'btn-secondary');
        plusButton.addEventListener('click', (function(index) {
            return function() {
                cartItems[index].quantity++;
                updateCart();
            };
        })(i));
        quantityCell.appendChild(plusButton);


        row.appendChild(quantityCell);

        var subtotalCell = document.createElement('td');
        var subtotal = item.price * item.quantity;
        subtotalCell.textContent = 'Php ' + subtotal;
        row.appendChild(subtotalCell);

        var removeCell = document.createElement('td');
        var removeButton = document.createElement('button');
        removeButton.textContent = 'Remove';
        removeButton.addEventListener('click', (function(index) {
            return function() {
                cartItems.splice(index, 1);
                updateCart();
            };
        })(i));
        removeCell.appendChild(removeButton);
        row.appendChild(removeCell);

        cartTable.appendChild(row);

        total += subtotal;
    }

    cartTotalElement.textContent = 'Total: Php ' + total;

    if (cartItems.length > 0) {
        document.getElementById('cart-page').style.display = 'block';
    } else {
        document.getElementById('cart-page').style.display = 'none';
    }
}

function clearCart() {
    cartItems = [];
    updateCart();
}

function openCheckoutModal() {
    var checkoutURL = "checkoutform/checkout_form.php?";
    var total = 0; // Initialize the total variable
    for (var i = 0; i < cartItems.length; i++) {
        var item = cartItems[i];
        checkoutURL += "name" + i + "=" + encodeURIComponent(item.name) + "&";
        checkoutURL += "id" + i + "=" + encodeURIComponent(item.id) + "&";
        checkoutURL += "quantity" + i + "=" + encodeURIComponent(item.quantity) + "&";
        checkoutURL += "subtotal" + i + "=" + encodeURIComponent(item.price * item.quantity) + "&";

    }
    $('#checkoutFrame').attr('src', checkoutURL);
    $('#checkoutModal').modal('show');

}


$('#checkoutModal').on('hidden.bs.modal', function() {
    var iframe = document.getElementById('checkoutFrame');
    iframe.src = iframe.src;
});