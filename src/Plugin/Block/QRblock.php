<?php

namespace Drupal\jugaad_patch\Plugin\Block;

use PHPQRCode\QRcode;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a 'qrblock' block.
 *
 * @Block(
 *   id = "qr_block",
 *   admin_label = @Translation("Qr Block"),
 *   category = @Translation("Qr Block block example")
 * )
 */
class QRblock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * RouteMatch used to get parameter Node.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Describes a logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Construct Drupal\jugaad_products\Plugin\Block\ProductUrlQrCodeBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    RouteMatchInterface $route_match,
    LoggerInterface $logger,
    FileSystemInterface $file_system) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
    $this->logger = $logger;
    $this->file_system = $file_system;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('logger.factory')->get('jugaad_patches'),
      $container->get('file_system'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;
    $node = $this->routeMatch->getParameter('node');
    if (!empty($node)) {
      $type = $node->getType();
      if ($type == 'product') {
        $nid = $node->id();
        $applink = $node->get('field_purchase_link')->getValue()[0]['uri'];
        $qrcode = $this->generateQrCodes($nid, $applink);
        $html = '<img src="' . $base_url . $qrcode . '" >';
        return [
          '#type' => 'markup',
          '#markup' => $html,
          '#cache' => [
            'max-age' => 0,
          ],
        ];
      }
    }
  }

  /**
   * Generate QR code.
   */
  public function generateQrCodes($nid, $applink) {
    // The below code will automatically create the path for the img.
    try {
      $path = '';
      $directory = "public://Images/QrCodes/";
      $this->file_system->prepareDirectory($directory, FileSystemInterface::MODIFY_PERMISSIONS | FileSystemInterface::CREATE_DIRECTORY);
      // Name of the generated image.
      // Generates a png image.
      $uri = $directory . $nid . '.png';
      $path = $this->file_system->realpath($uri);
      // Generate QR code image.
      QRcode::png($applink, $path, 'L', 4, 2);
      $image_path = '/Images/QrCodes/' . $nid . '.png';
      $getfile_path = PublicStream::basePath();
      $path = '/' . $getfile_path . $image_path;
    }
    catch (Exception $e) {
      $error = Error::decodeException($e);
      $this->logger->error('%type: @message in %function (line %line of %file).', $error);
    }
    return $path;
  }

}
