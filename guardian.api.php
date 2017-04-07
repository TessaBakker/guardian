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

/**
 * Set guarded users by Guardian.
 *
 * Example format to return:
 *   array(3 => 'user@example.com');
 *
 * Implement _guardian_account_defaults() if some logic is needed for adding
 * newly guarded users after enabling Guardian.
 * Set a new password if you need some logic to disable guarded users without
 * disabling Guardian.
 *
 * @return array
 *  An array of uid's with corresponding e-mail address
 *  - key must be an integer
 *  - value must be a valid e-mail address
 */
function hook_guardian_guarded_users() {
  return array(
    2 => 'user2@example.com',
    5 => 'user5@example.com',
  );
}
