<?php

// Drupal Code Review Test
// Please review the following code which was written for a Drupal 8 system. Assume that this code needs to be used on PHP 8.1 as part of a system upgrade. Drupal 8 is based on the Symfony framework.
// Please describe the issues in the code and how you would re-factor this code to align to modern coding standards and architectural principles.

namespace Drupal\cti_example\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ExampleBlock' block.
 *
 * @Block(
 *   id = "example_block",
 *   admin_label = @Translation("Example block"),
 * )
 */
class ExampleBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  // @todo by Andriy Malyeyev: It is better to use "Type Declarations" approach like "...function build(): array {..."
  public function build() {
    $build = [];
    // @todo by Andriy Malyeyev: It is better to use "Dependency Injection" approach.
    // @todo by Andriy Malyeyev: Also, it is better to use \Drupal\node\NodeInterface::PUBLISHED for "status".
    $articles = \Drupal::entityTypeManager()->getStorage('node')
      ->loadByProperties(['type' => 'article', 'status' => 1]);
    // @todo by Andriy Malyeyev: We have to use special type for list like "'#theme' => 'item_list'".
    $markup = '<ul>';
    foreach ($articles as $article) {
      // @todo by Andriy Malyeyev: We have to remove "var_dump" function
      var_dump($article->get('field_show_in_list')->getValue());
      $field_show_in_list = $article->get('field_show_in_list')->getValue();
      // @todo by Andriy Malyeyev: We have to use special type for links like "'#type' => 'link'".
      // @todo by Andriy Malyeyev: Also, it is better to use method "->getTitle()" not "->label()".
      $link = '<a href="/node/' . $article->id() .'">' . $article->label() .
        '</a>';
      // @todo by Andriy Malyeyev: There is a mistake, not "=" it have to be "===".
      if ($field_show_in_list = true) {
        $markup .= '<li><h3>' . $link . '</h3></li>';
      }
    }
    $markup .= '</ul>';
    // @todo by Andriy Malyeyev: The text have to be wrapped by "t" function with "placeholder" for count value.
    $markup .= 'There are ' . count($articles) . ' articles.';
    // @todo by Andriy Malyeyev: It is useless code, we have to remove it.
    //$markup .= 'There are 3 articles.';
    // @todo by Andriy Malyeyev: It is better to return "render array".
    $build['example_block']['#markup'] = $markup;

    return $build;
  }
}
