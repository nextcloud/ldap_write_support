<?php

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
use Behat\Gherkin\Node\TableNode;
use Behat\Hook\AfterScenario;
use Behat\Hook\AfterSuite;
use Behat\Hook\BeforeScenario;
use Behat\Hook\BeforeSuite;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

require __DIR__ . '/../../vendor/autoload.php';

trait BasicStructure {
	/** @var string */
	private $currentUser = '';

	/** @var string */
	private $currentServer = '';

	/** @var string */
	private $baseUrl = '';

	/** @var int */
	private $apiVersion = 1;

	/** @var ResponseInterface */
	private $response = null;

	/** @var CookieJar */
	private $cookieJar;

	/** @var string */
	private $requestToken;

	protected $adminUser;
	protected $regularUser;
	protected $localBaseUrl;
	protected $remoteBaseUrl;

	public function __construct($baseUrl, $admin, $regular_user_password) {
		// Initialize your context here
		$this->baseUrl = $baseUrl;
		$this->adminUser = $admin;
		$this->regularUser = $regular_user_password;
		$this->localBaseUrl = $this->baseUrl;
		$this->remoteBaseUrl = $this->baseUrl;
		$this->currentServer = 'LOCAL';
		$this->cookieJar = new CookieJar();

		// in case of ci deployment we take the server url from the environment
		$testServerUrl = getenv('TEST_SERVER_URL');
		if ($testServerUrl !== false) {
			$this->baseUrl = $testServerUrl;
			$this->localBaseUrl = $testServerUrl;
		}

		// federated server url from the environment
		$testRemoteServerUrl = getenv('TEST_SERVER_FED_URL');
		if ($testRemoteServerUrl !== false) {
			$this->remoteBaseUrl = $testRemoteServerUrl;
		}
	}

	public function getResponse() {
		return $this->response;
	}

	/**
	 * @param string $version
	 */
	#[Given('/^using api version "(\d+)"$/')]
	public function usingApiVersion($version) {
		$this->apiVersion = (int)$version;
	}

	/**
	 * @param string $user
	 */
	#[Given('/^As an "([^"]*)"$/')]
	public function asAn($user) {
		$this->currentUser = $user;
	}

	/**
	 * @param string $server
	 * @return string Previous used server
	 */
	#[Given('/^Using server "(LOCAL|REMOTE)"$/')]
	public function usingServer($server) {
		$previousServer = $this->currentServer;
		if ($server === 'LOCAL') {
			$this->baseUrl = $this->localBaseUrl;
			$this->currentServer = 'LOCAL';
			return $previousServer;
		} else {
			$this->baseUrl = $this->remoteBaseUrl;
			$this->currentServer = 'REMOTE';
			return $previousServer;
		}
	}

	/**
	 * @param string $verb
	 * @param string $url
	 */
	#[When('/^sending "([^"]*)" to "([^"]*)"$/')]
	public function sendingTo($verb, $url) {
		$this->sendingToWith($verb, $url, null);
	}

	/**
	 * Parses the xml answer to get ocs response which doesn't match with
	 * http one in v1 of the api.
	 *
	 * @param ResponseInterface $response
	 * @return string
	 */
	public function getOCSResponse($response) {
		return simplexml_load_string($response->getBody())->meta[0]->statuscode;
	}

	/**
	 * This function is needed to use a vertical fashion in the gherkin tables.
	 *
	 * @param array $arrayOfArrays
	 * @return array
	 */
	public function simplifyArray($arrayOfArrays) {
		$a = array_map(fn ($subArray) => $subArray[0], $arrayOfArrays);
		return $a;
	}

