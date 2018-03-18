<?php

namespace Drupal\qrblock\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Block\BlockPluginInterface;
use Endroid\QrCode\QrCode;

/**
 * Provides an QRcode block.
 *
 * @Block(
 *   id = "qr_block",
 *   admin_label = @Translation("QR block"),
 * )
 */
class QRBlock extends BlockBase implements BlockPluginInterface {


  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $build['scan_code'] = [
        '#type' => 'fieldset',
        '#title' => $this->configuration['qrblock_title'],
      ];
      $build['scan_code']['qr_code_text'] = [
        '#markup' => $this->configuration['qrblock_description'],
      ];
      $image_uri = "public://node" . $node->id() . ".png";
      $realpath = drupal_realpath($image_uri);

      if (!file_exists($realpath)) {
        // Create a basic QR code
        $qrCode = new QrCode($node->get('field_purchase_link')->getValue());
        $qrCode->writeFile(\Drupal::service('file_system')->realpath());
      }
      $render = [
        '#theme' => 'image_style',
        '#style_name' => 'medium',
        '#uri' => $image_uri,
      ];
      $build['scan_code']['qr_code'] = [
        '#markup' => render($render),
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['qrblock_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Please enter the block heading.'),
      '#default_value' => isset($config['qrblock_title']) ? $config['qrblock_title'] : $this->t('Scan here on your mobile'),
    ];
    $form['qrblock_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Please enter the block description.'),
      '#default_value' => isset($config['qrblock_description']) ? $config['qrblock_description'] : $this->t('To purchase this product on our app to avail exclusive app-only'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['qrblock_title'] = $values['qrblock_title'];
    $this->configuration['qrblock_description'] = $values['qrblock_description'];
  }
}
