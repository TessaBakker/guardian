<?php

/**
 * @file
 * API documentation for the Guardian module.
 */

/**
 * Alter the Guardian mail metadata, that will be append to the body text.
 *
 * @param array $body
 *   Content of mail body.
 */
function hook_guardian_add_metadata_to_body_alter(array &$body) {
  if (!empty($_SERVER['HTTP_USER_AGENT'])) {
    $body[] = 'HTTP_USER_AGENT: ' . check_plain($_SERVER['HTTP_USER_AGENT']);
  }
}
