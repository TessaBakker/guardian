<?php

/**
 * @file
 * API documentation for the Guardian module.
 */

/**
 * Alter the Guardian mail metadata, that will be append to the body text.
 *
 * @param string[] $body
 *   Content of mail body.
 */
function hook_guardian_add_metadata_to_body_alter(array &$body) {
  if (!empty($_SERVER['HTTP_USER_AGENT'])) {
    $body[] = t('HTTP_USER_AGENT: @user_agent', ['@user_agent' => $_SERVER['HTTP_USER_AGENT']]);
  }
}
