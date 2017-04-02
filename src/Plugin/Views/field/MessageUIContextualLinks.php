<?php

/**
 * @file
 * Definition of Drupal\message_ui\Plugin\views\field\ViewButton.
 */

namespace Drupal\message_ui\Plugin\views\field;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\message_ui\MessageAccessControlHandler;
use Drupal\message_ui\MessageUiViewsContextualLinksInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\message\Entity\Message;

/**
 * Presenting contextual links to the messages view.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("message_ui_contextual_links")
 */
class MessageUIContextualLinks extends FieldPluginBase {

  /**
   * Stores the result of message_view_multiple for all rows to reuse it later.
   *
   * @var array
   */
  protected $build;

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\message_ui\MessageUiViewsContextualLinksManager $contextual */
    $contextual_links = \Drupal::service('plugin.manager.message_ui_views_contextual_links');

    $links = [];

    // Iterate over the plugins.
    foreach ($contextual_links->getDefinitions() as $plugin) {
      /** @var MessageUiViewsContextualLinksInterface $contextual_link */
      $contextual_link = $contextual_links->createInstance($plugin['id']);
      $contextual_link->setMessage($values->_entity);

      if (!$link = $contextual_link->getRouterInfo()) {
        // Nothing happens in the plugin. Skip.
        continue;
      }

      $link['attributes'] = ['class' => [$plugin['id']]];

      $links[$plugin['id']] = $link + ['weight' => $plugin['weight']];
    }

    usort($links, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    $row['operations']['data'] = [
      '#type' => 'operations',
      '#links' => $links,
    ];

    return $row;
  }

  /**
   * Determine if this field can allow advanced rendering.
   *
   * Fields can set this to FALSE if they do not wish to allow
   * token based rewriting or link-making.
   */
  protected function allowAdvancedRender() {
    return FALSE;
  }

}
