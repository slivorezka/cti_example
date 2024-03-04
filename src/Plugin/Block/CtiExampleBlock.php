<?php

declare(strict_types=1);

namespace Drupal\cti_example\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides "CTI Example Block" block.
 *
 * @Block(
 *   id = "cti_example_block",
 *   admin_label = @Translation("CTI Example Block"),
 *   category = @Translation("CTI")
 * )
 */
class CtiExampleBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The node term storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected NodeStorageInterface $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    // Let's get the IDs of CT "Article" by "Entity Query" way.
    $query = $this->nodeStorage->getQuery()
      ->accessCheck()
      ->condition('type', 'article')
      // Also, we can exclude some Articles directly from the list in the query.
      // ->condition('field_show_in_list', 1);
      ->condition('status', NodeInterface::PUBLISHED);

    $article_ids = $query->execute();

    // If it is nothing to show we will return a message.
    if (empty($article_ids)) {
      return $this->getEmptyContent();
    }

    // Let's load all CT "Article".
    $articles = $this->nodeStorage->loadMultiple($article_ids);
    $items = [];
    $article_tags = [];

    /** @var \Drupal\node\NodeInterface $article */
    foreach ($articles as $article) {
      // Let's check if it is allowed to show the "Article" in list.
      if ($this->isArticleInlist($article)) {
        // Add cache dependency for each node.
        $article_tags[] = 'node:' . $article->id();
        // This is another way how to wrap the link in "h3" tag.
        /*$items[] = [
          '#type' => 'link',
          '#title' => [
            '#type' => 'html_tag',
            '#tag' => 'h3',
            '#value' => $article->getTitle(),
          ],
          '#url' => $article->toUrl(),
        ];*/

        $items[] = [
          '#prefix' => '<h3>',
          '#suffix' => '</h3>',
          '#type' => 'link',
          '#title' => $article->getTitle(),
          '#url' => $article->toUrl(),
        ];
      }
    }

    // If it is nothing to show we will return a message.
    if (empty($items)) {
      return $this->getEmptyContent();
    }

    // Counting all items.
    $article_count = count($items);

    // Let's create a list.
    $build['content'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];

    // The description about total "Articles".
    $build['description'] = [
      '#plain_text' => $this->formatPlural($article_count, 'There is @article_count article.', 'There are @article_count articles.', [
        '@article_count' => $article_count,
      ]),
    ];

    // The cache dependency.
    $build['#cache'] = [
      'contexts' => $this->getCacheContexts(),
      'tags' => Cache::mergeTags($this->getCacheTags(), $article_tags),
    ];

    // Let's include some CSS and JS.
    $build['#attached']['library'][] = 'cti_example/cti.example';

    return $build;
  }

  /**
   * The empty content.
   *
   * @return array[]
   *   The render array for empty content case.
   */
  private function getEmptyContent(): array {
    return [
      'content' => [
        '#plain_text' => $this->t('Articles not found.'),
      ],
    ];
  }

  /**
   * Checks if it allowed to show the "Article" in list.
   *
   * @param \Drupal\node\NodeInterface $article
   *   Tne node type "Article".
   *
   * @return bool
   *   Returns TRUE if the "Article" in list or FALSE if not.
   */
  private function isArticleInlist(NodeInterface $article): bool {
    $field = 'field_show_in_list';
    // Let's check if the field exist.
    if ($article->hasField($field)) {
      $field_show_in_list_field = $article->get($field);
      // Let's check if the field is not empty, and it is equal to TRUE.
      // P.S. I suppose so the field is "boolean" field for the case.
      // And Drupal uses "1" for TRUE and "0" for FALSE for "boolean" fields.
      return !$field_show_in_list_field->isEmpty() && (int) $field_show_in_list_field->getString() === 1;
    }

    return FALSE;
  }

}