	/**
	 * @param string $verb
	 * @param string $url
	 * @param TableNode $body
	 */
	#[When('/^sending "([^"]*)" to "([^"]*)" with$/')]
	public function sendingToWith($verb, $url, $body) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php" . $url;
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		} elseif (!str_starts_with($this->currentUser, 'anonymous')) {
			$options['auth'] = [$this->currentUser, $this->regularUser];
		}
		$options['headers'] = [
			'OCS-APIRequest' => 'true'
		];
		if ($body instanceof TableNode) {
			$fd = $body->getRowsHash();
			$options['form_params'] = $fd;
		}

		// TODO: Fix this hack!
		if ($verb === 'PUT' && $body === null) {
			$options['form_params'] = [
				'foo' => 'bar',
			];
		}

		try {
			$this->response = $client->request($verb, $fullUrl, $options);
		} catch (ClientException $ex) {
			$this->response = $ex->getResponse();
		}
	}

	/**
	 * @param string $verb
	 * @param string $url
	 */
	#[When('/^sending "([^"]*)" with exact url to "([^"]*)"$/')]
	public function sendingToDirectUrl($verb, $url) {
		$this->sendingToWithDirectUrl($verb, $url, null);
	}

	public function sendingToWithDirectUrl($verb, $url, $body) {
		$fullUrl = substr($this->baseUrl, 0, -5) . $url;
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		} elseif (!str_starts_with($this->currentUser, 'anonymous')) {
			$options['auth'] = [$this->currentUser, $this->regularUser];
		}
		if ($body instanceof TableNode) {
			$fd = $body->getRowsHash();
			$options['form_params'] = $fd;
		}

		try {
			$this->response = $client->request($verb, $fullUrl, $options);
		} catch (ClientException $ex) {
			$this->response = $ex->getResponse();
		}
	}

	public function isExpectedUrl($possibleUrl, $finalPart) {
		$baseUrlChopped = substr($this->baseUrl, 0, -4);
		$endCharacter = strlen($baseUrlChopped) + strlen((string)$finalPart);
		return (substr((string)$possibleUrl, 0, $endCharacter) == "$baseUrlChopped" . "$finalPart");
	}

	/**
	 * @param int $statusCode
	 */
	#[Then('/^the OCS status code should be "([^"]*)"$/')]
	public function theOCSStatusCodeShouldBe($statusCode) {
		Assert::assertEquals($statusCode, $this->getOCSResponse($this->response));
	}

	/**
	 * @param int $statusCode
	 */
	#[Then('/^the HTTP status code should be "([^"]*)"$/')]
	public function theHTTPStatusCodeShouldBe($statusCode) {
		Assert::assertEquals($statusCode, $this->response->getStatusCode());
	}

	/**
	 * @param string $contentType
	 */
	#[Then('/^the Content-Type should be "([^"]*)"$/')]
	public function theContentTypeShouldbe($contentType) {
		Assert::assertEquals($contentType, $this->response->getHeader('Content-Type')[0]);
	}

	/**
	 * @param ResponseInterface $response
	 */
	private function extracRequestTokenFromResponse(ResponseInterface $response) {
		$this->requestToken = substr((string)preg_replace('/(.*)data-requesttoken="(.*)">(.*)/sm', '\2', $response->getBody()->getContents()), 0, 89);
	}

	/**
	 * @param string $user
	 */
	#[Given('Logging in using web as :user')]
	public function loggingInUsingWebAs($user) {
		$loginUrl = substr($this->baseUrl, 0, -5) . '/index.php/login';
		// Request a new session and extract CSRF token
		$client = new Client();
		$response = $client->get(
			$loginUrl,
			[
				'cookies' => $this->cookieJar,
			]
		);
		$this->extracRequestTokenFromResponse($response);

		// Login and extract new token
		$password = ($user === 'admin') ? 'admin' : '123456';
		$client = new Client();
		$response = $client->post(
			$loginUrl,
			[
				'form_params' => [
					'user' => $user,
					'password' => $password,
					'requesttoken' => $this->requestToken,
				],
				'cookies' => $this->cookieJar,
			]
		);
		$this->extracRequestTokenFromResponse($response);
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param TableNode|array|null $body
	 */
	#[When('Sending a :method to :url with requesttoken')]
	public function sendingAToWithRequesttoken($method, $url, $body = null) {
		$baseUrl = substr($this->baseUrl, 0, -5);

		$options = [
			'cookies' => $this->cookieJar,
			'headers' => [
				'requesttoken' => $this->requestToken
			],
		];

		if ($body instanceof TableNode) {
			$fd = $body->getRowsHash();
			$options['form_params'] = $fd;
		} elseif ($body) {
			$options = array_merge($options, $body);
		}

		$client = new Client();
		try {
			$this->response = $client->request(
				$method,
				$baseUrl . $url,
				$options
			);
		} catch (ClientException $e) {
			$this->response = $e->getResponse();
		}
	}

	/**
	 * @param string $method
	 * @param string $url
	 */
	#[When('Sending a :method to :url without requesttoken')]
	public function sendingAToWithoutRequesttoken($method, $url) {
		$baseUrl = substr($this->baseUrl, 0, -5);

		$client = new Client();
		try {
			$this->response = $client->request(
				$method,
				$baseUrl . $url,
				[
					'cookies' => $this->cookieJar
				]
			);
		} catch (ClientException $e) {
			$this->response = $e->getResponse();
		}
	}

	public static function removeFile($path, $filename) {
		if (file_exists("$path" . "$filename")) {
			unlink("$path" . "$filename");
		}
	}

	/**
	 * @param string $user
	 * @param string $filename
	 * @param string $text
	 */
	#[Given('User :user modifies text of :filename with text :text')]
	public function modifyTextOfFile($user, $filename, $text) {
		self::removeFile($this->getDataDirectory() . "/$user/files", "$filename");
		file_put_contents($this->getDataDirectory() . "/$user/files" . "$filename", "$text");
	}

	private function getDataDirectory() {
		// Based on "runOcc" from CommandLine trait
		$args = ['config:system:get', 'datadirectory'];
		$args = array_map(fn ($arg) => escapeshellarg((string)$arg), $args);
		$args[] = '--no-ansi --no-warnings';
		$args = implode(' ', $args);

		$descriptor = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];
		$process = proc_open('php console.php ' . $args, $descriptor, $pipes, $ocPath = '../..');
		$lastStdOut = stream_get_contents($pipes[1]);
		proc_close($process);

		return trim($lastStdOut);
	}

	/**
	 * @param string $filename
	 * @param string $times
	 * @param string $user
	 */
	#[Given('file :filename is created :times times in :user user data')]
	public function fileIsCreatedTimesInUserData($filename, $times, $user) {
		for ($i = 0; $i < $times; $i++) {
			file_put_contents($this->getDataDirectory() . "/$user/files" . "$filename-$i", "content-$i");
		}
	}

	public function createFileSpecificSize($name, $size) {
		$file = fopen('work/' . "$name", 'w');
		fseek($file, $size - 1, SEEK_CUR);
		fwrite($file, 'a'); // write a dummy char at SIZE position
		fclose($file);
	}

	public function createFileWithText($name, $text) {
		$file = fopen('work/' . "$name", 'w');
		fwrite($file, (string)$text);
		fclose($file);
	}

	/**
	 * @param string $filename
	 * @param string $size
	 */
	#[Given('file :filename of size :size is created in local storage')]
	public function fileIsCreatedInLocalStorageWithSize($filename, $size) {
		$this->createFileSpecificSize("local_storage/$filename", $size);
	}

	/**
	 * @param string $filename
	 * @param string $text
	 */
	#[Given('file :filename with text :text is created in local storage')]
	public function fileIsCreatedInLocalStorageWithText($filename, $text) {
		$this->createFileWithText("local_storage/$filename", $text);
	}

	/**
	 * @param int $seconds
	 */
	#[When('Sleep for :seconds seconds')]
	public function sleepForSeconds($seconds) {
		sleep((int)$seconds);
	}

	#[BeforeSuite]
	public static function addFilesToSkeleton() {
		$skeletonDir = __DIR__ . '/../../../../../../core/skeleton/';
		for ($i = 0; $i < 5; $i++) {
			file_put_contents($skeletonDir . 'textfile' . "$i" . '.txt', "Nextcloud test text file\n");
		}
		if (!file_exists($skeletonDir . 'FOLDER')) {
			mkdir($skeletonDir . 'FOLDER', 0777, true);
		}
		if (!file_exists($skeletonDir . 'PARENT')) {
			mkdir($skeletonDir . 'PARENT', 0777, true);
		}
		file_put_contents($skeletonDir . 'PARENT/' . 'parent.txt', "Nextcloud test text file\n");
		if (!file_exists($skeletonDir . 'PARENT/CHILD')) {
			mkdir($skeletonDir . 'PARENT/CHILD', 0777, true);
		}
		file_put_contents($skeletonDir . 'PARENT/CHILD/' . 'child.txt', "Nextcloud test text file\n");
	}

	#[AfterSuite]
	public static function removeFilesFromSkeleton() {
		$skeletonDir = __DIR__ . '/../../../../../../core/skeleton/';
		for ($i = 0; $i < 5; $i++) {
			self::removeFile("$skeletonDir", 'textfile' . "$i" . '.txt');
		}
		if (is_dir($skeletonDir . 'FOLDER')) {
			rmdir($skeletonDir . 'FOLDER');
		}
		self::removeFile($skeletonDir . 'PARENT/CHILD/', 'child.txt');
		if (is_dir($skeletonDir . 'PARENT/CHILD')) {
			rmdir($skeletonDir . 'PARENT/CHILD');
		}
		self::removeFile($skeletonDir . 'PARENT/', 'parent.txt');
		if (is_dir($skeletonDir . 'PARENT')) {
			rmdir($skeletonDir . 'PARENT');
		}
	}

	#[BeforeScenario]
	public static function removeFilesFromLocalStorageBefore() {
		$dir = './work/local_storage/';
		$di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
		$ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($ri as $file) {
			$file->isDir() ? rmdir($file) : unlink($file);
		}
	}

	#[AfterScenario]
	public static function removeFilesFromLocalStorageAfter() {
		$dir = './work/local_storage/';
		$di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
		$ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($ri as $file) {
			$file->isDir() ? rmdir($file) : unlink($file);
		}
	}

	#[Given('/^cookies are reset$/')]
	public function cookiesAreReset() {
		$this->cookieJar = new CookieJar();
	}

	/**
	 * @param TableNode $table
	 * @throws \Exception
	 */
	#[Then('The following headers should be set')]
	public function theFollowingHeadersShouldBeSet(TableNode $table) {
		foreach ($table->getTable() as $header) {
			$headerName = $header[0];
			$expectedHeaderValue = $header[1];
			$returnedHeader = $this->response->getHeader($headerName)[0];
			if ($returnedHeader !== $expectedHeaderValue) {
				throw new \Exception(
					sprintf(
						"Expected value '%s' for header '%s', got '%s'",
						$expectedHeaderValue,
						$headerName,
						$returnedHeader
					)
				);
			}
		}
	}
}
