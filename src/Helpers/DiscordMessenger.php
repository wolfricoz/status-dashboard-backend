<?php

namespace App\Helpers;

use DateTime;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class DiscordMessenger
{
	private string $webhookUrl;
	private HttpClientInterface $client;

	public function __construct()
	{
		$this->webhookUrl = $_ENV['SUPPORT_WEBHOOK'];
		$this->client = HttpClient::create();

	}

	public function sendNotification(string $title,
	                                 string $message,
	                                 string $footer = "status.roleplaymeets.com checks the status of the server every minute.",
	                                 string $color = '#00FF00'): bool
	{
		if (empty($this->webhookUrl)) {
			error_log('Discord webhook URL is not set.');
			return false;
		}
		$json_data = json_encode([
				"username" => "status.roleplaymeets.com",
				"embeds" => [
						[
								"title" => $title,
								"type" => "rich",
								"description" => $message,
								"url" => "https://status.roleplaymeets.com/",
								"timestamp" => (new DateTime('now'))->format(DateTime::ATOM),
								"color" => hexdec($color),
								"footer" => [
										"text" => $footer,
								],


						],
				],

		], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		try {
			$response = $this->client->request(
					'POST',
					$this->webhookUrl,
					[
							'headers' => [
									'Content-Type' => 'application/json',
							],
							'body' => $json_data,
							'max_redirects' => 10, // equivalent to CURLOPT_FOLLOWLOCATION
					],
			);
			return true;
		} catch (\Exception $e) {
			// Log the error or handle it as needed
			error_log('Discord webhook error: ' . $e->getMessage());
			return false;
		}
	}
}