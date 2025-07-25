<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CS Low Motor & Spare Parts Trading</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      scroll-behavior: smooth;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
      color: #333;
      margin: 0;
      padding: 0;
    }
    nav.navbar {
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    section {
      padding: 100px 0;
    }
    h2 {
      font-weight: 700;
      color: #003366;
    }
    .hero {
      position: relative;
      width: 100%;
      margin-top: 0;
    }
    .hero img {
      width: 100%;
      display: block;
      vertical-align: middle;
    }
    .hero-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0.5);
      z-index: 1;
    }
    .hero-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      color: white;
      text-shadow: 0 0 10px rgba(0,0,0,0.8);
      z-index: 2;
    }
    .hero h1 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
    }
    .card {
      border: none;
      border-radius: 0.75rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: transform 0.2s ease;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .btn-primary {
      background-color: #0056b3;
      border: none;
    }
    .btn-success {
      background-color: #28a745;
      border: none;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">CS Low</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#about">About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
        <li class="nav-item"><a class="nav-link" href="#map">Location</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero Banner -->
<section class="hero">
  <img src="/images/hero-background.jpg" alt="Motorcycle Service Banner">
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <h1>CS Low Motor & Spare Parts Trading</h1>
    <p class="lead">Your trusted motorcycle repair and parts expert.</p>
    <a href="#contact" class="btn btn-primary btn-lg mt-3">Contact Us</a>
  </div>
</section>

<!-- About Us -->
<section id="about" class="bg-light">
  <div class="container">
    <h2 class="text-center mb-4">About Us</h2>
    <p class="text-center lead">
      We are a professional motorcycle service and spare parts trading shop.
      With experienced technicians and high-quality parts, we provide reliable services and products for all types of motorcycles.
      Committed to excellence and customer satisfaction.
    </p>
  </div>
</section>

<!-- Services -->
<section id="services">
  <div class="container">
    <h2 class="text-center mb-5">Our Services</h2>
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title">Motorcycle Repair & Service</h5>
            <ul class="list-unstyled">
              <li>✔ Professional Tyre Replacement</li>
              <li>✔ Engine Oil Change</li>
              <li>✔ Battery Replacement and Installation</li>
              <li>✔ Major and Minor Component Repair and Replacement, among other services</li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title">Spare Parts Trading</h5>
            <ul class="list-unstyled">
              <li>✔ Engine Components</li>
              <li>✔ Brake and Suspension Parts</li>
              <li>✔ Electrical Parts</li>
              <li>✔ Wholesale & Retail</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contact -->
<section id="contact" class="bg-light">
  <div class="container">
    <h2 class="text-center mb-4">Contact Us</h2>
    <div class="text-center mb-4">
      <p class="mb-1">📍 Lot.5949, Jalan Bukit Ketri, Kg Alor Tampang, 02400 Perlis</p>
      <p class="mb-1">📞 019-233 9789</p>
    </div>
    <div class="text-center">
      <a href="tel:0192339789" class="btn btn-primary btn-lg me-2">Call Us</a>
      <a href="https://wa.me/60192339789" class="btn btn-success btn-lg">WhatsApp</a>
    </div>
  </div>
</section>

<!-- Map -->
<section id="map">
  <div class="container">
    <h2 class="text-center mb-4">Our Location</h2>
    <div class="ratio ratio-16x9 shadow-sm">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1250.720953573046!2d100.23859157568555!3d6.514746117347165!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x304c977c79b252bf%3A0x8e6e84be2bdfac8c!2sCS%20Low%20Motor%20%26%20Spare%20Parts%20Trading!5e0!3m2!1sen!2smy!4v1751424608710!5m2!1sen!2smy"
        style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>
</section>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
