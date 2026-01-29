<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Under Construction</title>
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 20px;
			color: #333;
		}

		.container {
			background: white;
			border-radius: 20px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
			padding: 40px;
			max-width: 600px;
			width: 100%;
			text-align: center;
			animation: fadeIn 0.5s ease-in;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(20px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.icon-container {
			margin-bottom: 30px;
		}

		.icon-container img {
			max-width: 200px;
			width: 100%;
			height: auto;
			border-radius: 10px;
		}

		/* Fallback icon if image doesn't exist */
		.icon-container::before {
			content: '🚧';
			font-size: 80px;
			display: block;
			margin-bottom: 20px;
		}

		.icon-container img {
			display: block;
			margin: 0 auto;
		}

		h1 {
			font-size: 2.5rem;
			color: #667eea;
			margin-bottom: 20px;
			font-weight: 700;
		}

		.message {
			font-size: 1.2rem;
			line-height: 1.8;
			color: #555;
			margin-bottom: 30px;
		}

		.message p {
			margin-bottom: 15px;
		}

		.thank-you {
			font-size: 1.1rem;
			color: #764ba2;
			font-weight: 600;
			margin-top: 20px;
		}

		/* Mobile optimizations */
		@media (max-width: 768px) {
			body {
				padding: 15px;
			}

			.container {
				padding: 30px 20px;
				border-radius: 15px;
			}

			h1 {
				font-size: 2rem;
				margin-bottom: 15px;
			}

			.message {
				font-size: 1rem;
				line-height: 1.6;
			}

			.icon-container img {
				max-width: 150px;
			}

			.icon-container::before {
				font-size: 60px;
			}
		}

		@media (max-width: 480px) {
			.container {
				padding: 25px 15px;
			}

			h1 {
				font-size: 1.75rem;
			}

			.message {
				font-size: 0.95rem;
			}

			.icon-container img {
				max-width: 120px;
			}

			.icon-container::before {
				font-size: 50px;
			}
		}

		/* Dark mode support */
		@media (prefers-color-scheme: dark) {
			body {
				background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
			}

			.container {
				background: #2d2d44;
				color: #e0e0e0;
			}

			h1 {
				color: #8b9aff;
			}

			.message {
				color: #c0c0c0;
			}

			.thank-you {
				color: #a78bfa;
			}
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="icon-container">
			<?php if (file_exists('under-construction-icon.jpg')): ?>
				<img src="under-construction-icon.jpg" alt="Under Construction" />
			<?php endif; ?>
		</div>
		
		<h1>Under Construction</h1>
		
		<div class="message">
			<p>This site is currently under construction.</p>
			<p>Once it is ready, it will become available.</p>
		</div>
		
		<div class="thank-you">
			Thank you for your patience!
		</div>
	</div>
</body>
</html>
