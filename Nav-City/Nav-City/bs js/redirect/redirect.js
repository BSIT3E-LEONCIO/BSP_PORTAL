<script>
  function openCheckoutModal() {
    // Check if the user is logged in
    var isLoggedIn = <?php echo isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn'] ? 'true' : 'false'; ?>;
    
    if (isLoggedIn) {
      // If logged in, proceed to the checkout process
      window.location.href = '/path/to/checkout/page';
    } else {
      // If not logged in, show the login prompt modal
      var loginPromptModal = new bootstrap.Modal(document.getElementById('loginPromptModal'));
      loginPromptModal.show();
    }
  }

  function redirectToLogin() {
    window.location.href = '/path/to/login/page';
  }
</script>