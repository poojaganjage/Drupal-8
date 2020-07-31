<?php

namespace Drupal\docker\Service;

use Drupal\cloud\Service\CloudServiceBase;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class DockerService.
 */
class DockerService extends CloudServiceBase implements DockerServiceInterface {

  /**
   * The docker api version to use.
   *
   * @var string
   */
  protected $apiVersion = '';

  /**
   * The return format of API requests.
   *
   * @var string
   */
  protected $format = 'json';

  /**
   * The docker unix socket.
   *
   * @var string
   */
  protected $unixSocket;

  /**
   * Flag whether to use the unix socket in the API requests.
   *
   * @var bool
   */
  protected $useSocket = TRUE;

  /**
   * The config factory.
   *
   * Subclasses should use the self::config() method, which may be overridden to
   * address specific needs when loading config, rather than this property
   * directly. See \Drupal\Core\Form\ConfigFormBase::config() for an example of
   * this.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Guzzle client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Constructs a new DockerService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory.
   * @param \Drupal\Core\Http\ClientFactory $client_factory
   *   The http client.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              ClientFactory $client_factory) {

    $this->configFactory = $config_factory;
    $this->httpClient = $client_factory->fromOptions([
      // No timeout because docker images can be quite large.
      'timeout' => 0,
    ]);

    // The parent constructor takes care of $this->messenger object.
    parent::__construct();

    // Set the unix socket.
    $this->unixSocket = $config_factory->get('docker.settings')->get('docker_unix_socket');
    $this->apiVersion = $config_factory->get('docker.settings')->get('docker_api_version');
  }

  /**
   * Helper method to do a Guzzle HTTP POST.
   *
   * @param string $endpoint
   *   The endpoint to access.
   * @param array $params
   *   Parameters to pass to the POST request.
   *
   * @return string|bool
   *   String interpretation of the response body or FALSE
   */
  protected function doPost($endpoint, array $params = []) {
    $output = FALSE;
    try {
      $headers = array_merge_recursive($this->getDefaultParams(), $params);
      $response = $this->httpClient->post(
        $endpoint,
        $headers
      );
      $output = $response->getBody()->getContents();
    }
    catch (GuzzleException $error) {
      $response = $error->getResponse();
      $response_info = $response->getBody()->getContents();
      $message = new FormattableMarkup(
        'Docker api error. Error details are ?: [] as follows:<pre>@response</pre>',
        [
          '@response' => print_r(json_decode($response_info), TRUE),
        ]
      );
      $this->logError('Remote API Connection', $error, $message);
    }
    catch (\Exception $error) {
      $this->logError('Remote API Connection', $error, $this->t('An unknown error
      occurred while trying to connect to the remote API. This is not a Guzzle
      error, nor an error in the remote API, rather a generic local error
      occurred. The reported error was @error',
        ['@error' => $error->getMessage()]
      ));
    }
    return $output;
  }

