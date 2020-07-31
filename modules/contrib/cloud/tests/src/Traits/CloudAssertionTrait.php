<?php

namespace Drupal\Tests\cloud\Traits;

/**
 * The assertion trait for common usage.
 */
trait CloudAssertionTrait {

  /**
   * Assert successful/non-error responses.
   */
  protected function assertNoErrorMessage() {
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains($this->t('Error message'));
    $this->assertSession()->pageTextNotContains($this->t('Warning message'));
  }

  /**
   * Assert warning responses.
   */
  protected function assertWarningMessage() {
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains($this->t('Error message'));
    $this->assertSession()->pageTextContains($this->t('Warning message'));
  }

  /**
   * Assert error response.
   */
  protected function assertErrorMessage() {
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains($this->t('Warning message'));
    $this->assertSession()->pageTextContains($this->t('Error message'));
  }

}
