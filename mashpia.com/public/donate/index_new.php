<?php
// make sure using secure page
// if (!((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)) {
// 	header("Location: https://mashpia.com/donate");
// 	exit;
// }
$ip = $_SERVER['REMOTE_ADDR'];
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Donate - Tzivos Hashem</title>
		
		<!-- Bootstrap 5 CSS -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
		<!-- Bootstrap Icons -->
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
		<!-- Google Fonts -->
		<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
		<style>
			:root {
				--bs-primary: #2563eb;
				--bs-primary-rgb: 37, 99, 235;
			}
			
			body {
				font-family: 'Inter', sans-serif;
				background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
				min-height: 100vh;
			}
			
			.card {
				border: none;
				border-radius: 1rem;
				box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
			}
			
			.form-control, .form-select {
				border-radius: 0.75rem;
				border: 1px solid #e2e8f0;
				padding: 0.75rem 1rem;
				transition: all 0.2s ease;
				font-size: 16px; /* Prevents zoom on iOS */
			}
			
			.form-control:focus, .form-select:focus {
				border-color: var(--bs-primary);
				box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.1);
			}
			
			.form-floating > label {
				padding: 1rem;
			}
			
			.form-floating > .form-control {
				height: calc(3.5rem + 2px);
			}
			
			.btn-primary {
				border-radius: 0.75rem;
				padding: 0.75rem 2rem;
				font-weight: 500;
				background: linear-gradient(135deg, var(--bs-primary) 0%, #1d4ed8 100%);
				border: none;
				transition: all 0.2s ease;
				width: 100%; /* Full width on mobile */
			}
			
			.btn-primary:hover {
				transform: translateY(-1px);
				box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2);
			}
			
			.alert {
				border-radius: 0.75rem;
				border: none;
			}
			
			.input-group-text {
				border-radius: 0.75rem;
				background-color: #f8fafc;
				border: 1px solid #e2e8f0;
			}
			
			.form-select {
				background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
			}
			
			.logo-container img {
				transition: transform 0.3s ease;
			}
			
			.logo-container img:hover {
				transform: scale(1.05);
			}
			
			.form-label {
				font-weight: 500;
				color: #475569;
			}
			
			.card-body {
				background: rgba(255, 255, 255, 0.9);
				backdrop-filter: blur(10px);
			}

			/* Mobile Optimizations */
			@media (max-width: 768px) {
				.container {
					padding: 1rem;
				}

				.card-body {
					padding: 1.5rem !important;
				}

				.form-floating > .form-control {
					height: calc(3.25rem + 2px);
				}

				.form-floating > label {
					padding: 0.75rem;
				}

				.btn-primary {
					padding: 0.75rem 1.5rem;
				}

				.row {
					margin-left: -0.5rem;
					margin-right: -0.5rem;
				}

				.col-md-6, .col-md-4, .col-12 {
					padding-left: 0.5rem;
					padding-right: 0.5rem;
				}

				.g-4 {
					gap: 1rem !important;
				}

				/* Improve touch targets */
				.form-control, .form-select, .btn {
					min-height: 44px;
				}

				/* Adjust input spacing for better mobile UX */
				.form-floating {
					margin-bottom: 0.5rem;
				}

				/* Make alerts more compact on mobile */
				.alert {
					padding: 0.75rem 1rem;
					margin-bottom: 1rem;
				}

				/* Adjust logo size for mobile */
				.text-center img {
					max-width: 150px !important;
				}
			}

			/* Prevent zoom on input focus for iOS */
			@media screen and (-webkit-min-device-pixel-ratio:0) { 
				select,
				textarea,
				input {
					font-size: 16px;
				}
			}

			/* Credit Card Styling */
			.credit-card-icon {
				position: absolute;
				right: 1rem;
				top: 50%;
				transform: translateY(-50%);
				z-index: 10;
				opacity: 0.5;
				transition: opacity 0.2s ease;
			}

			.credit-card-icon.active {
				opacity: 1;
			}

			.form-floating .credit-card-icon {
				top: 25%;
			}

			.form-floating .credit-card-icon.active {
				top: 0;
			}
		</style>
	</head>
	
	<body>
		<div class="container py-4 py-md-5">
			<div class="row justify-content-center">
				<div class="col-12 col-md-8">
					<div class="card">
						<div class="card-body p-4 p-md-5">
							<div class="text-center mb-4 mb-md-5">
								<img src="/mobile/img_new/TH Logo-colorful-svg.svg" alt="Tzivos Hashem Logo" class="img-fluid" style="max-width: 200px;">
							</div>
							
							<?php if (isset($_GET['msg'])): ?>
								<div class="alert alert-success alert-dismissible fade show" role="alert">
									<i class="bi bi-check-circle-fill me-2"></i>
									<?= htmlspecialchars(urldecode($_GET['msg'])) ?>
									<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
								</div>
							<?php endif; ?>
							
							<?php if (isset($_GET['error'])): ?>
								<div class="alert alert-danger alert-dismissible fade show" role="alert">
									<i class="bi bi-exclamation-circle-fill me-2"></i>
									<?= htmlspecialchars(urldecode($_GET['error'])) ?>
									<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
								</div>
							<?php endif; ?>
							
							<form action="https://mashpia.com/donate/donate.php" method="post" id="donateForm" class="needs-validation" novalidate>
								<div class="row g-3 g-md-4">
									<div class="col-12 col-md-6">
										<div class="form-floating">
											<input type="email" class="form-control" name="email" id="email" placeholder="Email Address" required>
											<label for="email"><i class="bi bi-envelope me-2"></i>Email Address</label>
										</div>
									</div>
									
									<div class="col-12 col-md-6">
										<div class="form-floating">
											<input type="tel" class="form-control" name="phone" id="phone" placeholder="Contact Phone Number" required>
											<label for="phone"><i class="bi bi-telephone me-2"></i>Contact Phone Number</label>
										</div>
									</div>
									
									<div class="col-12 col-md-6">
										<div class="form-floating">
											<input type="text" class="form-control" name="ccfname" id="ccfname" placeholder="First Name on Card" required>
											<label for="ccfname"><i class="bi bi-person me-2"></i>First Name on Card</label>
										</div>
									</div>
									
									<div class="col-12 col-md-6">
										<div class="form-floating">
											<input type="text" class="form-control" name="cclname" id="cclname" placeholder="Last Name on Card" required>
											<label for="cclname"><i class="bi bi-person me-2"></i>Last Name on Card</label>
										</div>
									</div>
									
									<div class="col-12">
										<div class="form-floating">
											<input type="text" class="form-control" name="ccaddress" id="ccaddress" placeholder="Billing Address Line 1" required>
											<label for="ccaddress"><i class="bi bi-geo-alt me-2"></i>Billing Address Line 1</label>
										</div>
									</div>
									
									<div class="col-12">
										<div class="form-floating">
											<input type="text" class="form-control" name="ccaddress2" id="ccaddress2" placeholder="Billing Address Line 2">
											<label for="ccaddress2"><i class="bi bi-geo-alt me-2"></i>Billing Address Line 2 (Optional)</label>
										</div>
									</div>
									
									<div class="col-12 col-md-4">
										<div class="form-floating">
											<input type="text" class="form-control" name="cccity" id="cccity" placeholder="City" required>
											<label for="cccity"><i class="bi bi-building me-2"></i>City</label>
										</div>
									</div>
									
									<div class="col-12 col-md-4">
										<div class="form-floating">
											<input type="text" class="form-control" name="ccstate" id="ccstate" placeholder="State" required>
											<label for="ccstate"><i class="bi bi-geo me-2"></i>State</label>
										</div>
									</div>
									
									<div class="col-12 col-md-4">
										<div class="form-floating">
											<input type="text" class="form-control" name="cczip" id="cczip" placeholder="Zip" required>
											<label for="cczip"><i class="bi bi-pin-map me-2"></i>Zip</label>
										</div>
									</div>
									
									<div class="col-12">
										<div class="form-floating">
											<input type="text" class="form-control" name="cccountry" id="cccountry" placeholder="Country">
											<label for="cccountry"><i class="bi bi-globe me-2"></i>Country</label>
										</div>
									</div>
									
									<div class="col-12">
										<div class="form-floating position-relative">
											<input type="text" class="form-control" name="ccnum" id="ccnum" placeholder="Credit Card Number" required maxlength="19" pattern="^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|6(?:011|5[0-9]{2})[0-9]{12})$">
											<label for="ccnum"><i class="bi bi-credit-card me-2"></i>Credit Card Number</label>
											<i class="bi bi-credit-card-2-front credit-card-icon" id="cardIcon"></i>
										</div>
										<div class="invalid-feedback">
											Please enter a valid credit card number.
										</div>
									</div>
									
									<div class="col-12 col-md-6">
										<div class="form-floating">
											<input type="text" class="form-control" name="ccexp" id="ccexp" placeholder="Expiry - MMYY" required>
											<label for="ccexp"><i class="bi bi-calendar me-2"></i>Expiry - MMYY</label>
										</div>
									</div>
									
									<div class="col-12 col-md-6">
										<div class="form-floating">
											<input type="text" class="form-control" name="cccvv" id="cccvv" placeholder="CVV" required>
											<label for="cccvv"><i class="bi bi-shield-lock me-2"></i>CVV</label>
										</div>
									</div>
									
									<div class="col-12">
										<label for="amount" class="form-label"><i class="bi bi-currency-dollar me-2"></i>Donation Amount</label>
										<select class="form-select" name="amount" id="amount" required>
											<option value="0">Choose Amount</option>
											<?php
											$amounts = array(18,36,50,54,72,90,100,250,500,1000,5000);
											foreach ($amounts as $amount) {
												echo "<option value='" . $amount . "'>$" . $amount . "</option>";
											}
											?>
											<option value="-1">Other Amount</option>
										</select>
									</div>
									
									<div class="otherGroup" style="display: none;">
										<div class="col-12">
											<div class="input-group">
												<span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
												<input type="number" class="form-control" name="other" id="other" placeholder="Amount (to the nearest dollar)">
												<span class="input-group-text">.00</span>
											</div>
										</div>
									</div>
									
									<div class="col-12">
										<div class="form-floating">
											<textarea class="form-control" name="desc" id="desc" placeholder="In Honor of... / In memory of... / Description" style="height: 100px"></textarea>
											<label for="desc"><i class="bi bi-chat-square-text me-2"></i>In Honor of... / In memory of... / Description</label>
										</div>
									</div>
									
									<div class="col-12 text-center mt-4">
										<button type="button" class="btn btn-primary btn-lg g-recaptcha"
											data-sitekey="6LfOSmMrAAAAAAgUL5hYf2hb2lM2UA0zdRCgs3Nc"
											data-callback="onSubmit"
											data-action="submit">
											<i class="bi bi-heart-fill me-2"></i>Submit Donation
										</button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Bootstrap 5 JS Bundle with Popper -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
		<!-- jQuery -->
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<!-- reCAPTCHA -->
		<script src="https://www.google.com/recaptcha/enterprise.js?render=6LfOSmMrAAAAAAgUL5hYf2hb2lM2UA0zdRCgs3Nc"></script>
		
		<script>
			var ip = '<?= $ip ?>';

			// Credit Card Validation and Formatting
			const cardPatterns = {
				visa: {
					pattern: /^4/,
					icon: 'bi-credit-card-2-front',
					spaces: [4, 8, 12],
					length: 16
				},
				mastercard: {
					pattern: /^5[1-5]/,
					icon: 'bi-credit-card-2-front',
					spaces: [4, 8, 12],
					length: 16
				},
				amex: {
					pattern: /^3[47]/,
					icon: 'bi-credit-card-2-front',
					spaces: [4, 10],
					length: 15
				},
				discover: {
					pattern: /^6(?:011|5)/,
					icon: 'bi-credit-card-2-front',
					spaces: [4, 8, 12],
					length: 16
				}
			};

			function formatCardNumber(input) {
				let value = input.value.replace(/\D/g, '');
				let cardType = null;

				// Determine card type
				for (let type in cardPatterns) {
					if (cardPatterns[type].pattern.test(value)) {
						cardType = type;
						break;
					}
				}

				// Update icon
				const cardIcon = document.getElementById('cardIcon');
				if (cardType) {
					cardIcon.className = `bi bi-credit-card-2-front credit-card-icon active`;
					cardIcon.setAttribute('title', cardType.charAt(0).toUpperCase() + cardType.slice(1));
				} else {
					cardIcon.className = 'bi bi-credit-card-2-front credit-card-icon';
					cardIcon.removeAttribute('title');
				}

				// Format number with spaces
				if (cardType && cardPatterns[cardType].spaces) {
					let formattedValue = '';
					let index = 0;
					
					for (let i = 0; i < value.length; i++) {
						if (cardPatterns[cardType].spaces.includes(i)) {
							formattedValue += ' ';
						}
						formattedValue += value[i];
					}

					input.value = formattedValue;
				} else {
					input.value = value;
				}

				// Validate length
				if (cardType && value.length > cardPatterns[cardType].length) {
					input.value = input.value.slice(0, cardPatterns[cardType].length + cardPatterns[cardType].spaces.length);
				}
			}

			function validateCardNumber(input) {
				const value = input.value.replace(/\s/g, '');
				let isValid = false;
				let cardType = null;

				// Check card type and basic validation
				for (let type in cardPatterns) {
					if (cardPatterns[type].pattern.test(value) && value.length === cardPatterns[type].length) {
						cardType = type;
						isValid = true;
						break;
					}
				}

				// Luhn algorithm validation
				if (isValid) {
					let sum = 0;
					let isEven = false;

					// Loop through values starting from the rightmost digit
					for (let i = value.length - 1; i >= 0; i--) {
						let digit = parseInt(value[i]);

						if (isEven) {
							digit *= 2;
							if (digit > 9) {
								digit -= 9;
							}
						}

						sum += digit;
						isEven = !isEven;
					}

					isValid = sum % 10 === 0;
				}

				input.setCustomValidity(isValid ? '' : 'Please enter a valid credit card number');
				return isValid;
			}

			// Add event listeners for card number input
			document.addEventListener('DOMContentLoaded', function() {
				const cardInput = document.getElementById('ccnum');
				
				cardInput.addEventListener('input', function() {
					formatCardNumber(this);
				});

				cardInput.addEventListener('blur', function() {
					validateCardNumber(this);
				});

				cardInput.addEventListener('keypress', function(e) {
					if (!/\d/.test(e.key)) {
						e.preventDefault();
					}
				});
			});

			function onSubmit(token) {
				let input = document.createElement('input');
				input.type = 'hidden';
				input.name = 'g-recaptcha-response';
				input.value = token;
				document.getElementById('donateForm').appendChild(input);
				document.getElementById('donateForm').submit();
			}
			
			$(function() {
				checkFraud();
				
				$("#amount").change(function() {
					var amount = parseInt($(this).val());
					if (amount == -1) {
						$(".otherGroup").show();
					} else {
						$(".otherGroup").hide();
					}
				});
				
				// Form validation
				const form = document.getElementById('donateForm');
				form.addEventListener('submit', function(event) {
					if (!form.checkValidity()) {
						event.preventDefault();
						event.stopPropagation();
					}
					form.classList.add('was-validated');
				});
			});

			function checkFraud() {
				let ips = [];
				if (typeof(Storage) !== 'undefined') {
					if (!localStorage.getItem('ips')) {
						var d = new Date();
						ips.push({
							address: ip,
							requests: [d]
						});
						localStorage.setItem('ips', JSON.stringify(ips));
					} else {
						let found = false;
						ips = JSON.parse(localStorage.getItem('ips'));
						for (i in ips) {
							let info = ips[i];
							if (info.address == ip) {
								found = true;
								let prevTime = 0;
								let numRequests = 0;
								for (r in info.requests) {
									let request = info.requests[r];
									let curTime = new Date(request).getTime();
									if (prevTime) {
										let diff = (curTime - prevTime) / 1000;
										if (diff <= 60) {
											numRequests++;
										}
										let day = 60 * 60 * 24;
										if (diff > day) {
											ips[i].requests = [];
										}
									}
									prevTime = curTime;
								}
								if (numRequests >= 5) {
									alert("You cannot submit many donations in such a short amount of time.");
									return false;
								} else {
									ips[i].requests.push(new Date());
								}
							}
						}
						if (!found) {
							var d = new Date();
							ips.push({
								address: ip,
								requests: [d]
							});
						}
						localStorage.setItem('ips', JSON.stringify(ips));
					}
				}
			}
		</script>
	</body>
</html>