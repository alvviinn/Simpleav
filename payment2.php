<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Simpleav</title>
  <link rel="shortcut icon" href="img/logo2.jpg" type="image/x-icon">

  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="script.js"></script>
</head>
<body>

<section id="payment">

  <div class="payment-container bg-white">
    <div class="heading bg-primary">
      <h2>The Premium Plan</h2>
    </div>
    <form id="payment-form" action="stkpush2.php" method="POST">
      <div class="form-group">
        <label for="card-name">Name</label>
        <input type="text" id="card-name" name="card-name" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="phone-number">Phone Number</label>
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text">+254</span> <!-- Constant country code -->
          </div>
          <input type="tel" id="phone-number" name="phone-number" class="form-control" placeholder=" " required>
        </div>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary">Pay Now</button>
      </div>
    </form>
  </div>
</section>

<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
  AOS.init();
</script>

</body>
</html>