  /**
   * Helper method to do a Guzzle HTTP GET.
   *
   * @param string $endpoint
   *   The endpoint to access.
   * @param array $params
   *   Parameters to pass to the POST request.
   *
   * @return string|bool
   *   String interpretation of the response body or FALSE
   */
  protected function doGet($endpoint, array $params = []) {
    $output = FALSE;
    try {
      $response = $this->httpClient->get(
        $endpoint,
        array_merge($this->getDefaultParams(), $params)
      );

      $output = $response->getBody()->getContents();
    }
    catch (GuzzleException $error) {
      $response = $error->getResponse();
      if ($response !== NULL) {
        $response_info = $response->getBody()->getContents();
        $response_info = new FormattableMarkup(
          'Docker api error. Error details are as follows:<pre>@response</pre>',
          [
            '@response' => print_r(json_decode($response_info), TRUE),
          ]
        );
      }
      else {
        $response_info = $error->getMessage();
      }
      $message = new FormattableMarkup(
        'Docker api error. Error details are as follows:<pre>@response</pre>',
        [
          '@response' => $response_info,
        ]
      );

      $this->logError('Remote API Connection', $error, $message);
    }
    catch (\Exception $error) {
      $this->logError('Remote API Connection', $error, $this->t('An unknown error
      occurred while trying to connect to the remote API. This is not a Guzzle
      error, nor an error in the remote API, rather a generic local error
      occurred. The reported error was @error',
        ['@error' => $error->getMessage()]
      ));
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function setUnixSocket($unix_socket) {
    $this->unixSocket = $unix_socket;
  }

  /**
   * {@inheritdoc}
   */
  public function setApiVersion($api_version) {
    $this->apiVersion = $api_version;
  }

  /**
   * {@inheritdoc}
   */
  public function listImages() {
    $output = [];
    $endpoint = $this->buildEndpoint("images/$this->format");
    $results = $this->doGet($endpoint);
    if ($results !== FALSE) {
      $output = json_decode($results);
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function pushImage($name, array $auth_array = []) {
    $params = [];
    $endpoint = $this->buildEndpoint("images/$name/push");
    if (!empty($auth_array)) {
      $auth_json = json_encode($auth_array);
      $params['headers']['X-Registry-Auth'] = base64_encode($auth_json);
    }
    return $this->doPost($endpoint, $params);
  }

  /**
   * {@inheritdoc}
   */
  public function inspectImage($name) {
    $endpoint = $this->buildEndpoint("images/$name/$this->format");
    return $this->doGet($endpoint);
  }

  /**
   * {@inheritdoc}
   */
  public function pullImage($from_image) {
    $endpoint = $this->buildEndpoint("images/create?fromImage=$from_image");
    $response = $this->doPost($endpoint, []);
    $success = FALSE;
    // Verify image was pulled.
    try {
      $image = $this->inspectImage($from_image);
      $success = TRUE;
    }
    catch (DockerServiceException $e) {
      $this->logError('Docker pull image', $e, $this->t('Unable to find pulled image'), FALSE);
    }
    return $success;
  }

  /**
   * {@inheritdoc}
   */
  public function tagImage($name, $repo, $tag) {
    $endpoint = $this->buildEndpoint("images/$name/tag?repo=$repo&tag=$tag");
    $response = $this->doPost($endpoint, []);
  }

  /**
   * {@inheritdoc}
   */
  public function setFormat($format) {
    $this->format = $format;
  }

  /**
   * {@inheritdoc}
   */
  public function setUseSocket($use_socket) {
    $this->useSocket = $use_socket;
  }

  /**
   * {@inheritdoc}
   */
  public function parseImage($image) {
    $pattern = "/^(?:([^\/]+)\/)?(?:([^\/]+)\/)?([^@:\/]+)(?:[@:](.+))?$/";
    $match = preg_match($pattern, $image, $matches);

    if ($match === FALSE) {
      return FALSE;
    }

    $registry = $matches[1];
    $namespace = $matches[2];
    $repository = $matches[3];
    $tag = $matches[4] ?? 'latest';

    // If there is no hostname, the registry variable is the namespace.
    if (empty($namespace) && !empty($registry) && !preg_match('/[:.]/', $registry)) {
      $namespace = $registry;
      $registry = '';
    }

    $info = [
      'namespace' => $namespace,
      'registry' => $registry,
      'repository' => $repository,
      'tag' => $tag,
    ];

    // Derive the namespace/repository and also the full name.
    $registry = !empty($registry) ? $registry . '/' : '';
    $namespace = !empty($namespace) ? $namespace . '/' : '';
    $tag = ':' . $tag;

    $info['full_repository'] = $namespace . $repository;
    $info['name'] = $registry . $namespace . $repository . $tag;

    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public function isDockerUp($unix_socket = '', $api_version = '') {
    $is_available = FALSE;
    try {
      if (!empty($unix_socket)) {
        $this->setUnixSocket($unix_socket);
      }
      if (!empty($api_version)) {
        $this->setApiVersion($api_version);
      }
      $this->listImages();
      $is_available = TRUE;
    }
    catch (DockerServiceException $e) {
      // Set an error if docker is unreachable.
      $this->logError('Remote API Connection', $e, $this->t('Docker unreachable.  @error', ['@error' => $e->getMessage()]), FALSE);
    }
    return $is_available;
  }

  /**
   * Log errors to watchdog and throw an exception.
   *
   * @param string $type
   *   The error type.
   * @param \Exception $exception
   *   The exception object.
   * @param string $message
   *   The message to log.
   * @param bool $throw
   *   TRUE to throw exception.
   *
   * @throws \Drupal\docker\Service\DockerServiceException
   */
  private function logError($type, \Exception $exception, $message, $throw = TRUE) {
    watchdog_exception($type, $exception, $message);
    if ($throw === TRUE) {
      throw new DockerServiceException($exception);
    }
  }

  /**
   * Build a local api endpoint.
   *
   * @param string $operation
   *   Docker operation.
   * @param bool $https
   *   Build string using http or https.
   *
   * @return string
   *   Docker endpoint.
   */
  private function buildEndpoint($operation, $https = FALSE) {
    $endpoint = $https === TRUE ? 'https' : 'http';
    $endpoint .= '://locahost';
    if (!empty($this->apiVersion)) {
      $endpoint .= "/v{$this->apiVersion}";
    }
    $endpoint .= "/$operation";
    return $endpoint;
  }

  /**
   * Setup any default parameters for the Guzzle request.
   *
   * @return array
   *   Array of parameters.
   */
  private function getDefaultParams() {
    $params = [
      'headers' => [
        'Content-Type' => 'application/json',
      ],
    ];
    if ($this->useSocket === TRUE) {
      $params['curl'] = [
        CURLOPT_UNIX_SOCKET_PATH => $this->unixSocket,
      ];
    }
    return $params;
  }

}
